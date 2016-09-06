<?php

require_once 'services/PaymentService.php';
require_once 'services/CurlService.php';
require_once 'services/ParseService.php';
require_once 'validation.php';

/**
 * Class EpgpaymentpagePaymentModuleFrontController
 */
class EpgpaymentpagePaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    protected $epg_token_name = 'epg_token_payment';
    protected $epg_error_name = 'epg_error_payment';

    /** @var CurlService */
    private $curlService;
    /** @var ParseService */
    private $parseService;
    /** @var PaymentService */
    private $paymentService;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;

        $this->paymentService = new PaymentService(
            $this->context,
            Tools::getValue('ptype'),
            Tools::getValue('ttype'),
            Tools::getValue('Token')
        );

        if ($this->context->cookie->__get($this->epg_token_name) &&
            $this->context->cookie->__get($this->epg_token_name) === Tools::getValue('Token')
        ) {
            $this->paymentService->setActionId(2);
        }

        $this->paymentService->setParams();

        $this->curlService = new CurlService(
            $this->paymentService->getUrl(),
            $this->paymentService->getParams()
        );
        $rawResponse = $this->curlService->sendRequest();

        $this->parseService = new ParseService($rawResponse);
        $result = $this->parseService->parse();

        if (!empty($result->TransactionId) && $result->ResultStatus == 'OK') {
            $validation = new EpgpaymentpageValidationModuleFrontController();
            $validation->setResponse($result);
            $validation->postProcess();
        }

        if ($result->ResultStatus != 'OK') {
            $this->context->cookie->__unset($this->epg_token_name);
            $this->context->cookie->__set(
                $this->epg_error_name,
                $result->ResultStatus . ' - ' . $result->ResultMessage
            );
            Tools::redirect('index.php?controller=order&step=3');
        }

        if ($this->paymentService->getActionId() == 1) {
            $this->context->cookie->__set($this->epg_token_name, $result->Token);
        }

        $this->context->smarty->assign([
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int)$cart->id_currency),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'this_path' => $this->module->getPathUri(),
            'this_path_epg' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/',
            'pp_url' => $result->RedirectUrl
        ]);

        $this->setTemplate('payment_execution.tpl');
    }
}
