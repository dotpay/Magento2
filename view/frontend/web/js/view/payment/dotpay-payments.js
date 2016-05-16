/**
 * Dotpay Magento JS component
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'dotpay_dotpay',
                component: 'Dotpay_Dotpay/js/view/payment/method-renderer/dotpay-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);