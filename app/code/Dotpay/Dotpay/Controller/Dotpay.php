<?php

/**
 * Dotpay abstract controller
 */

namespace Dotpay\Dotpay\Controller;

abstract class Dotpay extends \Magento\Framework\App\Action\Action {
    
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
    protected function getDotId() {
        return $this->_model->getConfigData('id');
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotControl() {
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
        $lang = \Locale::getRegion($this->localeResolver->getLocale());
        return strtolower($lang);
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotUrl() {
        return "http://{$_SERVER['HTTP_HOST']}/dotpay/processing/back";
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotUrlC() {
        return "http://{$_SERVER['HTTP_HOST']}/dotpay/notification/response";
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotUrlSignature() {
        return "http://{$_SERVER['HTTP_HOST']}/dotpay/processing/signature";
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
     * @return string
     */
    protected function getDotStreet() {
        $result = '';
        
        $street = $this->_checkoutSession->getLastRealOrder()->getBillingAddress()->getStreet();
        
        if(isset($street[0])) {
            $result = $street[0];
        }
        
        return $result;
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotStreetN1() {
        $result = '';
        
        $street = $this->_checkoutSession->getLastRealOrder()->getBillingAddress()->getStreet();
        
        if(isset($street[1])) {
            $result = $street[1];
        }
        
        return $result;
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
     * @return array
     */
    private function getHiddenFields() {
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
            'street' => $this->getDotStreet(),
            'street_n1' => $this->getDotStreetN1(),
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
    
    private function getHiddenFieldsOneClick() {
        $hiddenFields = $this->getHiddenFields();
        
        if($this->_model->isDotpayTest()) {
            $hiddenFields['currency'] = 'EUR';
        }
        
        $hiddenFields['channel'] = 248;
        $hiddenFields['ch_lock'] = 1;
        $hiddenFields['type'] = 4;
        
        return $hiddenFields;
    }
    
    protected function getHiddenFieldsOneClickCard($credit_card_customer_id, $credit_card_id) {
        $hiddenFields = $this->getHiddenFieldsOneClick();

        $hiddenFields['credit_card_customer_id'] = $credit_card_customer_id;
        $hiddenFields['credit_card_id'] = $credit_card_id;

        return $hiddenFields;
    }

    protected function getHiddenFieldsOneClickRegister() {
        $hiddenFields = $this->getHiddenFieldsOneClick();
        
        $hiddenFields['credit_card_store'] = 1;
        $hiddenFields['credit_card_customer_id'] = 0;
        
        return $hiddenFields;
    }
    
    /**
     * 
     * @return array
     */
    protected function getHiddenFieldsMasterPass() {
        $hiddenFields = $this->getHiddenFields();
        
        $hiddenFields['channel'] = 71;
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
        
        $hiddenFields['channel'] = 73;
        $hiddenFields['ch_lock'] = 1;
        $hiddenFields['type'] = 4;
        
        return $hiddenFields;
    }
    
    /**
     * 
     * @param string $type
     * @param int $channel
     * @param string $blik
     * @return string
     */
    protected function buildSignature4Request($type, $channel = null, $blik = null) {
        switch ($type) {
            case 'mp':
                $hiddenFields = $this->getHiddenFieldsMasterPass();
                break;
            case 'blik':
                $hiddenFields = $this->getHiddenFieldsBlik();
                break;
            case 'dotpay':
            default:
                $hiddenFields = $this->getHiddenFieldsDotpay();
        }
        
        
        $fieldsRequestArray = array(
            'DOTPAY_PIN' => $this->_model->getConfigData('pin'),
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
            'blik_code' => self::STR_EMPTY
        );
        
         if('mp' === $type && $this->_model->isDotpayMasterPass()) {
            if(isset($channel)) {
                $fieldsRequestArray['channel'] = $channel;
            }
            $fieldsRequestArray['bylaw'] = '1';
            $fieldsRequestArray['personal_data'] = '1';
        } elseif('blik' === $type && $this->_model->isDotpayBlik()) {
            if(isset($channel)) {
                $fieldsRequestArray['channel'] = $channel;
            }
            if(isset($blik)) {
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
