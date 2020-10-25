

$(document).ready(function() {

    let validatorUserData = $('#formUserData').validate({
        rules: {
            name: 'required',
            surname: 'required',
            email: {
                required: true,
                email: true,
                remote: '/Account/validateEditedEmail'
            }
        },
        messages: {
            name: 'Wprowadż imię!',
            surname: 'Wprowadź nazwisko!',
            email: {
                remote: 'Email jest zajęty.',
                required: 'Wprowadż email!',
                email: 'Wprowadż poprawny adres email.'
            }
        }
    });


    $('#edit-user-data-modal').on('show.bs.modal', function() {
        validatorUserData.resetForm();
    });

    $('#edit-user-data-modal').on('hide.bs.modal', function() {
        validatorUserData.resetForm();
    });
});
