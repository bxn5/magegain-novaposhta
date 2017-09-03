define([
    'Magento_Ui/js/form/element/abstract',
    'mage/url',
    'ko',
    'jquery',
    'jquery/ui'
], function (Abstract, url, ko, $) {
    'use strict';

    ko.bindingHandlers.shippingAutoComplete = {

        init: function (element, valueAccessor) {
             Promise.resolve(
                 $.ajax({
                        type: 'POST',
                        url: '/novaposhta/ajax/cities',
                        dataType: 'json'
                    })
             ).then(function (result_list) {
                var settings = valueAccessor();

                var selectedOption = settings.selected;
                var options =  JSON.parse(result_list);
                var updateElementValueWithLabel = function (event, ui) {
                    // Stop the default behavior
                    event.preventDefault();
                    $(element).val(ui.item.label);

                    if (typeof ui.item !== "undefined") {
                        // ui.item - id|label|...
                        selectedOption(ui.item);
                        //selectedValue(ui.item.value);
                    }
                };

                    $(element).autocomplete({
                        source: options,
                        select: function (event, ui) {
                            updateElementValueWithLabel(event, ui);
                        }
                    });

             });
        }
    };

    return Abstract.extend({

        selectedDepartment: ko.observable(''),
        selectedCity: ko.observable(''),
        postCode: ko.observable(''),
        getCities: function ( request, response ) {
            var departmentValue = $('[name="region"]').val();
            $.ajax({
                url: url.build('novaposhta/ajax/cities/'),
                data: JSON.stringify({
                    q: request.term,
                    filter: departmentValue
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