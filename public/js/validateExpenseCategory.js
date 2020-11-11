$.validator.addMethod('validCategoryLimit',
    function(value, element, param) {
        if (value === '') {
            return false;
        } else {
            return true;
        }
    },
);


$(document).ready(function() {

    let expenseCategoryId;

    $(document).on('click', '.editExpenseCategory', function() {
        let linkId = $(this).attr("id");
        expenseCategoryI = getExpenseCategoryId(linkId);
    });


    let validatorExpenseCategory = $('#formExpenseCategory').validate({
        rules: {
            amount: {
                validCategoryLimit: true
            },
            expenseCategoryType: {
                required: true
            }
        },
        messages: {
            amount: 'Wprowadż kwotę lub zrezygnuj z limitu',
            expenseCategoryType: {
                required: 'Wprowadż nazwę kategorii'
            }
        }
    });

    $('#edit-expence-category-modal').on('show.bs.modal', function() {
        validatorExpenseCategory.resetForm();
    });

    $('#edit-expence-category-modal').on('hide.bs.modal', function() {
        validatorExpenseCategory.resetForm();
    });


    $("#killerCheckbox").change(function() {
        validatorExpenseCategory.resetForm();
    });



    function getExpenseCategoryId(linkId) {
        let expenseCategoryId = linkId.replace("editExpenseCategoryId", "");
        return expenseCategoryId;
    }
});
