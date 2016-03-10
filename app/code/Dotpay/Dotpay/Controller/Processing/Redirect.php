<?php

/**
 * Dotpay action /dotpay/processing/redirect
 */

namespace Dotpay\Dotpay\Controller\Processing;

use Dotpay\Dotpay\Controller\Dotpay;

class Redirect extends Dotpay {

    public function execute() {
        
        $dotAction = $this->getDotAction();
        
        $hiddenFields = $this->getHiddenFields();
        
        $fields = '';
        foreach($hiddenFields as $k => $v) {
            $fields .= $this->genField($k, $v);
        }

        $form = <<<END
<form id="redirectDotpay" action="{$dotAction}" method="POST">
    {$fields}
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
    
    protected function genField($name, $value) {
        return <<<END
            <input type="hidden" name="{$name}" value="{$value}" />

END;
    }
}
