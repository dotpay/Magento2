<?php

/**
 * Dotpay action /dotpay/processing/widget
 */

namespace Dotpay\Dotpay\Controller\Oneclick;

use Dotpay\Dotpay\Controller\Dotpay;

class Manage extends Dotpay {
    
    protected $_customerSession;
    
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
        $this->_customerSession = $customerSession;
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
        if(!$this->_customerSession->isLoggedIn())
            $this->_redirect('customer/account/login');
        $cards = $this->_model->cardList();
        $this->_coreRegistry->register('dataWidget', array(
            'cards' => $cards,
            'urlRemove' => $this->getDotUrlOneClickRemove(),
            'removeTxtCard' => __("Remove card"),
            'confirmTxtTitle' => __("Card remove confirm"),
            'cancelTxt' => __("Cancel"),
            'confirmTxt' => __("Confirm"),
            'numberTxt' => __("Card number"),
            'brandTxt' => __("Card brand name"),
            'removeTxt' => __("Usuń"),
            'errorTxt' => __("Error"),
            'removeQuestion' => __("Do you want to remove the card"),
            'errorMsg' => __("Unable to remove the card."),
            'title' => __('Dotpay One-Click card manage')
        ));
        
        return $this->_resultPageFactory->create();
    }
}
