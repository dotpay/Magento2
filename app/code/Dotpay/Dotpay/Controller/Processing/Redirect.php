<?php

/**
 * Dotpay action /dotpay/processing/redirect
 */

namespace Dotpay\Dotpay\Controller\Processing;

use Dotpay\Dotpay\Controller\Dotpay;

class Redirect extends Dotpay {

    public function execute() {
        
        $dotAction = $this->getDotAction();
        $dotId = $this->getDotId();
        $dotControl = $this->getDotControl();
        $dotPinfo = $this->getDotPinfo();
        $dotAmount = $this->getDotAmount();
        $dotCurrency = $this->getDotCurrency();
        $dotDescription = $this->getDotDescription();
        $dotLang = $this->getDotLang();
        $dotUrl = $this->getDotUrl();
        $dotUrlC = $this->getDotUrlC();
        $dotApiVersion = $this->getDotApiVersion();
        $dotType = $this->getDotType();
        $dotFirstname = $this->getDotFirstname();
        $dotLastname = $this->getDotLastname();
        $dotEmail = $this->getDotEmail();
        

        $form = <<<END
<form id="redirectDotpay" action="{$dotAction}" method="POST">
    <input type="hidden" name="id" value="{$dotId}" />
    <input type="hidden" name="control" value="{$dotControl}" />
    <input type="hidden" name="p_info" value="{$dotPinfo}" />
    <input type="hidden" name="amount" value="{$dotAmount}" />
    <input type="hidden" name="currency" value="{$dotCurrency}" />
    <input type="hidden" name="description" value="{$dotDescription}"/>
    <input type="hidden" name="lang" value="{$dotLang}" />            
    <input type="hidden" name="URL" value="{$dotUrl}" />            
    <input type="hidden" name="URLC" value="{$dotUrlC}" />            
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
    
    /**
     * 
     * @return string
     */
    protected function getDotType() {
        return 0;
    }
}
