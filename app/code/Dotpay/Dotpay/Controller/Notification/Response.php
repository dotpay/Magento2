<?php

/**
 * Dotpay action /dotpay/notification/response
 */

namespace Dotpay\Dotpay\Controller\Notification;

use Dotpay\Dotpay\Controller\Dotpay;

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
        
        $order = $this->getOrder($this->fields['control']);
        
        $this->checkSignature($order);

        $lastStatus = $order->getStatus();
        if (\Magento\Sales\Model\Order::STATE_COMPLETE === $lastStatus || \Magento\Sales\Model\Order::STATE_CANCELED === $lastStatus) {
            die('OK');
        }

        $payment = $order->getPayment();
        $payment->setTransactionId(microtime(true));
        $transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_PAYMENT, null, false);
        $transaction->setParentTxnId(null);
        if (self::STATUS_COMPLETED === $this->fields['operation_status']) {
            $transaction->setIsClosed(1);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE);
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
            die('FAIL');
        }

        return $order;
    }

    protected function checkSignature($order) {
        $hashDotpay = $this->fields['signature'];
        $hashCalculate = $this->calculateSignature($order);

        if ($hashDotpay !== $hashCalculate) {
            die('FAIL');
        }
    }

    protected function checkRemoteIP() {
        $ips = $this->_model->getConfigData('authorized_ips');

        $realIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : '0.0.0.0';
        $remoteIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';

        if (!in_array($realIp, $ips) && !in_array($remoteIp, $ips)) {
            die('FAIL');
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

    protected function calculateSignature($order) {
        $string = '';
        $string .= $this->_model->getConfigData('pin');

        foreach ($this->fields as $k => $v) {
            switch ($k) {
                case 'signature':
                    /**
                     * NOP
                     */
                    break;
                case 'operation_original_amount':
                    $origAmount = round($order->getGrandTotal(), 2);
                    $string .= sprintf("%01.2f", $origAmount);
                    break;
                case 'operation_original_currency':
                     $string .= $order->getOrderCurrencyCode();
                    break;
                case 'email':
                     $string .= $order->getBillingAddress()->getEmail();
                    break;
                default:
                    $string .= $v;
            }
        }

        return hash('sha256', $string);
    }

}
