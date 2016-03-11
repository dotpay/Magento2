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
        $hiddenFields = $this->getHiddenFields();
        
        $security = $this->_model->isDotpaySecurity();
        if(1 === $security) {
            $chk = $this->buildSignature4Request();
            $hiddenFields['CHK'] = $chk;
        }
        
        $this->_coreRegistry->register('dataWidget', array(
            'txtP' => __('You chose payment by Dotpay. Select a payment channel and click Continue do proceed'),
            'txtSubmit' => __('Continue'),
            'action' => $this->getDotAction(),
            'hiddenFields' => $hiddenFields,
            'agreement_bylaw' =>  $this->getDotpayAgreement('bylaw'),
            'agreement_personal_data' => $this->getDotpayAgreement('personal_data'),
            'signatureUrl' => $this->getDotUrlSignature(),
        ));
        
        /**
         * must be before return?
         */
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Dotpay channels payment'));
        
        return $this->_resultPageFactory->create();
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
    
    protected function getDotpayAgreement($what) {
        $resultStr = '';
        
        $dotpay_url = $this->getDotAction();
        $payment_currency = $this->getDotCurrency();
        
        $dotpay_id = $this->getDotId();
        
        $order_amount = $this->getDotAmount();
        
        $dotpay_lang = $this->getDotLang();
        
        $curl_url = "{$dotpay_url}payment_api/channels/";
        $curl_url .= "?currency={$payment_currency}";
        $curl_url .= "&id={$dotpay_id}";
        $curl_url .= "&amount={$order_amount}";
        $curl_url .= "&lang={$dotpay_lang}";
        
        /**
         * curl
         */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $curl_url);
        curl_setopt($ch, CURLOPT_REFERER, $curl_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resultJson = curl_exec($ch);
        curl_close($ch);
        
        /**
         * 
         */
        $result = json_decode($resultJson, true);

        foreach ($result['forms'] as $forms) {
            foreach ($forms['fields'] as $forms1) {
                if ($forms1['name'] == $what) {
                    $resultStr = $forms1['description_html'];
                }
            }
        }

        return $resultStr;
    }
}
