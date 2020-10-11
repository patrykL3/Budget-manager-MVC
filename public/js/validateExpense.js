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
        $('#formExpense').validate({
            rules: {
                amount: 'required',
                date: 'required',
                expense_category: {
                    required: true,
                    validCategory: true
                },
                payment_category: {
                    required: true,
                    validCategory: true
                }
            },
            messages: {
                amount: 'Wprowadż kwotę!',
                date: 'Wprowadź datę!',
                expense_category: {
                    required: 'Wprowadż kategorię!',
                    validCategory: 'Wprowadź kategorię wydatku!'
                },
                payment_category: {
                    required: 'Wprowadż kategorię!',
                    validCategory: 'Wprowadź metodę płatności!'
                }
            }
        });
    });
