<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require 'controllers/front/services/DatabaseService.php';

class Epgpaymentpage extends PaymentModule
{
    protected $_html = '';
    protected $_postErrors = array();
    protected $epg_error_name = 'epg_error_payment';

    public $merchantId;
    public $merchantGuid;
    public $ppUrl;
    public $poTypes;
    public $extra_mail_vars;

    public function __construct()
    {
        $this->name = 'epgpaymentpage';
        $this->tab = 'payments_gateways';
        $this->version = '1.2.1';
        $this->author = 'Euro Payment Group GmbH';
        $this->controllers = array('payment', 'validation');
        $this->is_eu_compatible = 1;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $config = Configuration::getMultiple(array('MERCHANT_ID', 'MERCHANT_GUID', 'PP_URL', 'PO_TYPES'));
        if (!empty($config['MERCHANT_ID'])) {
            $this->merchantId = $config['MERCHANT_ID'];
        }
        if (!empty($config['MERCHANT_GUID'])) {
            $this->merchantGuid = $config['MERCHANT_GUID'];
        }
        if (!empty($config['PP_URL'])) {
            $this->ppUrl = $config['PP_URL'];
        }
        if (!empty($config['PO_TYPES'])) {
            $this->poTypes = explode(',', $config['PO_TYPES']);
        }

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('EPG PaymentPage');
        $this->description = $this->l('Accept payments through the Euro Payment Group Payment Page');
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');

        if (!isset($this->merchantId) || !isset($this->merchantGuid) || !isset($this->merchantEnv)) {
            $this->warning = $this->l('MerchantId, MerchantGuid and Environment must be configured before using this module.');
        }
        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }

        $this->extra_mail_vars = [
            '{epg_paymentoption}' => ''
        ];
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('payment') ||
            !$this->registerHook('displayPaymentEU') ||
            !$this->registerHook('paymentReturn')
        ) {
            return false;
        }

        $this->prepareDatabase();

        return true;
    }

    private function prepareDatabase()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `epg_orders` (
                  `order_id` INT(11) NOT NULL PRIMARY KEY,
                  `token` VARCHAR(255) NOT NULL,
                  `transactionId` INT(11) NOT NULL,
                  `resultStatus` VARCHAR(50) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $db = DatabaseService::instance();
        $db->query($sql);
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('MERCHANT_ID')
            || !Configuration::deleteByName('MERCHANT_GUID')
            || !Configuration::deleteByName('PP_URL')
            || !Configuration::deleteByName('PO_TYPES')
            || !parent::uninstall()
        )
            return false;

        $db = DatabaseService::instance();
        $sql = "DROP TABLE `epg_orders`";
        $db->query($sql);

        return true;
    }

    protected function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue('MERCHANT_ID')) {
                $this->_postErrors[] = $this->l('Merchant Id is required.');
            } else if (!Tools::getValue('MERCHANT_GUID')) {
                $this->_postErrors[] = $this->l('Merchant Guid is required.');
            } else if (!Tools::getValue('PP_URL')) {
                $this->_postErrors[] = $this->l('PaymentPage URL is required.');
            } else if (!Tools::getValue('PO_TYPES')) {
                $this->_postErrors[] = $this->l('Methods of Payment are required');
            }
        }
    }

    protected function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('MERCHANT_ID', Tools::getValue('MERCHANT_ID'));
            Configuration::updateValue('MERCHANT_GUID', Tools::getValue('MERCHANT_GUID'));
            Configuration::updateValue('PP_URL', Tools::getValue('PP_URL'));
            Configuration::updateValue('PO_TYPES', implode(',', Tools::getValue('PO_TYPES')));
        }
        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    protected function _displayEpg()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        } else {
            $this->_html .= '<br />';
        }


        $this->_html .= $this->_displayEpg();
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    public function hookPayment($params)
    {
        if (!$this->active) {
            return false;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return false;
        }

        $this->smarty->assign([
            'this_path' => $this->_path,
            'this_path_epg' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
            'static_token' => Tools::getToken(false),
            'this_error' => $this->context->cookie->__get($this->epg_error_name),
            'poTypes' => $this->poTypes
        ]);

        $this->context->cookie->__unset($this->epg_error_name);
        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookDisplayPaymentEU($params)
    {
        if (!$this->active) {
            return false;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return false;
        }
        
        $payment_options = [
            'cta_text' => $this->l('Pay'),
            'logo' => Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/epg_logo.jpg'),
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
        ];

        return $payment_options;
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return false;
        }

        $state = $params['objOrder']->getCurrentState();

        if (in_array($state, [
            Configuration::get('PS_OS_BANKWIRE'),
            Configuration::get('PS_OS_OUTOFSTOCK'),
            Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')
        ])) {
            $this->smarty->assign([
                'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
                'epgMerchantGuid' => Tools::nl2br($this->merchantGuid),
                'epgMerchantId' => $this->merchantId,
                'status' => 'ok',
                'id_order' => $params['objOrder']->id
            ]);

            if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference)) {
                $this->smarty->assign('reference', $params['objOrder']->reference);
            }

            $epgOrder = new Order($params['objOrder']->id);
            $epgOrder->setCurrentState(2);
        } else {
            $this->smarty->assign('status', 'failed');
        }

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('EPG Parameters'),
                    'icon' => 'icon-envelope'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Merchant Id'),
                        'name' => 'MERCHANT_ID',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Merchant Guid'),
                        'name' => 'MERCHANT_GUID',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Paymentpage URL'),
                        'name' => 'PP_URL',
                        'required' => true
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Methods of Payment supported'),
                        'name' => 'PO_TYPES[]',
                        'multiple' => true,
                        'selected' => 'selected',
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array('id_option' => 'creditcard', 'name' => $this->l('Credit Card')),
                                array('id_option' => 'giropay', 'name' => $this->l('Giropay')),
                                array('id_option' => 'ideal', 'name' => $this->l('iDEAL')),
                                array('id_option' => 'eps', 'name' => $this->l('EPS')),
                                array('id_option' => 'sofort', 'name' => $this->l('Sofortüberweisung')),
                                array('id_option' => 'paypal', 'name' => $this->l('Paypal')),
                                array('id_option' => 'skrill', 'name' => $this->l('Skrill')),
                                array('id_option' => 'neteller', 'name' => $this->l('Neteller')),
                                array('id_option' => 'paysafecard', 'name' => $this->l('Paysafecard')),
                            ),
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'MERCHANT_ID' => Tools::getValue('MERCHANT_ID', Configuration::get('MERCHANT_ID')),
            'MERCHANT_GUID' => Tools::getValue('MERCHANT_GUID', Configuration::get('MERCHANT_GUID')),
            'PP_URL' => Tools::getValue('PP_URL', Configuration::get('PP_URL')),
            'PO_TYPES[]' => Tools::getValue('PO_TYPES', explode(',', Configuration::get('PO_TYPES'))),
        );
    }
}
