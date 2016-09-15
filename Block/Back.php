<?php
/**
 * Dotpay Widget block
 */
namespace Dotpay\Dotpay\Block;

/**
 * Customer account billing agreements block
 */
class Back extends \Magento\Framework\View\Element\Template
{
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
        $this->_coreRegistry = $coreRegistry;
        $this->_isScopePrivate = true;
        parent::__construct($context, $data);
    }
    
    /**
     * 
     * @return string
     */
    public function getDataWidget() {
        return $this->_coreRegistry->registry('dataWidget');
    }
}
