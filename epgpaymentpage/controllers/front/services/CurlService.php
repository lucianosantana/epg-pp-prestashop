<?php

class CurlService
{
    private $url;
    private $params;

    public function __construct($url, $params)
    {
        $this->url = $url;
        $this->params = $params;
    }

    public function sendRequest()
    {
        $cpt = curl_init();

        curl_setopt($cpt, CURLOPT_URL, $this->url);
        curl_setopt($cpt, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($cpt, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cpt, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($cpt, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($cpt, CURLOPT_POST, count($this->params));
        curl_setopt($cpt, CURLOPT_POSTFIELDS, http_build_query($this->params));

        $response = curl_exec($cpt);
        curl_close($cpt);

        return $response;
    }
}