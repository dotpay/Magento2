<?php

namespace Dotpay\Dotpay\Block;

use Magento\Catalog\Block as CatalogBlock;

/**
 * Dotpay checkout shortcut link
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Shortcut extends \Magento\Framework\View\Element\Template implements CatalogBlock\ShortcutInterface
{
    /**
     * Whether the block should be eventually rendered
     *
     * @var bool
     */
    protected $_shouldRender = true;
    
    /**
     * Start express action
     *
     * @var string
     */
    protected $_startAction = '';

    /**
     * Shortcut alias
     *
     * @var string
     */
    protected $_alias = '';

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $_mathRandom;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Checkout\Model\Session $checkoutSession = null,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_mathRandom = $mathRandom;
        $this->_localeResolver = $localeResolver;

        $this->_startAction = 'checkout';
        $this->setTemplate('shortcut.phtml');

        parent::__construct($context, $data);
        $this->currentCustomer = $currentCustomer;
    }

    /**
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _beforeToHtml()
    {
        $result = parent::_beforeToHtml();

        // set misc data
        $this->setShortcutHtmlId(
            $this->_mathRandom->getUniqueHash('dotpay_shortcut_')
        )->setCheckoutUrl(
            $this->getUrl($this->_startAction)
        );

        return $result;
    }

    /**
     * Render the block if needed
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_shouldRender) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Check is "OR" label position before shortcut
     *
     * @return bool
     */
    public function isOrPositionBefore()
    {
        return $this->getShowOrPosition() == CatalogBlock\ShortcutButtons::POSITION_BEFORE;
    }

    /**
     * Check is "OR" label position after shortcut
     *
     * @return bool
     */
    public function isOrPositionAfter()
    {
        return $this->getShowOrPosition() == CatalogBlock\ShortcutButtons::POSITION_AFTER;
    }

    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->_alias;
    }
}
