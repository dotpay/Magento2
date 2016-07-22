<?php

namespace Dotpay\Dotpay\Block\Adminhtml\System\Config;

/**
 * Empty block to Magento Admin Config
 */
class AbstractBlock extends \Magento\Config\Block\System\Config\Form\Field {
    /**
     * Prepare block
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return $this->_decorateRowHtml($element, $this->_getElementHtml($element));
    }
}