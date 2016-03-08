<?php

/**
 * Dotpay abstract controller
 */

namespace Dotpay\Dotpay\Controller;

abstract class Dotpay extends \Magento\Framework\App\Action\Action {
    
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
}
