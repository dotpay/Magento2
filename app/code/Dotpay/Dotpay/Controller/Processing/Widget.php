<?php

/**
 * Dotpay action /dotpay/processing/widget
 */

namespace Dotpay\Dotpay\Controller\Processing;

use Dotpay\Dotpay\Controller\Dotpay;

class Widget extends Dotpay {
    
    protected $_coreRegistry;
    
    protected $_resultPageFactory;
    
    protected $agreementByLaw = '';
    
    protected $agreementPersonalData = '';
    
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
        /**
         * 
         */
        $this->agreementByLaw = $this->getDotpayAgreement('bylaw');
        $this->agreementPersonalData = $this->getDotpayAgreement('personal_data');
        
        /**
         * 
         */
        $agreements = array(
            'bylaw' => $this->agreementByLaw,
            'personal_data' => $this->agreementPersonalData,
        );
        
         /**
         * hidden fields MasterPass, BLIK, Dotpay
         */
        $hiddenFields = array(
            'mp' => array(
                'active' => $this->_model->isDotpayMasterPass(),
                'fields' => $this->getHiddenFieldsMasterPass(),
                'agreements' => $agreements,
                'icon' => $this->getIconMasterPass(),
            ),
            'blik' => array(
                'active' => $this->_model->isDotpayBlik(),
                'fields' => $this->getHiddenFieldsBlik(),
                'agreements' => $agreements,
                'icon' => $this->getIconBLIK(),
            ),
            'dotpay' => array(
                'active' => $this->_model->isDotpayWidget(),
                'fields' => $this->getHiddenFieldsDotpay($order_id),
                'agreements' => $agreements,
                'icon' => $this->getIconDotpay(),
            ),
        );
        
        /**
         * 
         */
        if($this->_model->isDotpaySecurity()) {
            foreach($hiddenFields as $key => $val) {
                $chk = $this->buildSignature4Request($hiddenFields, $key);
                
                if(!isset($_SESSION['hiddenFields'])) {
                    $_SESSION['hiddenFields'] = array();
                }
                
                $_SESSION['hiddenFields'][$key] = $val;
                
                $hiddenFields[$key]['fields']['CHK'] = $chk;
            }
        }
        
        /**
         * 
         */
        if($this->_model->isDotpayMasterPass() || $this->_model->isDotpayBlik() || $this->_model->isDotpayWidget()) {
            /**
             * 
             */
            $txtP = __('You chose payment by Dotpay. Select a payment channel and click Continue do proceed', 'dotpay-payment-gateway');
        } else {
            $txtP = __('You chose payment by Dotpay. Click Continue do proceed', 'dotpay-payment-gateway');
        }
        
        $this->_coreRegistry->register('dataWidget', array(
            'txtP' => $txtP,
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
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_URL, $curl_url);
            curl_setopt($ch, CURLOPT_REFERER, $curl_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $resultJson = curl_exec($ch);
        } catch (Exception $exc) {
            $resultJson = false;
        } finally {
            if($ch) {
                curl_close($ch);
            }
        }
        
        /**
         * 
         */
        if(false !== $resultJson) {
            $result = json_decode($resultJson, true);

            if (isset($result['forms']) && is_array($result['forms'])) {
                foreach ($result['forms'] as $forms) {
                    if (isset($forms['fields']) && is_array($forms['fields'])) {
                        foreach ($forms['fields'] as $forms1) {
                            if ($forms1['name'] == $what) {
                                $resultStr = $forms1['description_html'];
                            }
                        }
                    }
                }
            }
        }
        
        if($resultStr === '') {
            $this->_model->disableAgreements();
        }

        return $resultStr;
    }
}
