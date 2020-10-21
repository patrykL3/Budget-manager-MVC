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

    let validatorIncome = $('#formIncome').validate({
        rules: {
            amount: 'required',
            date: 'required',
            category: {
                required: true,
                validCategory: true
            }
        },
        messages: {
            amount: 'Wprowadż kwotę!',
            date: 'Wprowadź datę!',
            category: {
                required: 'Wprowadż kategorię!',
                validCategory: 'Wprowadż kategorię!'
            }
        }
    });

    $('#edit-income-modal').on('show.bs.modal', function() {
        validatorIncome.resetForm();
    });

    $('#edit-income-modal').on('hide.bs.modal', function() {
        validatorIncome.resetForm();
    });

});
