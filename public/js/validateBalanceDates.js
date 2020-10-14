/**
 * Add jQuery Validation plugin method for a valid category
 *
 */


$(document).ready(function() {


        /**
         * Validate the form
         */
        $('#formDates').validate({
            rules: {
                balance_start_date: 'required',
                balance_end_date: 'required'
            },
            messages: {
                balance_start_date: 'Wprowadż datę!',
                balance_end_date: 'Wprowadż datę!'
            }
        });
    });
