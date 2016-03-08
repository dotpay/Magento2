<?php
/**
 * Dotpay Widget block
 */
namespace Dotpay\Dotpay\Block;

/**
 * Customer account billing agreements block
 */
class Widget extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory $agreementCollection
     * @param \Magento\Paypal\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
    
    public function getFoo()
    {
        // will return 'bar'
        return $this->_coreRegistry->registry('foo');
    }
}
