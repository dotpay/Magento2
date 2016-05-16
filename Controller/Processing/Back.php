<?php

/**
 * Dotpay action /dotpay/processing/back
 */

namespace Dotpay\Dotpay\Controller\Processing;

use Dotpay\Dotpay\Controller\Dotpay;

class Back extends Dotpay {

    public function execute() {
        if (!$this->getRequest()->getParam('status')) {
            return $this->_redirect('noroute');
        }

        if ($this->getRequest()->getParam('status') == 'OK') {
            $this->_redirect('checkout/onepage/success');
        } else {
            $this->_redirect('checkout/onepage/failure');
        }
    }

}
