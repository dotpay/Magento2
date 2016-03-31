<?php
/**
 * Dotpay payment method model
 */

namespace Dotpay\Dotpay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Quote\Api\Data\CartInterface;

class Payment extends \Magento\Payment\Model\Method\AbstractMethod implements ConfigProviderInterface
{
    const CODE = 'dotpay_dotpay';

    protected $_code = self::CODE;

    protected $_countryFactory;

    protected $_minAmount = null;
    protected $_maxAmount = null;
    
    protected $_storeManager;
    
    protected $_agreements = true;
    
    /**
     * @var Config
     */
    protected $config;
    
    protected $_supportedCurrencyCodes = array(
        'PLN'
        , 'EUR'
        , 'USD'
        , 'GBP'
        , 'JPY'
        , 'CZK'
        , 'SEK'
        , 'DKK'
    );
    
    protected  $_resource;
    
    protected $_deploymentConfig;
    
    protected $_connection;
    
    protected $_tablePrefix;
    
    protected $_tableOneClick = 'dotpay_oneclick';
    
    protected $_checkTableOneClick = false;
    
    protected $_customerId = null;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        array $data = array()
        
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );
        
        $this->_countryFactory = $countryFactory;

        $this->_minAmount = $this->getConfigData('min_order_total');
        $this->_maxAmount = $this->getConfigData('max_order_total');
        
        $this->_storeManager = $storeManager;
        
        $this->_resource = $resource;
        $this->_deploymentConfig = $deploymentConfig;
        
        /**
         * One-Click
         */
        $this->getDBConnection();
        $this->getDBTablePrefix();
        $this->createTableOneClick();
        $this->_checkTableOneClick = $this->checkTableOneClick();
    }
    
    /**
     * 
     */
    public function setCustomerID($userId) {
        if(!isset($this->_customerId)) {
            $this->_customerId = (int) $userId;
        }
    }
    
    /**
     * 
     */
    public function getCustomerID() {
        return $this->_customerId;
    }

    /**
     * Determine method availability based on quote amount and config data
     *
     * @param null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        
        if ($quote && (
            $quote->getBaseGrandTotal() < $this->_minAmount
            || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
        ) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    /**
     * Availability for currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                'dotpay' => [
                    'paymentAcceptanceMarkSrc' => $this->getPaymentMarkImageUrl()
                ]
            ]
        ];
        return $config;
    }
    
    /**
     * Get Dotpay "mark" image URL
     *
     * @return string
     */
    public function getPaymentMarkImageUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC);
        return $baseUrl . 'frontend/Magento/luma/en_US/Dotpay_Dotpay/img/dotpay.gif';
    }
    
    /**
     * Get Dotpay "MasterPass" image URL
     *
     * @return string
     */
    public function getPaymentOneClickImageUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC);
        return $baseUrl . 'frontend/Magento/luma/en_US/Dotpay_Dotpay/img/dotpay.png';
    }
    
    /**
     * Get Dotpay "MasterPass" image URL
     *
     * @return string
     */
    public function getPaymentMasterPassImageUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC);
        return $baseUrl . 'frontend/Magento/luma/en_US/Dotpay_Dotpay/img/MasterPass.png';
    }
    
    /**
     * Get Dotpay "Blik" image URL
     *
     * @return string
     */
    public function getPaymentBlikImageUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC);
        return $baseUrl . 'frontend/Magento/luma/en_US/Dotpay_Dotpay/img/BLIK.png';
    }
    
    /**
     * Get Dotpay "Dotpay" image URL
     *
     * @return string
     */
    public function getPaymentDotpayImageUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC);
        return $baseUrl . 'frontend/Magento/luma/en_US/Dotpay_Dotpay/img/dotpay.png';
    }
    
    /**
     * 
     * @return boolean
     */
    public function isDotpayTest() {
        $result = false;
        
        if (1 === (int) $this->getConfigData('test')) {
            $result = true;
        }
        
        return $result;
    }
    
    public function isDotpayOneClick() {
        $result = false;
        
        if (1 === (int) $this->getConfigData('oneclick')) {
            $result = true;
        }
        if(false === $this->_agreements) {
            $result = false;
        }
        if(null === $this->getCustomerID() || 0 === $this->getCustomerID()) {
            $result = false;
        }
        
        return $result;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isDotpayMasterPass() {
        $result = false;
        
        if (1 === (int) $this->getConfigData('masterpass')) {
            $result = true;
        }
        
        if(false === $this->_agreements) {
            $result = false;
        }
        
        return $result;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isDotpayBlik() {
        $result = false;
        
        if (1 === (int) $this->getConfigData('blik')) {
            $result = true;
        }
        
        if(false === $this->_agreements) {
            $result = false;
        }
        
        return $result;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isDotpayWidget() {
        $result = false;
        
        if (1 === (int) $this->getConfigData('widget')) {
            $result = true;
        }
        
        if(false === $this->_agreements) {
            $result = false;
        }
        
        return $result;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isDotpaySecurity() {
        $result = false;
        
        if (1 === (int) $this->getConfigData('security')) {
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * 
     */
    public function disableAgreements() {
        $this->_agreements = false;
    }
    
    /**
     * 
     */
    private function getDBConnection() {
        if(!isset($this->_connection)) {
            $this->_connection = $this->_resource->getConnection(
                \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
            );
        }
    }
    
    /**
     * 
     */
    private function getDBTablePrefix() {
        if(!isset($this->_tablePrefix)) {
            $this->_tablePrefix = $this->_deploymentConfig->get(
                \Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX
            );
        }
    }
    
     /**
     * 
     */
    private function createTableOneClick() {
        
        $sqlCreateTable = <<<END
            CREATE TABLE IF NOT EXISTS `{$this->_tablePrefix}{$this->_tableOneClick}` (
                `oneclick_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `oneclick_order` bigint(20) NOT NULL,
                `oneclick_user` bigint(20) NOT NULL,
                `oneclick_card_title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                `oneclick_card_hash` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                `oneclick_card_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                PRIMARY KEY (`oneclick_id`),
                UNIQUE KEY `oneclick_card_hash` (`oneclick_card_hash`),
                UNIQUE KEY `oneclick_order` (`oneclick_order`),
                UNIQUE KEY `oneclick_card_id` (`oneclick_card_id`),
                KEY `oneclick_user` (`oneclick_user`),
                KEY `oneclick_card_title` (`oneclick_card_title`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

END;
            
        try {
            $this->_connection->rawQuery($sqlCreateTable);
        } catch (Exception $exc) {
           /**
            * NOP
            */
        }
    }
    
    /**
     * 
     */
    private function checkTableOneClick() {
        $ok = false;
        
        $results = $this->_connection->fetchAll('SHOW TABLES');
        foreach ($results as $tables) {
            foreach ($tables as $table) {
                if($table === "{$this->_tablePrefix}{$this->_tableOneClick}") {
                   $ok = true;
                   break 2;
                }
            }
        }
        
        return $ok;
    }
    
    /**
     * 
     */
    public function getConnection() {
        return $this->_connection;
    }
    
    /**
     * 
     */
    public function getTablePrefix() {
        return $this->_tablePrefix;
    }
    
    /**
     * 
     */
    public function cardList() {
        
        $sql = <<<END
            SELECT *
            FROM {$this->_tablePrefix}{$this->_tableOneClick}
            WHERE
                oneclick_user = '{$this->getCustomerID()}'
                AND
                oneclick_card_id IS NOT NULL
            ;

END;
            $results = array();
            if($this->_checkTableOneClick) {
                $results = $this->_connection->fetchAll($sql);
            }
            
            return $results;
    }
    
    /**
     * 
     */
    private function generateCardHash() {
        $microtime = '' . microtime(true);
        $md5 = md5($microtime);
        
        $mtRand = mt_rand(0, 11);
        
        $md5Substr = substr($md5, $mtRand, 21);
        
        $a = substr($md5Substr, 0, 6);
        $b = substr($md5Substr, 6, 5);
        $c = substr($md5Substr, 11, 6);
        $d = substr($md5Substr, 17, 4);
        
        return "{$a}-{$b}-{$c}-{$d}";
    }
    
    /**
     * 
     */
    public function cardAdd($orderId, $cardTitle) {
        $result = 0;
        
        if($this->_checkTableOneClick) {
            $count = 100;
            do {
                $cardHash = $this->generateCardHash();
                $test = $this->_connection->insert(
                    "{$this->_tablePrefix}{$this->_tableOneClick}"
                    , array( 
                        'oneclick_order' => "{$orderId}"
                        ,'oneclick_user' => "{$this->getCustomerID()}"
                        ,'oneclick_card_title' => "{$cardTitle}" 
                        ,'oneclick_card_hash' => "{$cardHash}" 
                    )
                    , array(
                        '%d'
                        ,'%d'
                        ,'%s'
                        ,'%s'
                    )
                );
                
                if(false !== $test) {
                    $result = $cardHash;
                    break;
                }
                
                $count--;
            } while($count);
        }
            
        return $result;
    }
    
    /**
     * 
     */
    public function cardGetIdByCardHash($cardHash) {
        $sql = <<<END
            SELECT *
            FROM {$this->_tablePrefix}{$this->_tableOneClick}
            WHERE
                oneclick_user = '{$this->getCustomerID()}'
                AND
                oneclick_card_hash = '{$cardHash}'
            LIMIT 1
            ;

END;
            
            $results = null;
            if($this->_checkTableOneClick) {
                $row = $this->_connection->fetchRow($sql);
                $results = isset($row['oneclick_id']) ? $row['oneclick_id'] : null;
            }
            
            return $results;
    }
    
    /**
     * 
     */
    public function cardGetCreditCardIdByCardHash($cardHash) {
        $sql = <<<END
            SELECT *
            FROM {$this->_tablePrefix}{$this->_tableOneClick}
            WHERE
                oneclick_user = '{$this->getCustomerID()}'
                AND
                oneclick_card_hash = '{$cardHash}'
            LIMIT 1
            ;

END;
            
            $results = null;
            if($this->_checkTableOneClick) {
                $row = $this->_connection->fetchRow($sql);
                $results = isset($row['oneclick_card_id']) ? $row['oneclick_card_id'] : null;
            }
            
            return $results;
    }
    
    /**
     * 
     */
    public function cardGetHashByOrderId($orderId) {
        $sql = <<<END
            SELECT *
            FROM {$this->_tablePrefix}{$this->_tableOneClick}
            WHERE
                oneclick_user = '{$this->getCustomerID()}'
                AND
                oneclick_order = '{$orderId}'
            LIMIT 1
            ;

END;
            
            $results = null;
            if($this->_checkTableOneClick) {
                $row = $this->_connection->fetchRow($sql);
                $results = isset($row['oneclick_card_hash']) ? $row['oneclick_card_hash'] : null;
            }
            
            return $results;
    }
    
    /**
     * 
     */
    public function cardRegister() {
        
    }
    
    /**
     * 
     */
    public function cardDel() {
        
    }
}
