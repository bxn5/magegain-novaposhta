
define([
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    'ko',
    'jquery/ui',
], function ($, Component, ko, jqi) {
    'use strict';
    var prom = Promise.resolve(
            $.ajax({
                type: 'POST',
                url: '/novaposhta/ajax/cities',
                dataType: 'json'
            })
            );

    //jqAuto -- main binding (should contain additional options to pass to autocomplete)
//jqAutoSource -- the array of choices
//jqAutoValue -- where to write the selected value
//jqAutoSourceLabel -- the property that should be displayed in the possible choices
//jqAutoSourceInputValue -- the property that should be displayed in the input box
//jqAutoSourceValue -- the property to use for the value
    prom.then(function (result_list) {
        var data_arr = JSON.parse(result_list);
        var result = [];

        for (var i = 0; i < data_arr.length; i++) {

            result.push(new City(data_arr[i]));
        }
        var resarr = ko.observableArray(result);

        ko.bindingHandlers.jqAuto = {
            init: function (element, valueAccessor, allBindingsAccessor, viewModel) {
                var options = valueAccessor() || {},
                        allBindings = allBindingsAccessor(),
                        unwrap = ko.utils.unwrapObservable,
                        modelValue = allBindings.jqAutoValue,
                        source = resarr,
                        valueProp = allBindings.jqAutoSourceValue,
                        inputValueProp = allBindings.jqAutoSourceInputValue || valueProp,
                        labelProp = allBindings.jqAutoSourceLabel || valueProp;

                //function that is shared by both select and change event handlers
                function writeValueToModel(valueToWrite) {
                    if (ko.isWriteableObservable(modelValue)) {
                        modelValue(valueToWrite);
                    } else {  //write to non-observable
                        if (allBindings['_ko_property_writers'] && allBindings['_ko_property_writers']['jqAutoValue'])
                            allBindings['_ko_property_writers']['jqAutoValue'](valueToWrite);
                    }
                }

                //on a selection write the proper value to the model
                options.select = function (event, ui) {
                    writeValueToModel(ui.item ? ui.item.actualValue : null);
                };

                //on a change, make sure that it is a valid value or clear out the model value
                options.change = function (event, ui) {
                    var currentValue = $(element).val();
                    var matchingItem = ko.utils.arrayFirst(unwrap(source), function (item) {
                        return unwrap(inputValueProp ? item[inputValueProp] : item) === currentValue;
                    });

                    if (!matchingItem) {
                        writeValueToModel(null);
                    }
                }


                //handle the choices being updated in a DO, to decouple value updates from source (options) updates
                var mappedSource = ko.dependentObservable(function () {
                    var mapped = ko.utils.arrayMap(unwrap(source), function (item) {
                        var result = {};
                        result.label = labelProp ? unwrap(item[labelProp]) : unwrap(item).toString();  //show in pop-up choices
                        result.value = inputValueProp ? unwrap(item[inputValueProp]) : unwrap(item).toString();  //show in input box
                        result.actualValue = valueProp ? unwrap(item[valueProp]) : item;  //store in model
                        return result;
                    });
                    return mapped;
                }, null, {disposeWhenNodeIsRemoved: element});

                //whenever the items that make up the source are updated, make sure that autocomplete knows it
                mappedSource.subscribe(function (newValue) {
                    $(element).autocomplete("option", "source", newValue);
                });

                options.source = mappedSource();

                //initialize autocomplete
                $(element).autocomplete(options);
            },
            update: function (element, valueAccessor, allBindingsAccessor, viewModel) {
                //update value based on a model change
                var allBindings = allBindingsAccessor(),
                        unwrap = ko.utils.unwrapObservable,
                        modelValue = unwrap(allBindings.jqAutoValue) || '',
                        valueProp = allBindings.jqAutoSourceValue,
                        inputValueProp = allBindings.jqAutoSourceInputValue || valueProp;

                //if we are writing a different property to the input than we are writing to the model, then locate the object
                if (valueProp && inputValueProp !== valueProp) {
                    var source = unwrap(allBindings.jqAutoSource) || [];
                    var modelValue = ko.utils.arrayFirst(source, function (item) {
                        return unwrap(item[valueProp]) === modelValue;
                    }) || {};  //probably don't need the || {}, but just protect against a bad value          
                }

                //update the element with the value that should be shown in the input
                $(element).val(modelValue && inputValueProp !== valueProp ? unwrap(modelValue[inputValueProp]) : modelValue.toString());
            }
        };
    });
    function City(name) {
        this.name = ko.observable(name);


        this.displayName = ko.dependentObservable(function () {
            return this.name();
        }, this);
    }

   



    return Component.extend({
        defaults: {
            template: 'Magegain_Novaposhta/form/element/city',
        },
        myCities: function () {
            ko.observableArray(resarr);
        },
       


        mySelectedGuid: function () {
            ko.observable("ec361d63-38ae-4ecc-ab46-6c0ef19ed3ac");
        }




    });
});

