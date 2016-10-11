<?php

/**
 * Dotpay action /dotpay/processing/widget
 */

namespace Dotpay\Dotpay\Controller\Processing;

use Dotpay\Dotpay\Controller\Dotpay;
use Dotpay\Dotpay\Tool\SellerApi;
use Dotpay\Dotpay\Tool\Curl;
use \Dotpay\Dotpay\Model\Payment;

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
            $context,
            $customerSession,
            $checkoutSession,
            $orderFactory,
            $model,
            $localeResolver
        );
    }

    public function execute() {
        if(!$this->_checkoutSession->getLastRealOrder()->getEntityId()) {
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
        }
        
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
         * hidden fields One-Click, MasterPass, BLIK, Dotpay
         */
        $hiddenFields = array();
        $disabledChannels = array();
        
        $channelsApiData = $this->getApiChannels();
        
         /**
         * One-Click Cards
         */
        if($this->_model->isDotpayOneClick() && $this->getChannelData($channelsApiData, Payment::$ocChannel))
        {
            $disabledChannels[] = Payment::$ocChannel;
            $ocAgreements = $agreements;
            $ocAgreements['oneclick'] = $this->_model->getConfigData('oneclick_agreement');
            $hiddenFields['oneclick'] = array(
                'active' => $this->_model->isDotpayOneClick(),
                'fields' => $this->getHiddenFieldsOneClick(),
                'agreements' => $ocAgreements,
                'icon' => $this->_model->getPaymentOneClickImageUrl(),
                'text' => 'One-Click',
                'registerText' => __('Card register'),
                'selectText' => __('Card select'),
                'action' => $this->getDotUrlOneClickPreparing(),
                'manageUrl' => $this->getDotUrlOneClickManage(),
                'cards' => $this->_model->cardList()
            );
        }
        
        /**
         * PV card channel
         */
        if($this->_model->isDotpayPV() && $this->isDotSelectedCurrency($this->getDotCurrenciesForPV()) && $this->getChannelData($this->getApiChannels(true), Payment::$pvChannel))
        {
            $disabledChannels[] = Payment::$mpChannel;
            $hiddenFields['pv'] = array(
                'active' => $this->_model->isDotpayPV(),
                'fields' => $this->getHiddenFieldsPV(),
                'agreements' => $agreements,
                'icon' => $this->_model->getPaymentOneClickImageUrl(),
                'text' => __('Card channel for your currency'),
                'action' => $this->getDotAction()
            );
        }
        
        /**
         * MasterPass
         */
        if($this->_model->isDotpayMasterPass() && $this->getChannelData($channelsApiData, Payment::$mpChannel))
        {
            $disabledChannels[] = Payment::$mpChannel;
            $hiddenFields['mp'] = array(
                'active' => $this->_model->isDotpayMasterPass(),
                'fields' => $this->getHiddenFieldsMasterPass(),
                'agreements' => $agreements,
                'icon' => $this->_model->getPaymentMasterPassImageUrl(),
                'text' => 'MasterPass (First Data Polska S.A.)',
                'action' => $this->getDotAction()
            );
        }
        
        /**
         * BLIK
         */
        if($this->_model->isDotpayTest())
            $bcFieldName = 'blik_code_fake';
        else
            $bcFieldName = 'blik_code';
        if($this->_model->isDotpayBlik() && $this->getChannelData($channelsApiData, Payment::$blikChannel))
        {
            $disabledChannels[] = Payment::$blikChannel;
            $hiddenFields['blik'] = array(
                'active' => $this->_model->isDotpayBlik(),
                'fields' => $this->getHiddenFieldsBlik(),
                'agreements' => $agreements,
                'icon' => $this->_model->getPaymentBlikImageUrl(),
                'text' => 'BLIK (Polski Standard Płatności Sp. z o.o.)',
                'action' => $this->getDotAction(),
                'bcFieldName' => $bcFieldName
            );
        }
        
        /**
         * Dotpay
         */
        $hiddenFields['dotpay'] = array(
            'active' => $this->_model->isDotpayWidget(),
            'fields' => $this->getHiddenFieldsDotpay(),
            'agreements' => $agreements,
            'icon' => $this->_model->getPaymentDotpayImageUrl(),
            'text' => '',
            'action' => $this->getDotAction()
        );
        
        /**
         * 
         */
        foreach($hiddenFields as $key => $val) {
            $oneclickCardTest = 'oneclick_card';
            $keySubstr = substr($key, 0, strlen($oneclickCardTest));

            if($oneclickCardTest === $keySubstr) {
                $chk = $this->buildSignature4Request($oneclickCardTest, null, null, $val['fields']['credit_card_customer_id']);
            } else {
                $chk = $this->buildSignature4Request($key);
            }

            $hiddenFields[$key]['fields']['chk'] = $chk;
        }

        /**
         * 
         */
        if($this->_model->isDotpayOneClick() || $this->_model->isDotpayMasterPass() || $this->_model->isDotpayBlik() || $this->_model->isDotpayWidget()) {
            $txtP = __('You chose payment by Dotpay. Select a payment channel and click Continue do proceed');
        } else {
            $txtP = __('You chose payment by Dotpay. Click Continue do proceed');
        }
        
        $this->_coreRegistry->register('dataWidget', array(
            'oneclick' => $this->_model->isDotpayOneClick(),
            'oneclickTxtValid' => __('6 or more characters'),
            'oneclickTxtPlaceholder' => __('Card title 6 or more characters'),
            'oneclickTxtSaveCard' => __('Remember your data card (Your card details are safely stored in Dotpay. There will be no need for them to enter the next payment in the store.)'),
            'mp' => $this->_model->isDotpayMasterPass(),
            'blik' => $this->_model->isDotpayBlik(),
            'blikTxtValid' => __('Only 6 digits'),
            'blikTxtPlaceholder' => __('Blik code 6 digits'),
            'widget' => $this->_model->isDotpayWidget(),
            'txtP' => $txtP,
            'txtSubmit' => __('Continue'),
            'hiddenFields' => $hiddenFields,
            'signatureUrl' => $this->getDotUrlSignature(),
            'oneclickRegisterUrl' => $this->getDotUrlOneClickRegister(),
            'txtSelectedChannel' => __('Selected payment channel'),
            'txtChangeChannel' => __('change the channel'),
            'txtAvChannels' => __('Available channels'),
            'disabledChannels' => implode(',', $disabledChannels),
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
            $curl = new Curl();
            $curl->addOption(CURLOPT_SSL_VERIFYPEER, false)
                 ->addOption(CURLOPT_HEADER, false)
                 ->addOption(CURLOPT_FOLLOWLOCATION, true)
                 ->addOption(CURLOPT_URL, $curl_url)
                 ->addOption(CURLOPT_REFERER, $curl_url)
                 ->addOption(CURLOPT_RETURNTRANSFER, true);
            $resultJson = $curl->exec();
        } catch (Exception $exc) {
            $resultJson = false;
        } finally {
            if($curl) {
                $curl->close();
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
