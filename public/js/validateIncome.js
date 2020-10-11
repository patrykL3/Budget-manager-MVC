/**
 * Add jQuery Validation plugin method for a valid category
 *
 */
$.validator.addMethod('validCategory',
    function(value, element, param) {

        if (value === 'Wybierz kategorię') {
            return false;
        } else {
            return true;
        }
    },
);

$(document).ready(function() {


        /**
         * Validate the form
         */
        $('#formIncome').validate({
            rules: {
                amount: 'required',
                date: 'required',
                category: {
                    required: true,
                    validCategory: true
                }
            },
            messages: {
                login: {
                    remote: 'Login jest zajęty.',
                    required: 'Wprowadż login!',
                },
                amount: 'Wprowadż kwotę!',
                date: 'Wprowadź datę!',
                category: {
                    required: 'Wprowadż kategorię!',
                    validCategory: 'Wprowadż kategorię!'
                }
            }
        });
    });
