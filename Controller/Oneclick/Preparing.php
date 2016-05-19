<?php

/**
 * Dotpay action /dotpay/processing/widget
 */

namespace Dotpay\Dotpay\Controller\Oneclick;

use Dotpay\Dotpay\Controller\Dotpay;

class Preparing extends Dotpay {
    
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
        $case = $this->getRequest()->getParam('card_case');
        $hiddenFields = array();
        if($case=='register')
        {
            $orderId = $this->getLastOrderId();
            $cardHash = $this->_model->cardAdd($orderId);
            $hiddenFields = $this->getHiddenFieldsOneClickRegister($cardHash);
        }
        else
        {
            $card = $this->getRequest()->getParam('card_select');
            $ccData = $this->_model->cardGetDataByOneclickId($card);
            $hiddenFields = $this->getHiddenFieldsOneClickCard($ccData['oneclick_card_hash'], $ccData['oneclick_card_id']);
        }
        if($this->_model->isDotpaySecurity()) {
            $type = ($case=='register')?'_register':'';
            $hiddenFields['chk'] = $this->buildSignature4Request('oneclick'.$type, null, null, empty($hiddenFields['credit_card_customer_id'])?null:$hiddenFields['credit_card_customer_id']);
        }
        $this->_coreRegistry->register('dataWidget', array(
            'hiddenFields' => $hiddenFields,
            'action' => $this->getDotAction()
        ));
        
        return $this->_resultPageFactory->create();
    }
}
