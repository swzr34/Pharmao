/*global define*/
define([
    'Magento_Ui/js/form/form'
], function(Component) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();
            // component initialization logic
            return this;
        },
        
        permissionChanged : function (obj, event) {

            if (event.originalEvent) { //user changed
                var val = jQuery('#selectAddress').val();
                if(val == "new") {
                        jQuery('#co-shipping-form').find("input[type=text]").val("");
                        jQuery('div[name="shippingAddress.country_id"], div[name="shippingAddress.custom_attributes.address_finder"], div[name="shippingAddress.firstname"], div[name="shippingAddress.lastname"], div[name="shippingAddress.telephone"], div[name="shippingAddress.company"]').css('display', 'block');
                } else {
                    jQuery('div[name="shippingAddress.custom_attributes.address_finder"]').find("button").click();
                    jQuery('div[name="shippingAddress.country_id"], div[name="shippingAddress.custom_attributes.address_finder"]').css('display', 'none');
                    jQuery('div[name="shippingAddress.firstname"], div[name="shippingAddress.lastname"], div[name="shippingAddress.telephone"], div[name="shippingAddress.company"]').css('display', 'block');
                    var splitedVal = val.split(',');
                    jQuery('input[name="street[0]"]').val(splitedVal[0]);
                    jQuery('input[name="street[1]"]').val(splitedVal[1]);
                    jQuery('input[name="street[2]"]').val(splitedVal[2]);
                    jQuery('input[name="city"]').val(splitedVal[4]);
                    jQuery('input[name="postcode"]').val(splitedVal[3]);
                    jQuery("select[name='country_id'] option:contains(" + splitedVal[5] + ")").attr('selected', true);
                }
                jQuery('input[name="street[0]"], input[name="street[1]"], input[name="street[2]"], input[name="city"], input[name="postcode"], select[name="country_id"]').trigger("change");
                jQuery('input[name="postcode"]').keyup();
                
            } else { // program changed
            }
        
        }
    });
});