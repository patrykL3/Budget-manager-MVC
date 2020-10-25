$(document).ready(function() {

    $.validator.addMethod('validPassword',
        function(value, element, param) {

            if (value != '') {
                if (value.match(/.*[a-z]+.*/i) == null) {
                    return false;
                }
                if (value.match(/.*\d+.*/) == null) {
                    return false;
                }
            }

            return true;
        },
    );

    let validatorUserPassword = $('#formUserPassword').validate({
        rules: {
            oldPassword: {
                required: true
            },
            newPassword: {
                required: true,
                minlength: 6,
                validPassword: true
            },
            newPasswordConfirmation: {
                equalTo: '#newPassword'
            }
        },
        messages: {
            oldPassword: 'Wprowadź poprzednie hasło.',
            newPassword: 'Hasło musi zawierać przynajmniej 6 znaków, jedną cyfrę oraz literę.',
            newPasswordConfirmation: 'Hasło nie zostało powtórzone poprawnie.'
        }
    });


    $('#edit-user-password').on('show.bs.modal', function() {
        validatorUserPassword.resetForm();
    });

    $('#edit-user-password').on('hide.bs.modal', function() {
        validatorUserPassword.resetForm();
        $('#oldPassword').val('');
        $('#newPassword').val('');
        $('#newPasswordConfirmation').val('');
        $("#incorrectPasswordData").attr('hidden', 'hidden');
    });
});
