<?php

/**
 * Dotpay action /dotpay/processing/widget
 */

namespace Dotpay\Dotpay\Controller\Processing;

use Dotpay\Dotpay\Controller\Dotpay;

class Back extends Dotpay {
    
    protected $_urlBuilder;

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
        $this->_urlBuilder = $context->getUrl();
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
        if($this->getRequest()->getParam('error_code'))
        {
            $message = "";
            switch($this->getRequest()->getParam('error_code'))
            {
                case 'PAYMENT_EXPIRED':
                    $message = __('Exceeded expiration date of the generated payment link.');
                    break;
                case 'UNKNOWN_CHANNEL':
                    $message = __('Selected payment channel is unknown.');
                    break;
                case 'DISABLED_CHANNEL':
                    $message = __('Selected channel payment is desabled.');
                    break;
                case 'BLOCKED_ACCOUNT':
                    $message = __('Account is disabled.');
                    break;
                case 'INACTIVE_SELLER':
                    $message = __('Seller account is inactive.');
                    break;
                case 'AMOUNT_TOO_LOW':
                    $message = __('Amount is too low.');
                    break;
                case 'AMOUNT_TOO_HIGH':
                    $message = __('Amount is too high.');
                    break;
                case 'BAD_DATA_FORMAT':
                    $message = __('Data format is bad.');
                    break;
                case 'HASH_NOT_EQUAL_CHK':
                    $message = __('Request has been modified during transmission.');
                    break;
                default:
                    $message = __('Unknown error.');
            }
            $this->_coreRegistry->register("dataWidget", array(
                "errorMessage"=>$message,
                "order"=>__("Order ID: ").$this->_checkoutSession->getLastRealOrder()->getRealOrderId(),
                "siteTitle"=>__('Payment error'),
                "textInUrl"=>__('Back to shop'),
                "backUrl"=>$this->_urlBuilder->getUrl('checkout/cart', ['_secure' => true])
            ));
            return $this->_resultPageFactory->create();
        }
        
        if (!$this->getRequest()->getParam('status')) {
            return $this->_redirect('customer/account');
        }

        if ($this->getRequest()->getParam('status') == 'OK') {
            $this->_redirect('checkout/onepage/success');
        } else {
            $this->_redirect('checkout/onepage/failure');
        }
    }
}
