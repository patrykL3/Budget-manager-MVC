$(document).ready(function() {

    let expenseCategoryId;
    let rowWithExpenseCategoryToEdit;

    $(document).on('click', '.editExpenseCategory', function() {
        let linkId = $(this).attr("id");
        expenseCategoryId = getExpenseCategoryId(linkId);
        rowWithExpenseCategoryToEdit = (($(this).parent()).parent()).parent();

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

    $("#formExpenseCategory").on("submit", function(e) {
        let newCategoryType = $('#expenseCategoryType').val();
        let newKillerFeature = $("#killerCheckbox").is(':checked');
        let newKillerFeatureValue = $("#categoryLimit").val();


        let error = $('.form-control.error');

        if (!error.length) {
            $.ajax({
                type: "POST",
                url: "/Settings/updateExpenseCategory",
                data: {
                    expenseCategoryId: expenseCategoryId,
                    newCategoryType: newCategoryType,
                    newKillerFeature: newKillerFeature,
                    newKillerFeatureValue: newKillerFeatureValue
                },
                success: function(data) {
                    if (data) {
                        let newExpenseCategoryId = data;
                        rowWithExpenseCategoryToEdit.remove();
                        createNewDivWithExpenseCategory(newExpenseCategoryId, newCategoryType);
                    }
                    $("#edit-expence-category-modal").modal('hide');
                }
            });
            e.preventDefault();
        }
    });

    function createNewDivWithExpenseCategory(newExpenseCategoryId, newCategoryType) {
        let newIdInputWithCategory = createNewId("valueExpenseCategoryId", newExpenseCategoryId);
        let newEditCategoryLinkId = createNewId("editExpenseCategoryId", newExpenseCategoryId);
        let newDeleteCategoryLinkId = createNewId("deleteExpenseCategoryId", newExpenseCategoryId);
        newCategoryType = newCategoryType.toLowerCase();
        newCategoryType = newCategoryType.substr(0, 1).toUpperCase() + newCategoryType.substr(1);

        newCategoryDiv = ($(".onceOfExpenseCategoryRow").last()).clone();
        newCategoryDiv.insertAfter($(".onceOfExpenseCategoryRow").last());
        newCategoryDiv.find('input').val(newCategoryType);
        newCategoryDiv.find('input').prop('id', newIdInputWithCategory);
        newCategoryDiv.find('.editExpenseCategory').prop('id', newEditCategoryLinkId);
        newCategoryDiv.find('.deleteUserExpenseCategory').prop('id', newDeleteCategoryLinkId);
    }

    function createNewId(baseSpan, newId) {
        let fullId = baseSpan.concat(newId);

        return fullId;
    }

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

    function getExpenseCategoryId(linkId) {
        let expenseCategoryId = linkId.replace("editExpenseCategoryId", "");
        return expenseCategoryId;
    }

});
