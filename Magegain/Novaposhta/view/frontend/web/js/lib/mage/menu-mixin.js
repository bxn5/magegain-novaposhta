define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    return function (data) {

        $.widget('mage.menu', data.menu, {
            _create: function () {
                $(this.element).data('ui-menu', this);
                this._super();
            }
        });

        data.menu = $.mage.menu;

        return data;
    };
});