$(document).ready(function() {

    let regularExpresionChar = new RegExp(/[\d\.\,]/, 'i');
    let regularExpresionEntireAmount = new RegExp(/\....|,...|,,|\.\.|,\.|\.,/, 'i');

    $('.amount').bind("keypress", function(event) {
        let backspaceASCII = 8;

        if (event.which != backspaceASCII) {

            let currentChar = String.fromCharCode(event.which);
            let fullValue = $(this).val() + currentChar;

            if (!regularExpresionChar.test(currentChar) || regularExpresionEntireAmount.test(fullValue) && event.target.selectionStart + 3 >=  fullValue.length ) {
                event.preventDefault();
            }
        }
    });
});
