<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com> - EPG Developers <tech-deployment@europaymentgroup.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */

require('paymentservice.php');
require('validation.php');


/**
 * Class EpgpaymentpagePaymentModuleFrontController
 */
class EpgpaymentpagePaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    protected $epg_token_name = 'epg_token_payment';
    protected $epg_error_name = 'epg_error_payment';

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {

        parent::initContent();

        $cart = $this->context->cart;

        $address = new Address((int)$cart->id_address_invoice);
        $customer = new Customer((int)$cart->id_customer);
        $currency = $this->context->currency;
        $country = new Country((int)$address->id_country);

        // start - make object for service to this class

        $paymentservice = new paymentservice($this->context,
                                            Tools::getValue('ptype'),
                                            Tools::getValue('ttype'),
                                            Tools::getValue('Token')
                                            );

        if ($this->context->cookie->__get($this->epg_token_name) &&
            $this->context->cookie->__get($this->epg_token_name) === Tools::getValue('Token')) {
            $paymentservice->setActionId(2);
        }

        $paymentservice->settingParameters();
        $result = $paymentservice->sendRequest();

        if (!empty($result->TransactionId) && $result->ResultStatus == 'OK') {
            $validation = new EpgpaymentpageValidationModuleFrontController();
            $validation->setResponse($result);
            $validation->postProcess();
        }

        if ($result->ResultStatus != 'OK') {
            $this->context->cookie->__unset($this->epg_token_name);
            $this->context->cookie->__set($this->epg_error_name, $result->ResultStatus
                                                                                . ' - ' . $result->ResultMessage);
            Tools::redirect('index.php?controller=order&step=3');
        }

        if($paymentservice->getActionId() == 1) {
            $this->context->cookie->__set($this->epg_token_name, $result->Token);

        }
        // end*/

        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int)$cart->id_currency),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'this_path' => $this->module->getPathUri(),
            'this_path_epg' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/',
            'pp_url' => $result->RedirectUrl
        ));

        $this->setTemplate('payment_execution.tpl');
    }
}
