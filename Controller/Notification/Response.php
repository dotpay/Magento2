<?php

/**
 * Dotpay action /dotpay/notification/response
 */

namespace Dotpay\Dotpay\Controller\Notification;

use Dotpay\Dotpay\Controller\Dotpay;
use Dotpay\Dotpay\Tool\SellerApi;

class Response extends Dotpay {

    const STATUS_NEW = 'new';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PROCESSING_REALIZATION_WAITING = 'processing_realization_waiting';
    const STATUS_PROCESSING_REALIZATION = 'processing_realization';

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $_order;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction
     */
    protected $_transaction;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction
     */
    protected $_transactionResource;

    /**
     *
     * @var array
     */
    protected $fields = array(
        'id' => '',
        'operation_number' => '',
        'operation_type' => '',
        'operation_status' => '',
        'operation_amount' => '',
        'operation_currency' => '',
        'operation_withdrawal_amount' => '',
        'operation_commission_amount' => '',
        'operation_original_amount' => '',
        'operation_original_currency' => '',
        'operation_datetime' => '',
        'operation_related_number' => '',
        'control' => '',
        'description' => '',
        'email' => '',
        'p_info' => '',
        'p_email' => '',
        'channel' => '',
        'channel_country' => '',
        'geoip_country' => '',
        'signature' => ''
    );

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Dotpay\Dotpay\Model\Payment $model
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Sales\Model\OrderRepository $order
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context
    , \Magento\Customer\Model\Session $customerSession
    , \Magento\Checkout\Model\Session $checkoutSession
    , \Magento\Sales\Model\OrderFactory $orderFactory
    , \Dotpay\Dotpay\Model\Payment $model
    , \Magento\Framework\Locale\Resolver $localeResolver
    , \Magento\Sales\Model\OrderRepository $order
    ) {
        $this->_order = $order;

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
        $this->checkRemoteIP();
        $this->getPostParams();
        
        /**
         * check order
         */
        $order = $this->getOrder($this->fields['control']);
        
        /**
         * check currency, amount, email
         */
        $this->checkCurrency($order);
        $this->checkAmount($order);
        $this->checkEmail($order);
        
        /**
         * check signature
         */
        $this->checkSignature($order);

        $lastStatus = $order->getStatus();
        if (\Magento\Sales\Model\Order::STATE_COMPLETE === $lastStatus || \Magento\Sales\Model\Order::STATE_CANCELED === $lastStatus) {
            //die('OK');
        }

        $payment = $order->getPayment();
        $payment->setTransactionId(microtime(true));
        $transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_PAYMENT, null, false);
        $transaction->setParentTxnId(null);
        if (self::STATUS_COMPLETED === $this->fields['operation_status']) {
            $transaction->setIsClosed(1);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE);
            if($this->_model->isDotpayOneClick()) {
                $this->updateCardInfo($this->fields['operation_number'], $this->fields['control']);
            }
        } elseif (self::STATUS_REJECTED === $this->fields['operation_status']) {
            $transaction->setIsClosed(1);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
        } else {
            $transaction->setIsClosed(0);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
        }
        $transaction->setAdditionalInformation('info', serialize($this->fields));
        $transaction->save();
        $order->save();

        die('OK');
    }

    protected function getOrder($idOrder) {
        $order = $this->_order->get($idOrder);
        if (!$order) {
            die('FAIL ORDER: not exist');
        }

        return $order;
    }

    protected function checkSignature($order) {
        $hashDotpay = $this->fields['signature'];
        $hashCalculate = $this->calculateSignature();

        if ($hashDotpay !== $hashCalculate) {
            die('FAIL SIGNATURE');
        }
    }

    protected function checkRemoteIP() {
        $ips = $this->_model->getConfigData('authorized_ips');
        
        $remoteIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        $realIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : '0.0.0.0';
        
        if (in_array($remoteIp, $ips)) {
            /**
             * OK NOP
             */
        } else if($this->_model->getConfigData('test') && ($remoteIp == $this->_model->getConfigData('office_ip') || $remoteIp == self::LOCAL_IP)) {
            /**
             * OK NOP
             */
        } elseif (self::CHECK_REAL_IP && in_array($realIp, $ips) && $remoteIp === self::LOCAL_IP) {
            /**
             * OK NOP
             */
        } else {
            die('FAIL IP: access denied');
        }
    }

    protected function getPostParams() {
        foreach ($this->fields as $k => &$v) {
            $value = $this->getRequest()->getPost($k);
            if ($value !== '') {
                $v = $value;
            }
        }
    }
    
    protected function checkCurrency($order) {
        $currencyOrder = $order->getOrderCurrencyCode();
        $currencyResponse = $this->fields['operation_original_currency'];

        if ($currencyOrder !== $currencyResponse) {
            die('FAIL CURRENCY');
        }
    }
    
    protected function checkAmount($order) {
        $amount = round($order->getGrandTotal(), 2);
        $amountOrder = sprintf("%01.2f", $amount);
        $amountResponse = $this->fields['operation_original_amount'];

        if ($amountOrder !== $amountResponse) {
            die('FAIL AMOUNT');
        }
    }
    
    protected function checkEmail($order) {
        $emailBilling = $order->getBillingAddress()->getEmail();
        $emailResponse = $this->fields['email'];

        if ($emailBilling !== $emailResponse) {
            die('FAIL EMAIL');
        }
    }

    protected function calculateSignature() {
        $string = '';
        $string .= $this->_model->getConfigData('pin');

        foreach ($this->fields as $k => $v) {
            switch ($k) {
                case 'signature':
                    /**
                     * NOP
                     */
                    break;
                default:
                    $string .= $v;
            }
        }

        return hash('sha256', $string);
    }
    
    protected function updateCardInfo($payment, $order) {
        $api = new SellerApi($this->getDotSellerApiUrl());
        $cc = $api->getCreditCardByPayment($this->_model->getConfigData('dotpay_user'),
                                           $this->_model->getConfigData('dotpay_password'),
                                           $payment);
        if(!$cc) return;
            $this->_model->cardRegister($order, $cc->id, $cc->masked_number, $cc->brand->name);
    }
}
