define(
    [
        'underscore',
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/cart/totals-processor/default',
        'Magento_Checkout/js/model/cart/cache',
        'Magento_Customer/js/customer-data',
        'mage/url',
        'mage/calendar',
        'mage/translate'
    ],
    function (
        _,
        $,
        ko,
        quote,
        priceUtils,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        customer,
        checkoutData,
        creditCardData,
        validator,
        additionalValidators,
        getTotalsAction,
        defaultTotal,
        cartCache,
        customerData,
        _url,
        calendar
    ) {
        'use strict';
        return Component.extend({

            redirectAfterPlaceOrder: false,

            defaults: {
                template: 'Milabs_Provu/payment/provu',
                conditionSelected: ko.observable()
            },
            initialize: function () {
                this._super();

                var self = this;
               
                //Set condition object
                this.conditionSelected.subscribe(function (value) {
                    self.selectionChanged(value)
                });

            },
            isEnable: function () {
                return window.checkoutConfig.payment.provu.isActive;

            },getLogoIconProvu: function(){
                return window.checkoutConfig.payment.provu.logoIconProvu;
            },
            getTitlePayment: function () {
                return window.checkoutConfig.payment.provu.title;
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.provu.instructions;
            },
            
            getInfoProvu: function () {
                return window.checkoutConfig.payment.provu.infoProvu;
            },

            afterPlaceOrder: function () {

                $.ajax({
                    url: _url.build('provu/ajax/redirect'),
                }).done(function(result) {
                    if(result === 'false'){
                        window.location.replace(_url.build(window.checkoutConfig.defaultSuccessPageUrl));
                    }
                });
                window.location.replace(_url.build('provu/ajax/redirect'));
            },

            getConditionsValues: function () {
                return _.map(window.checkoutConfig.payment.provu.installments, function (value, key) {
                    return {
                        'value': key,
                        'text': value
                    };
                });
            },
            
        });
    }
);

