<?php

namespace Dotpay\Dotpay\Block\Adminhtml\System\Config;

/**
 * Block with Dotpay Config Intro
 */
class Intro extends AbstractBlock {
    /**
     * Path to block template
     */
    const WIZARD_TEMPLATE = 'system/config/intro.phtml';

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::WIZARD_TEMPLATE);
        }
        return $this;
    }

    /**
     * Get the block html content
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $this->addData(
            [
                'test_data' => 'tomek'
            ]
        );
        return $this->_toHtml();
    }
}