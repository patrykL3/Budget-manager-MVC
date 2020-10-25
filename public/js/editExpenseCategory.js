$(document).ready(function() {

    let expenseCategoryId;

    $(".editExpenseCategory").click(function() {

        expenseCategoryId = $(this).attr("id");

        $.ajax({
            type: "POST",
            data: {
                expenseCategoryId: expenseCategoryId
            },
            url: "/Settings/getDataToEditExpenseCategory",
            success: function(data) {
                let dataEditExpenseCategory = jQuery.parseJSON(data);
                let expenseCategory = dataEditExpenseCategory.category_type;
                let killerFeature = dataEditExpenseCategory.killer_feature;
                let killerFeatureValue = dataEditExpenseCategory.killer_feature_value;

                if (killerFeature != 0) {
                    $("#killerCheckbox").prop("checked", true);
                    setComponentsAttributesWhenKillerCheckboxChecked();
                    $('#categoryLimit').val(killerFeatureValue);
                } else {
                    setComponentsAttributesWithoutKiller();
                    $('#categoryLimit').val("");
                }
                $('#expenseCategoryType').val(expenseCategory);
            }
        });
    });


    $("#killerCheckbox").change(function() {
        if (this.checked) {
            setComponentsAttributesWhenKillerCheckboxChecked();
        } else {
            setComponentsAttributesWithoutKiller();
        }
    });

    function setComponentsAttributesWhenKillerCheckboxChecked() {
        $("#iconNoProtected").attr('hidden', 'hidden');
        $("#iconProtected").removeAttr('hidden');
        $("#categoryLimit").removeAttr('disabled');
        $("#categoryLimit").focus();
    }

    function setComponentsAttributesWithoutKiller() {
        $("#iconProtected").attr('hidden', 'hidden');
        $("#iconNoProtected").removeAttr('hidden');
        $("#categoryLimit").attr('disabled', 'disabled');
        $("#killerCheckbox").prop("checked", false);
    }

});
