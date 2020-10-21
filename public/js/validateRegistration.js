
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

$(document).ready(function() {

    $('#formRegistration').validate({
        rules: {
            login: {
                required: true,
                remote: '/account/validateLogin'
            },
            name: 'required',
            surname: 'required',
            email: {
                required: true,
                email: true,
                remote: '/Account/validateEmail'
            },
            password: {
                required: true,
                minlength: 6,
                validPassword: true
            },
            password_confirmation: {
                equalTo: '#inputPassword'
            }
        },
        messages: {
            login: {
                remote: 'Login jest zajęty.',
                required: 'Wprowadż login!',
            },
            name: 'Wprowadż imię!',
            surname: 'Wprowadź nazwisko!',
            email: {
                remote: 'Email jest zajęty.',
                required: 'Wprowadż email!',
                email: 'Wprowadż poprawny adres email.'
            },
            password: 'Hasło musi zawierać przynajmniej 6 znaków, jedną cyfrę oraz literę.',
            password_confirmation: 'Hasło nie zostało powtórzone poprawnie.'
        }
    });
});
