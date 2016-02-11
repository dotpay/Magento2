<?php

/**
 * Dotpay abstract controller
 */

namespace Dotpay\Dotpay\Controller;

abstract class Dotpay extends \Magento\Framework\App\Action\Action {
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;
    
    /**
     * @var \Dotpay\Dotpay\Model\Payment
     */
    protected $_model;
    
    /**
     * Locale Resolver
     *
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $localeResolver;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Dotpay\Dotpay\Model\Payment $model
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context
    , \Magento\Customer\Model\Session $customerSession
    , \Magento\Checkout\Model\Session $checkoutSession
    , \Magento\Sales\Model\OrderFactory $orderFactory
    , \Dotpay\Dotpay\Model\Payment $model
    , \Magento\Framework\Locale\Resolver $localeResolver
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_model = $model;
        $this->localeResolver = $localeResolver;

        parent::__construct($context);
    }
}
