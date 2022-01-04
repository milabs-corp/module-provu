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
                type: 'provu',
                component: 'Milabs_Provu/js/view/payment/method-renderer/provu-method'
            }
        );
        return Component.extend({});
    }
);