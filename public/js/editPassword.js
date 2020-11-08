$(document).ready(function() {

    $("#formUserPassword").on("submit", function(e) {

        let oldPassword = $('#oldPassword').val();
        let newPassword = $('#newPassword').val();
        let newPasswordConfirmation = $('#newPasswordConfirmation').val();

        let error = $('.form-control.error');

        if (!error.length) {
            $.ajax({
                type: "POST",
                url: "/Settings/updateUserPassword",
                data: {
                    "oldPassword": oldPassword,
                    "newPassword": newPassword,
                    "newPasswordConfirmation": newPasswordConfirmation,
                },
                success: function(data) {

                    errorCurrentPassword = jQuery.parseJSON(data);


                    if (errorCurrentPassword != 0) {
                        console.log("błąd");
                        $("#incorrectPasswordData").removeAttr('hidden');

                    } else {
                        $("#edit-user-password").modal('hide');
                    }

                }
            });
            e.preventDefault();
        }
    });

});
