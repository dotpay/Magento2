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
     * @var bool
     */
    protected $_isScopePrivate;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry
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
        $this->_isScopePrivate = true;
        parent::__construct($context, $data);
    }
    
    /**
     * 
     * @return array
     */
    public function getDataWidget()
    {
        return $this->_coreRegistry->registry('dataWidget');
    }
}
