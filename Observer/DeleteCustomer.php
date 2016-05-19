<?php

namespace Dotpay\Dotpay\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Dotpay\Dotpay\Model\Payment as DotpayConfig;

/**
 * Dotpay oneclick delete customer observer
 */
class DeleteCustomer implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Constructor
     *
     * @param DotpayConfig $dotpayConfig
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * Add Dotpay shortcut buttons
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $customerId = $observer->getEvent()->getCustomer()->getCustomerId();
        $paymentModel = $this->_objectManager->create('Dotpay\Dotpay\Model\Payment');
        $paymentModel->cardDeleteForCustomer($customerId);
    }
}
