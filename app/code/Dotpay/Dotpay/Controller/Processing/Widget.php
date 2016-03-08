<?php

/**
 * Dotpay action /dotpay/processing/widget
 */

namespace Dotpay\Dotpay\Controller\Processing;

use Dotpay\Dotpay\Controller\Dotpay;

class Widget extends Dotpay {
    
    protected $_coreRegistry;
    
    protected $_resultPageFactory;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Dotpay\Dotpay\Model\Payment $model
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context
    , \Magento\Customer\Model\Session $customerSession
    , \Magento\Checkout\Model\Session $checkoutSession
    , \Magento\Sales\Model\OrderFactory $orderFactory
    , \Dotpay\Dotpay\Model\Payment $model
    , \Magento\Framework\Locale\Resolver $localeResolver
    , \Magento\Framework\Registry $coreRegistry
    , \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $pageFactory;

        parent::__construct(
            $context
            , $customerSession
            , $checkoutSession
            , $orderFactory
            , $model
            , $localeResolver
        );
    }

    public function execute() {
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Dotpay channels payment'));
        
        $this->_coreRegistry->register('dataWidget', array(
            'txtP' => __('You chose payment by Dotpay. Select a payment channel and click Continue do proceed'),
            'txtSubmit' => __('Continue'),
            'action' => $this->getDotAction(),
            'hiddenFields' => $this->getHiddenFields(),
        ));
        
        
        return $this->_resultPageFactory->create();
    }
    
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
     * @return string
     */
    protected function getDotType() {
        return 4;
    }
    
    /**
     * 
     * @return string
     */
    protected function getDotChLock() {
        return 1;
    }
}
