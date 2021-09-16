define([
    'Magento_Ui/js/grid/listing'
], function (Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'Pharmao_delivery/ui/grid/listing'
        },
        getRowClass: function (col,row) {
            if(col.index == 'status'){
                if(row.status == 'new') {
                    return 'new';
                } else if(row.status == 'closed') {
                    return 'closed';
                } else if(row.status == 'processing') {
                    return 'processing';
                } else {
                    return 'pending';
                }
            }
        }
    });
});