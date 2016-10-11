<?php

namespace Dotpay\Dotpay\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface {
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->getConnection()->dropTable('dotpay_oneclick');
    }
}