<?php

namespace Dotpay\Dotpay\Tool;


use Dotpay\Dotpay\Tool\Curl;

class SellerApi {
    private $_baseurl;
    private $_test;
    private $_cainfo;
    
    public function __construct($url, $cainfo = '') {
        $this->_baseurl = $url;
        $this->_cainfo = $cainfo;
    }

    public function getCreditCardInfo($username, $password, $orderId) {
        $payments = $this->getPaymentByOrderId($username, $password, $orderId);
        if(empty($payments))
            return NULL;
        $number = $payments[0]->number;
        $payment = $this->getPaymentByNumber($username, $password, $number);
        if($payment->payment_method->channel_id!=248)
            return NULL;
        return $payment->payment_method->credit_card;
    }
    
    public function getCreditCardByPayment($username, $password, $number) {
        $payment = $this->getPaymentByNumber($username, $password, $number);
        return $payment->payment_method->credit_card;
    }
    
    public function getPaymentByNumber($username, $password, $number) {
        $url = $this->_baseurl.$this->getDotPaymentApi()."payments/$number/";
        $curl = new Curl();
        $curl->addOption(CURLOPT_URL, $url)
             ->addOption(CURLOPT_USERPWD, $username.':'.$password);
        $this->setCurlOption($curl);
        $response = json_decode($curl->exec());
        return $response;
    }

    public function getPaymentByOrderId($username, $password, $orderId) {
        $url = $this->_baseurl.$this->getDotPaymentApi().'payments/?control='.$orderId;
        $curl = new Curl();
        $curl->addOption(CURLOPT_URL, $url)
             ->addOption(CURLOPT_USERPWD, $username.':'.$password);
        $this->setCurlOption($curl);
        $response = json_decode($curl->exec());
        return $response->results;
    }
    
    private function getDotPaymentApi() {
        return "api/";
    }


    private function setCurlOption($curl) {
        $curl->addOption(CURLOPT_SSL_VERIFYPEER, TRUE)
             ->addOption(CURLOPT_SSL_VERIFYHOST, 2)
             ->addOption(CURLOPT_FOLLOWLOCATION, 1)
             ->addOption(CURLOPT_RETURNTRANSFER, 1)
             ->addOption(CURLOPT_TIMEOUT, 100)
             ->addOption(CURLOPT_CUSTOMREQUEST, "GET");
    }
}
