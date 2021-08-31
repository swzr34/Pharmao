require(['jquery', 'domReady!'], function($) {
    //'use strict';

    jQuery(document).ready(function(){
        alert('ready');
        jQuery('#addresses').on('change', function() {
            alert('changed');
        });
    });
});
