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
                    'paymentAcceptanceMarkSrc' => $this->getPaymentMarkImageUrl(),
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
}