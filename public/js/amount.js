$(document).ready(function() {

    let regularExpresionChar = new RegExp(/[\d\.\,]/, 'i');
    let regularExpresionEntireAmount = new RegExp(/\....$|,...$|,,$|\.\.$|,\.$|\.,$/, 'i');

    $('#amount').bind("keypress", function(event) {

        if (event.which != 8) {

            let currentChar = String.fromCharCode(event.which);
            let fullValue = $("#amount").val() + currentChar;

            if (!regularExpresionChar.test(currentChar) || regularExpresionEntireAmount.test(fullValue)) {
                event.preventDefault();
            }
        }
    });
});
