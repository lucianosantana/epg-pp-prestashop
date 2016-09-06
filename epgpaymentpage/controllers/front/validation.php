<?php

class EpgpaymentpageValidationModuleFrontController extends ModuleFrontController
{
    private $response;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 ||
            $cart->id_address_delivery == 0 ||
            $cart->id_address_invoice == 0 ||
            !$this->module->active
        ) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available in case
        // the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module)
            if ($module['name'] == 'epgpaymentpage') {
                $authorized = true;
                break;
            }
        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

        $mailVars = [];

        $this->module->validateOrder(
            $cart->id,
            Configuration::get('PS_OS_BANKWIRE'),
            $total,
            $this->module->displayName,
            null,
            $mailVars,
            (int)$currency->id,
            false,
            $customer->secure_key
        );
        $this->processPaymentInDB();
        Tools::redirect('index.php?controller=order-confirmation&id_cart=' .
            $cart->id . '&id_module=' . $this->module->id . '&id_order=' .
            $this->module->currentOrder . '&key=' . $customer->secure_key
        );
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    private function processPaymentInDB()
    {
        $db = DatabaseService::instance();
        $sql = "INSERT INTO `epg_orders` 
                (order_id, token, transactionId, resultStatus) 
                VALUES (?, ?, ?, 'OK')";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('isi', $this->module->currentOrder, $this->response->Token, $this->response->TransactionId);
        $stmt->execute();
        $stmt->close();
    }
}
