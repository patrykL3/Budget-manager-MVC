$(document).ready(function() {

    $("#user-data").click(function() {

        $.ajax({
            type: "GET",
            datatype: "json",
            url: "/Settings/getDataToEditUserData",
            success: function(data) {
                let userData = jQuery.parseJSON(data);
                let name = userData.name;
                let surname = userData.surname;
                let email = userData.email;

                $('#userName').val(name);
                $('#userSurname').val(surname);
                $('#userEmail').val(email);
            }
        });
    });

    $("#formUserData").on("submit", function(e) {

        let name = $('#userName').val();
        let surname = $('#userSurname').val();
        let email = $('#userEmail').val();

        let error = $('.form-control.error');

        if (!error.length) {
            $.ajax({
                type: "POST",
                url: "/Settings/updateUserData",
                data: {
                    "name": name,
                    "surname": surname,
                    "email": email,
                },
                success: function(data) {
                    $("#edit-user-data-modal").modal('hide');
                }
            });
            e.preventDefault();
        }
    });

});
