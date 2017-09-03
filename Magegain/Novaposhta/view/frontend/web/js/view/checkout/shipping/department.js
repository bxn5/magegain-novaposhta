define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/form/element/abstract',
    'mage/url'
], function ($, ko, Component, quote, Abstract, url) {
    'use strict';

    ko.bindingHandlers.deAutoComplete = {

        init: function (element, valueAccessor) {
            var settings = valueAccessor();
            var selectedOption = settings.selected;
            var options = settings.options;
            var updateElementValueWithLabel = function (event, ui) {
                // Stop the default behavior
                event.preventDefault();

                $(element).val(ui.item.label);

                // Update our SelectedOption observable
                if (typeof ui.item !== "undefined") {
                    selectedOption(ui.item);
                }
            };

            $(element).autocomplete({
                source: options,
                select: function (event, ui) {
                    updateElementValueWithLabel(event, ui);
                }
            });

        }

    };

    return Abstract.extend({

        selectedDepartment: ko.observable(''),
        selectedMethod : ko.computed(function () {
            var method = quote.shippingMethod();
            var selectedMethod = method != null ? method.carrier_code : null;
            return selectedMethod;
        }, this),
    getDepartments: function ( request, response ) {
        var cityValue = $('[name="city"]').val();
        $.ajax({
            url: url.build('novaposhta/ajax/departments'),
            data: JSON.stringify({
                q: request.term,
                city: cityValue
                }),
        contentType: "application/json",
        type: "POST",
        dataType: 'json',
        error : function () {
            alert("An error have occurred.");
        },
            success : function (data) {
                var items = JSON.parse(data);
                response(items);
            }
            });
    }
    });
});