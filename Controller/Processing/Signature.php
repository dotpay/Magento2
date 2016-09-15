<?php

/**
 * Dotpay action /dotpay/processing/widget
 */

namespace Dotpay\Dotpay\Controller\Processing;

use Dotpay\Dotpay\Controller\Dotpay;

class Signature extends Dotpay {
    
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
        $chk = '';
        
        $type = $this->getRequest()->getParam('type');
        $channel = $this->getRequest()->getParam('channel');
        
        switch ($type) {
            case 'mp':
                $chk = $this->buildSignature4Request($type, $channel);
                break;
            case 'blik':
                if($this->_model->isDotpayTest())
                    $blik = NULL;
                else
                    $blik = $this->getRequest()->getParam('blik');
                $chk = $this->buildSignature4Request($type, $channel, $blik);
                break;
            case 'dotpay':
                if(!$this->_model->isDotpayWidget()) {
                    $this->_model->disableAgreements();
                }
                $chk = $this->buildSignature4Request($type, $channel);
                break;
            default:
        } 
        
        die($chk);
    }
}

