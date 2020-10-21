$(document).ready(function() {

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
