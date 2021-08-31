define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../../model/shipping-rates-validator/pharmaodropoffs',
    '../../model/shipping-rates-validation-rules/pharmaodropoffs'
], function (Component,
             defaultShippingRatesValidator,
             defaultShippingRatesValidationRules,
             customShippingRatesValidator,
             customShippingRatesValidationRules) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('pharmaodropoffs', customShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('pharmaodropoffs', customShippingRatesValidationRules);

    return Component;
});