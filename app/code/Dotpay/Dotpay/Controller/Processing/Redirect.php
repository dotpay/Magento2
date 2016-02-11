<?php

/**
 * Dotpay action /dotpay/processing/redirect
 */

namespace Dotpay\Dotpay\Controller\Processing;

use Dotpay\Dotpay\Controller\Dotpay;

class Redirect extends Dotpay {

    public function execute() {
        
        $dotTest = (int) $this->_model->getConfigData('test');
        $dotAction = $this->_model->getConfigData('redirect_url');
        if(1 === $dotTest) {
            $dotAction = $this->_model->getConfigData('redirect_url_test');
        }
        $dotId = $this->_model->getConfigData('id');
        $dotControl = $this->_checkoutSession->getLastRealOrder()->getEntityId();
        $dotPinfo = "Sklep - {$_SERVER['HTTP_HOST']}";
        $amount = round($this->_checkoutSession->getLastRealOrder()->getGrandTotal(), 2);
        $dotAmount = sprintf("%01.2f", $amount);
        $dotCurrency = $this->_checkoutSession->getLastRealOrder()->getOrderCurrencyCode();
        $dotDescription = __("Order ID: %1", $this->_checkoutSession->getLastRealOrder()->getRealOrderId());
        $lang = \Locale::getRegion($this->localeResolver->getLocale());
        $dotLang = strtolower($lang);
        $dorUrl = "http://{$_SERVER['HTTP_HOST']}/dotpay/processing/back";
        $dorUrlC = "http://{$_SERVER['HTTP_HOST']}/dotpay/notification/response";
        $dotApiVersion = 'dev';
        $dotType = 0;
        $dotFirstname = $this->_checkoutSession->getLastRealOrder()->getBillingAddress()->getFirstname();
        $dotLastname = $this->_checkoutSession->getLastRealOrder()->getBillingAddress()->getLastname();
        $dotEmail = $this->_checkoutSession->getLastRealOrder()->getBillingAddress()->getEmail();
        

        $form = <<<END
<form id="redirectDotpay" action="{$dotAction}" method="GET">
    <input type="hidden" name="id" value="{$dotId}" />
    <input type="hidden" name="control" value="{$dotControl}" />
    <input type="hidden" name="p_info" value="{$dotPinfo}" />
    <input type="hidden" name="amount" value="{$dotAmount}" />
    <input type="hidden" name="currency" value="{$dotCurrency}" />
    <input type="hidden" name="description" value="{$dotDescription}"/>
    <input type="hidden" name="lang" value="{$dotLang}" />            
    <input type="hidden" name="URL" value="{$dorUrl}" />            
    <input type="hidden" name="URLC" value="{$dorUrlC}" />            
    <input type="hidden" name="api_version" value="{$dotApiVersion}" />            
    <input type="hidden" name="type" value="{$dotType}" />      
    <input type="hidden" name="firstname" value="{$dotFirstname}" />      
    <input type="hidden" name="lastname" value="{$dotLastname}" />      
    <input type="hidden" name="email" value="{$dotEmail}" />      
</form>
                
END;
                
    $html =<<<END
<!doctype html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Redirect to Dotpay</title>
    </head>
    <body>
        {$form}
        <script type="text/javascript">
            document.getElementById('redirectDotpay').submit();
        </script>
    </body>
</html>

END;

        $this->getResponse()->setBody($html);
    }
}
