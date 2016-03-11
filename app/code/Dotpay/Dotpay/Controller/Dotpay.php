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
     * @return array
     */
    protected function getHiddenFields() {
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
            'type' => $this->getDotType(),
            'ch_lock' => $this->getDotChLock(),
            'firstname' => $this->getDotFirstname(),
            'lastname' => $this->getDotLastname(),
            'email' => $this->getDotEmail()
        );
    }
    
    /**
     * 
     * @param array $hiddenFields
     * @param type $channel
     * @return type
     */
    protected function buildSignature4Request($channel = null) {
        $fieldsRequestArray = array(
            'DOTPAY_PIN' => $this->_model->getConfigData('pin'),
            'api_version' => $this->getDotApiVersion(),
            'lang' => $this->getDotLang(),
            'DOTPAY_ID' => $this->getDotId(),
            'amount' => $this->getDotAmount(),
            'currency' => $this->getDotCurrency(),
            'description' => $this->getDotDescription(),
            'control' => $this->getDotControl(),
            'channel' => self::STR_EMPTY,
            'ch_lock' => $this->getDotChLock(),
            'URL' => $this->getDotUrl(),
            'type' => $this->getDotType(),
            'buttontext' => self::STR_EMPTY,
            'URLC' => $this->getDotUrlC(),
            'firstname' => $this->getDotFirstname(),
            'lastname' => $this->getDotLastname(),
            'email' => $this->getDotEmail(),
            'street' => self::STR_EMPTY,
            'street_n1' => self::STR_EMPTY,
            'street_n2' => self::STR_EMPTY,
            'state' => self::STR_EMPTY,
            'addr3' => self::STR_EMPTY,
            'city' => self::STR_EMPTY,
            'postcode' => self::STR_EMPTY,
            'phone' => self::STR_EMPTY,
            'country' => self::STR_EMPTY,
            'bylaw' => self::STR_EMPTY,
            'personal_data' => self::STR_EMPTY,
            'blik_code' => self::STR_EMPTY
        );
        
        $widget = $this->_model->isDotpayWidget();
        
        if($channel) {
            $fieldsRequestArray['channel'] = $channel;
        }
        
        if(1 === $widget) {
            $fieldsRequestArray['bylaw'] = '1';
            $fieldsRequestArray['personal_data'] = '1';
        }
        
        $fieldsRequestStr = implode(self::STR_EMPTY, $fieldsRequestArray);
        
        return hash('sha256', $fieldsRequestStr);
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotChLock() {
        return 0;
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotType() {
        return 0;
    }
}
