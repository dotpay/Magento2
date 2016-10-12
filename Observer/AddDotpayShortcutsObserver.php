<?php

namespace Dotpay\Dotpay\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Dotpay\Dotpay\Model\Payment as DotpayConfig;

/**
 * Dotpay module observer
 */
class AddDotpayShortcutsObserver implements ObserverInterface {
    /**
     * @var DotpayConfig
     */
    protected $dotpayConfig;

    /**
     * Constructor
     *
     * @param DotpayConfig $dotpayConfig
     */
    public function __construct(
        DotpayConfig $dotpayConfig
    ) {
        $this->dotpayConfig = $dotpayConfig;
    }

    /**
     * Add Dotpay shortcut buttons
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer) {
        $shortcutButtons = $observer->getEvent()->getContainer();
        
        $params = [
        ];

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