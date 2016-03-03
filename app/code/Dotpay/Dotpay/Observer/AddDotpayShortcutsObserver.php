<?php

namespace Dotpay\Dotpay\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Dotpay\Dotpay\Model\Payment as DotpayConfig;

/**
 * PayPal module observer
 */
class AddDotpayShortcutsObserver implements ObserverInterface
{
    /**
     * @var \Dotpay\Dotpay\Helper\Shortcut\Factory
     */
    protected $shortcutFactory;

    /**
     * @var DotpayConfig
     */
    protected $dotpayConfig;

    /**
     * Constructor
     *
     * @param \Dotpay\Dotpay\Helper\Shortcut\Factory $shortcutFactory
     * @param DotpayConfig $dotpayConfig
     */
    public function __construct(
//        \Dotpay\Dotpay\Helper\Shortcut\Factory $shortcutFactory,
        DotpayConfig $dotpayConfig
    ) {
//        $this->shortcutFactory = $shortcutFactory;
        $this->dotpayConfig = $dotpayConfig;
    }

    /**
     * Add PayPal shortcut buttons
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        
        $params = [
        ];

        // we believe it's \Magento\Framework\View\Element\Template
        $shortcut = $shortcutButtons->getLayout()->createBlock(
            'Dotpay\Dotpay\Block\Shortcut',
            '',
            $params
        );
        $shortcut->setIsInCatalogProduct(
            $observer->getEvent()->getIsCatalogProduct()
        )->setShowOrPosition(
            $observer->getEvent()->getOrPosition()
        );
        $shortcutButtons->addShortcut($shortcut);
    }
}
