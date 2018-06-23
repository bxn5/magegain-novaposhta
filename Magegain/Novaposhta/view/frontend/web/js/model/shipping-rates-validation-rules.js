
define(
    [],
    function () {
        "use strict";
        return {
            getRules: function () {
                return {
                    'postcode': {
                        'required': false
                    },
                    'city': {
                        'required': true
                    }
                };
            }
        };
    }
);
