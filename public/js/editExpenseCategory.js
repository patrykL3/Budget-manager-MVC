$(document).ready(function() {

    let categoryId;

    //setKillerCheckbox();

    $("#killerCheckbox").change(function() {
        if (this.checked) {
            $("#iconNoProtected").attr('hidden', 'hidden');
            $("#iconProtected").removeAttr('hidden');
            $("#categoryLimit").removeAttr('disabled');
            $("#categoryLimit").focus();
        } else {
            $("#iconProtected").attr('hidden', 'hidden');
            $("#iconNoProtected").removeAttr('hidden');
            $("#categoryLimit").attr('disabled', 'disabled');
        }
    });


});
