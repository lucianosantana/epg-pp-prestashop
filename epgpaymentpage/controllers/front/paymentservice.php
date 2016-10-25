<?php

/**
 * Created by EPG.
 * @author PrestaShop SA <contact@prestashop.com> - EPG Developers <tech-deployment@europaymentgroup.com>
 * Date: 14.01.2016
 * Time: 16:22
 */
class paymentservice
{
    protected $context;

    protected $ptype;

    protected $ttype;

    protected $params;

    protected $action_id;

    protected $data;

    public $actions = array(
        '1' => array(
            'name'  =>  'initiate',
            'url_link'  =>  '/tokenizer/get',
            'required_fields'  =>  array(
                'MerchantId', 'MerchantGuid', 'TransactionReference', 'TransactionUrl', 'ReturnUrl',
                'TransactionType', 'Phone', 'FirstName', 'LastName', 'Address', 'Zip', 'City', 'Email', 'Country',
                'State', 'Amount', 'Currency', 'Format', 'IpAddress',
            ),
            'returnTo' =>  '',
        ),
        '2' => array(
            'name'  =>  'get_result',
            'url_link'  =>  '/tokenizer/getresult',
            'required_fields'  =>  array(
                'MerchantId', 'MerchantGuid', 'TransactionType', 'Token', 'Format', 'IpAddress',
            ),
            'returnTo' =>  '',
        ),
    );

    public function __construct($context, $ptype, $ttype, $token)
    {
        $this->context = $context;
        $this->ttype = $ttype;
        $this->ptype = $ptype;
        $this->action_id = 1;

        $cart =  $context->cart;
        $cooki = $context->cookie;
        $link = $context->link;

        if ((int)$cart->id_currency)
            $currency = new Currency((int)$cart->id_currency);
        else
            $currency = $context->currency;

        if ((int)$cart->id_address_invoice)
            $address = new Address((int)$cart->id_address_invoice);
        else
            $address = $context->address;

        $this->data['Currency'] = ($currency->iso_code) ? $currency->iso_code : 'EUR';
        $this->data['Amount'] = (string)$cart->getOrderTotal(true, Cart::BOTH);
        $this->data['FirstName'] = $cooki->customer_firstname;
        $this->data['LastName'] = $cooki->customer_lastname;
        $this->data['Address'] = preg_replace(
                                    "/[^a-zA-Z0-9_äöüÄÖÜ ]/",
                                    "",
                                    $address->address1 . ' ' . $address->address2);

        $this->data['Country'] = (Country::getIsoById($address->id_country))
                                ? Country::getIsoById($address->id_country) : '';

        $this->data['State'] = (Country::getIsoById($address->id_state)) ? Country::getIsoById($address->id_state) : '';
        $this->data['Zip'] = ($address->postcode) ? $address->postcode : '';
        $this->data['City'] = ($address->city) ? trim($address->city) : '';
        $this->data['Email'] = trim($cooki->email);
        $this->data['Phone'] = ($address->phone) ? $address->phone : '';
        $this->data['TransactionType'] = $ttype;
        $this->data['Token'] = $token;

        $this->actions[1]['returnTo'] = $link->getModuleLink('epgpaymentpage', 'payment', ['ptype' => $ptype, 'ttype' => $ttype]);

        $config = Configuration::getMultiple(array('MERCHANT_ID', 'MERCHANT_GUID', 'PP_URL'));
        $this->data['MerchantId'] = (!empty($config['MERCHANT_ID'])) ? $config['MERCHANT_ID'] : 0;
        $this->data['MerchantGuid'] = (!empty($config['MERCHANT_GUID'])) ? $config['MERCHANT_GUID'] : '';
        $this->actions[1]['url_link'] = $config['PP_URL'] . $this->actions[1]['url_link'];
        $this->actions[2]['url_link'] = $config['PP_URL'] . $this->actions[2]['url_link'];

        $this->data['Format'] = 'json';
        $this->data['IpAddress'] = $_SERVER['SERVER_ADDR'];

        $this->data['TransactionReference'] = 'Reference-' .  $cooki->id_customer;
        $this->data['TransactionUrl'] = $_SERVER['SERVER_NAME'] == 'localhost' ? 'www.europaymentgroup.com' : $_SERVER['SERVER_NAME'];
    }

    public function gettingParameters()
    {
        return $this->params;
    }

    public function settingParameters()
    {
        $this->data['ReturnUrl'] = $this->actions[$this->action_id]['returnTo'];
        $this->params = array_intersect_key($this->data,
                                            array_flip($this->actions[$this->action_id]['required_fields']));
    }

    public function getActionId()
    {
        return $this->action_id;
    }

    public function setActionId($action_id)
    {
        $this->action_id = ($action_id > 2 || $action_id <1) ? 1 : $action_id;
    }

    public function sendRequest()
    {
        $params = $this->params;
        $url = $this->actions[$this->action_id]['url_link'];

        $cpt = curl_init();

        curl_setopt($cpt, CURLOPT_URL, $url);
        curl_setopt($cpt, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($cpt, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cpt, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($cpt, CURLOPT_CONNECTTIMEOUT, 15);

        curl_setopt($cpt, CURLOPT_POST, count($params));
        curl_setopt($cpt, CURLOPT_POSTFIELDS, http_build_query($params));

        $curl_return = curl_exec($cpt);
        $curl_error = curl_error($cpt);
        $curl_info = curl_getinfo($cpt);

        curl_close($cpt);
        $resultObject = json_decode($curl_return);

        return ($resultObject);
    }
}
