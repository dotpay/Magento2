<?php

/**
 * Dotpay abstract controller
 */

namespace Dotpay\Dotpay\Controller;

use \Dotpay\Dotpay\Model\Payment;
use \Dotpay\Dotpay\Tool\Curl;

abstract class Dotpay extends \Magento\Framework\App\Action\Action {
    
    // Force protocol HTTPS for Dotpay response
    const FORCE_HTTPS_DOTPAY_RESPONSE = false;
    
    // Check Real IP if server is proxy, balancer...
    const CHECK_REAL_IP = true;
    
    // Local IP address
    const LOCAL_IP = '127.0.0.1';
    
    // STR EMPTY
    const STR_EMPTY = '';
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;
    
    /**
     * @var \Dotpay\Dotpay\Model\Payment
     */
    protected $_model;
    
    /**
     * Locale Resolver
     *
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $localeResolver;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Dotpay\Dotpay\Model\Payment $model
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context
    , \Magento\Customer\Model\Session $customerSession
    , \Magento\Checkout\Model\Session $checkoutSession
    , \Magento\Sales\Model\OrderFactory $orderFactory
    , \Dotpay\Dotpay\Model\Payment $model
    , \Magento\Framework\Locale\Resolver $localeResolver
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_model = $model;
        $this->localeResolver = $localeResolver;

        parent::__construct($context);
        
        $this->_model->setCustomerID($this->_customerSession->getCustomerId());
        
        header_register_callback(function(){
            header_remove('Cache');
            header("Cache: no-cache", true);
            header_remove('Cache-Control');
            header('Cache-Control: max-age=0, post-check=0, pre-check=0, private, no-cache, no-store, must-revalidate, proxy-revalidate', true);
            header_remove('Pragma');
            header("Pragma: no-cache", true);
            header_remove('Last-Modified');
            header("Last-Modified: " . gmdate('D, d M Y H:i:s \G\M\T', time() - 1), true);
            header_remove('Expires');
            header('Expires: on,' . gmdate('D, d M Y H:i:s \G\M\T', time() - (60 * 60)), true);
        });
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotAction() {
        $dotTest = (int) $this->_model->getConfigData('test');
        $dotAction = $this->_model->getConfigData('redirect_url');
        if(1 === $dotTest) {
            $dotAction = $this->_model->getConfigData('redirect_url_test');
        }
        
        return $dotAction;
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotPluginDir() {
        return getcwd()."/app/code/Dotpay/Dotpay/";
    }
    
    /**
     * 
     * @return type
     */
    protected function getDotSellerApiUrl()
    {
        $dotTest = (int) $this->_model->getConfigData('test');
        $dotSellerApi = $this->_model->getConfigData('seller_url');
        if(1 === $dotTest) {
            $dotSellerApi = $this->_model->getConfigData('seller_url_test');
        }
        
        return $dotSellerApi;
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotId() {
        return $this->_model->getConfigData('seller_id');
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotPvId() {
        return $this->_model->getConfigData('pv_id');
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotUsername() {
        return $this->_model->getConfigData('dotpay_username');
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotPassword() {
        return $this->_model->getConfigData('dotpay_password');
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotControl() {
        return $this->getLastOrderId();
    }
    
    /**
     * 
     * @return string
     */
    protected function getLastOrderId() {
        return $this->_checkoutSession->getLastRealOrder()->getEntityId();
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotPinfo() {
        return "Sklep - {$_SERVER['HTTP_HOST']}";
    }
    
    /**
     * 
     * @param float $amount
     * @return string
     */
    protected function getFormatAmount($amount) {
        $amountRound = round((float) $amount, 2);
        $amountFormat = sprintf("%01.2f", $amountRound);
        
        return $amountFormat;
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotAmount() {
        return $this->getFormatAmount($this->_checkoutSession->getLastRealOrder()->getGrandTotal());
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotCurrency() {
        return $this->_checkoutSession->getLastRealOrder()->getOrderCurrencyCode();
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotDescription() {
        return __("Order ID: %1", $this->_checkoutSession->getLastRealOrder()->getRealOrderId());
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotLang() {
        $lang = strtolower(\Locale::getPrimaryLanguage($this->localeResolver->getLocale()));
        return $lang;
    }
    
    protected function getServerProtocol() {
        $result = 'http';
        
        if(isset($_SERVER['HTTPS'])) {
            $result = 'https';
        }
        
        return $result;
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotUrl() {
        return "{$this->getServerProtocol()}://{$_SERVER['HTTP_HOST']}/dotpay/processing/back";
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotUrlC() {
        return "{$this->getServerProtocol()}://{$_SERVER['HTTP_HOST']}/dotpay/notification/response";
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotUrlSignature() {
        return "{$this->getServerProtocol()}://{$_SERVER['HTTP_HOST']}/dotpay/processing/signature";
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotUrlOneClickRegister() {
        return "{$this->getServerProtocol()}://{$_SERVER['HTTP_HOST']}/dotpay/oneclick/register";
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotUrlOneClickPreparing() {
        return "{$this->getServerProtocol()}://{$_SERVER['HTTP_HOST']}/dotpay/oneclick/preparing";
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotUrlOneClickManage() {
        return "{$this->getServerProtocol()}://{$_SERVER['HTTP_HOST']}/dotpay/oneclick/manage";
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotUrlOneClickRemove() {
        return "{$this->getServerProtocol()}://{$_SERVER['HTTP_HOST']}/dotpay/oneclick/remove";
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotApiVersion() {
        return 'dev';
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotFirstname() {
        return $this->_checkoutSession->getLastRealOrder()->getBillingAddress()->getFirstname();
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotLastname() {
        return $this->_checkoutSession->getLastRealOrder()->getBillingAddress()->getLastname();
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotEmail() {
        return $this->_checkoutSession->getLastRealOrder()->getBillingAddress()->getEmail();
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotPhone() {
        return $this->_checkoutSession->getLastRealOrder()->getBillingAddress()->getTelephone();
    }
    
    /**
     * 
     * @return array
     */
    protected function getDotStreetAndStreetN1() {
        $result_street = '';
        $result_street_n1 = '';
        
        $street = $this->_checkoutSession->getLastRealOrder()->getBillingAddress()->getStreet();
        
        if(isset($street[0])) {
            $result_street = $street[0];
        }
        
        if(isset($street[1])) {
            $result_street_n1 = $street[1];
        } else if(isset($street[0])) {
            preg_match("/\s[\w\d\/_\-]{0,30}$/", $street[0], $matches);
            if(isset($matches[0])) {
                $result_street_n1 = trim($matches[0]);
                $result_street = str_replace($matches[0], '', $street[0]);
            }
        }
        
        return array(
            'street' => $result_street,
            'street_n1' => $result_street_n1
        );
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotCity() {
        return $this->_checkoutSession->getLastRealOrder()->getBillingAddress()->getCity();
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotPostcode() {
        return $this->_checkoutSession->getLastRealOrder()->getBillingAddress()->getPostcode();
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotCountry() {
        return strtoupper($this->_checkoutSession->getLastRealOrder()->getBillingAddress()->getCountryId());
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotCurrenciesForPV() {
        return $this->_model->getConfigData('pv_currencies');
    }
    
    /**
     * 
     * @return array
     */
    private function getHiddenFields() {
        $street_data = $this->getDotStreetAndStreetN1();
        return array(
            'id' => $this->getDotId(),
            'control' => $this->getDotControl(),
            'p_info' => $this->getDotPinfo(),
            'amount' => $this->getDotAmount(),
            'currency' => $this->getDotCurrency(),
            'description' => $this->getDotDescription(),
            'lang' => $this->getDotLang(),
            'URL' => $this->getDotUrl(),
            'URLC' => $this->getDotUrlC(),
            'api_version' => $this->getDotApiVersion(),
            'type' => 0,
            'ch_lock' => 0,
            'firstname' => $this->getDotFirstname(),
            'lastname' => $this->getDotLastname(),
            'email' => $this->getDotEmail(),
            'phone' => $this->getDotPhone(),
            'street' => $street_data['street'],
            'street_n1' => $street_data['street_n1'],
            'city' => $this->getDotCity(),
            'postcode' => $this->getDotPostcode(),
            'country' => $this->getDotCountry()
        );
    }
    
    /**
     * 
     * @return array
     */
    protected function getHiddenFieldsDotpay() {
        $hiddenFields = $this->getHiddenFields();
        
        if($this->_model->isDotpayWidget()) {
            $hiddenFields['ch_lock'] = 1;
            $hiddenFields['type'] = 4;
        }
        
        return $hiddenFields;
    }
    
    protected function getHiddenFieldsOneClick() {
        $hiddenFields = $this->getHiddenFields();
        
        $hiddenFields['channel'] = Payment::$ocChannel;
        $hiddenFields['ch_lock'] = 1;
        $hiddenFields['type'] = 4;
        
        return $hiddenFields;
    }
    
    protected function getHiddenFieldsOneClickCard($credit_card_customer_id, $credit_card_id) {
        $hiddenFields = $this->getHiddenFieldsOneClick();

        $hiddenFields['credit_card_customer_id'] = $credit_card_customer_id;
        $hiddenFields['credit_card_id'] = $credit_card_id;
        $hiddenFields['bylaw'] = '1';
        $hiddenFields['personal_data'] = '1';

        return $hiddenFields;
    }

    protected function getHiddenFieldsOneClickRegister($ccHash) {
        $hiddenFields = $this->getHiddenFieldsOneClick();
        
        $hiddenFields['credit_card_store'] = 1;
        $hiddenFields['credit_card_customer_id'] = $ccHash;
        
        return $hiddenFields;
    }
    
    /**
     * 
     * @return array
     */
    protected function getHiddenFieldsPV() {
        $hiddenFields = $this->getHiddenFields();
        
        $hiddenFields['channel'] = Payment::$pvChannel;
        $hiddenFields['id'] = $this->getDotPvId();
        $hiddenFields['ch_lock'] = 1;
        $hiddenFields['type'] = 4;
        
        return $hiddenFields;
    }
    
    /**
     * 
     * @return array
     */
    protected function getHiddenFieldsMasterPass() {
        $hiddenFields = $this->getHiddenFields();
        
        if($this->_model->isDotpayTest()) {
            $hiddenFields['channel'] = 246;
        } else {
            $hiddenFields['channel'] = Payment::$mpChannel;
        }
        
        $hiddenFields['ch_lock'] = 1;
        $hiddenFields['type'] = 4;
        
        return $hiddenFields;
    }
    
    /**
     * 
     * @return array
     */
    protected function getHiddenFieldsBlik() {
        $hiddenFields = $this->getHiddenFields();
        
        $hiddenFields['channel'] = Payment::$blikChannel;
        $hiddenFields['ch_lock'] = 1;
        $hiddenFields['type'] = 4;
        
        return $hiddenFields;
    }
    
    /**
     * Check, if actual currency is allow
     * @param string $allowCurrencyForm
     * @return boolean
     */
    protected function isDotSelectedCurrency($allowCurrencyForm) {
        $result = false;
        $paymentCurrency = $this->getDotCurrency();
        $allowCurrency = str_replace(';', ',', $allowCurrencyForm);
        $allowCurrency = strtoupper(str_replace(' ', '', $allowCurrency));
        $allowCurrencyArray =  explode(",",trim($allowCurrency));
        
        if(in_array(strtoupper($paymentCurrency), $allowCurrencyArray)) {
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Returns string with channels data JSON
     * @return string|boolean
     */
    protected function getApiChannels($pv=false) {
        $dotpayUrl = $this->getDotAction();
        $paymentCurrency = $this->getDotCurrency();
        
        if($pv)
            $dotpayId = $this->getDotPvId();
        else
            $dotpayId = $this->getDotId();
        
        $orderAmount = $this->getDotAmount();
        
        $dotpayLang = $this->getDotLang();
        
        $curlUrl = "{$dotpayUrl}payment_api/channels/";
        $curlUrl .= "?currency={$paymentCurrency}";
        $curlUrl .= "&id={$dotpayId}";
        $curlUrl .= "&amount={$orderAmount}";
        $curlUrl .= "&lang={$dotpayLang}";
        
        try {
            $curl = new Curl();
            $curl->addOption(CURLOPT_SSL_VERIFYPEER, false)
                 ->addOption(CURLOPT_HEADER, false)
                 ->addOption(CURLOPT_FOLLOWLOCATION, true)
                 ->addOption(CURLOPT_URL, $curlUrl)
                 ->addOption(CURLOPT_REFERER, $curlUrl)
                 ->addOption(CURLOPT_RETURNTRANSFER, true);
            $resultJson = $curl->exec();
        } catch (Exception $exc) {
            $resultJson = false;
        }
        
        if($curl) {
            $curl->close();
        }
        
        return $resultJson;
    }
    
    /**
     * Returns channel data, if payment channel is active for order data
     * @param type $id channel id
     * @return array|false
     */
    public function getChannelData($resultJson, $id) {
        if(false !== $resultJson) {
            $result = json_decode($resultJson, true);

            if (isset($result['channels']) && is_array($result['channels'])) {
                foreach ($result['channels'] as $channel) {
                    if (isset($channel['id']) && $channel['id']==$id) {
                        return $channel;
                    }
                }
            }
        }
        return false;
    }
    
    /**
     * 
     * @param string $type
     * @param int $channel
     * @param string $blik
     * @return string
     */
    protected function buildSignature4Request($type, $channel = null, $blik = null, $creditCardCustomerId = null) {
        $pin = $this->_model->getConfigData('pin');
        switch ($type) {
            case 'oneclick':
                $creditCardId = $this->_model->cardGetCreditCardIdByCardHash($creditCardCustomerId);
                $hiddenFields = $this->getHiddenFieldsOneClickCard($creditCardCustomerId, $creditCardId);
                break;
            case 'oneclick_register':
                $creditCardId = $this->_model->cardGetCreditCardIdByCardHash($creditCardCustomerId);
                $hiddenFields = $this->getHiddenFieldsOneClickRegister($creditCardCustomerId);
                break;
            case 'mp':
                $hiddenFields = $this->getHiddenFieldsMasterPass();
                break;
            case 'pv':
                $hiddenFields = $this->getHiddenFieldsPV();
                $pin = $this->_model->getConfigData('pv_pin');
                break;
            case 'blik':
                $hiddenFields = $this->getHiddenFieldsBlik();
                break;
            case 'dotpay':
            default:
                $hiddenFields = $this->getHiddenFieldsDotpay();
        }
        
        
        $fieldsRequestArray = array(
            'DOTPAY_PIN' => $pin,
            'api_version' => $this->getDotApiVersion(),
            'lang' => $hiddenFields['lang'],
            'DOTPAY_ID' => $hiddenFields['id'],
            'amount' => $hiddenFields['amount'],
            'currency' => $hiddenFields['currency'],
            'description' => $hiddenFields['description'],
            'control' => $hiddenFields['control'],
            'channel' => isset($hiddenFields['channel']) ? $hiddenFields['channel'] : self::STR_EMPTY,
            'ch_lock' => $hiddenFields['ch_lock'],
            'URL' => $hiddenFields['URL'],
            'type' => $hiddenFields['type'],
            'buttontext' => self::STR_EMPTY,
            'URLC' => $hiddenFields['URLC'],
            'firstname' => $hiddenFields['firstname'],
            'lastname' => $hiddenFields['lastname'],
            'email' => $hiddenFields['email'],
            'street' => $hiddenFields['street'],
            'street_n1' => $hiddenFields['street_n1'],
            'street_n2' => self::STR_EMPTY,
            'state' => self::STR_EMPTY,
            'addr3' => self::STR_EMPTY,
            'city' => $hiddenFields['city'],
            'postcode' => $hiddenFields['postcode'],
            'phone' => $hiddenFields['phone'],
            'country' => $hiddenFields['country'],
            'p_info' => $hiddenFields['p_info'],
            'bylaw' => self::STR_EMPTY,
            'personal_data' => self::STR_EMPTY,
            'credit_card_store' => isset($hiddenFields['credit_card_store']) ? $hiddenFields['credit_card_store'] : self::STR_EMPTY,
            'credit_card_customer_id' => isset($hiddenFields['credit_card_customer_id']) ? $hiddenFields['credit_card_customer_id'] : self::STR_EMPTY,
            'credit_card_id' => isset($hiddenFields['credit_card_id']) ? $hiddenFields['credit_card_id'] : self::STR_EMPTY,
            'blik_code' => self::STR_EMPTY
        );
        if('oneclick' === $type && $this->_model->isDotpayOneClick()) {
            if(isset($channel)) {
                $fieldsRequestArray['channel'] = $channel;
            }
            $fieldsRequestArray['bylaw'] = '1';
            $fieldsRequestArray['personal_data'] = '1';
        } elseif('oneclick_register' === $type && $this->_model->isDotpayOneClick()) {
            if(isset($channel)) {
                $fieldsRequestArray['channel'] = $channel;
            }
            if(isset($creditCardCustomerId)) {
                $fieldsRequestArray['credit_card_customer_id'] = $creditCardCustomerId;
            }
        } else if('mp' === $type && $this->_model->isDotpayMasterPass()) {
            if(isset($channel)) {
                $fieldsRequestArray['channel'] = $channel;
            }
            $fieldsRequestArray['bylaw'] = '1';
            $fieldsRequestArray['personal_data'] = '1';
        } elseif('blik' === $type && $this->_model->isDotpayBlik()) {
            if(isset($channel) && !$this->_model->isDotpayTest()) {
                $fieldsRequestArray['channel'] = $channel;
            }
            if(!empty($blik)) {
                $fieldsRequestArray['blik_code'] = $blik;
            }
            $fieldsRequestArray['bylaw'] = '1';
            $fieldsRequestArray['personal_data'] = '1';
        } elseif('dotpay' === $type) {
            if(isset($channel)) {
                $fieldsRequestArray['channel'] = $channel;
            }
            if($this->_model->isDotpayWidget()) {
                $fieldsRequestArray['bylaw'] = '1';
                $fieldsRequestArray['personal_data'] = '1';
            }
        }
        
        $fieldsRequestStr = implode(self::STR_EMPTY, $fieldsRequestArray);
        return hash('sha256', $fieldsRequestStr);
    }
}
