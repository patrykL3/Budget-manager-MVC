$(document).ready(function() {

    let newExpenseCategory;


    $(document).on('click', '#addNewExpenseCategory', function() {
        newExpenseCategory = $('#newExpenseCategory').val();
        if (newExpenseCategory != "") {
            $.ajax({
                type: "POST",
                data: {
                    newExpenseCategory: newExpenseCategory
                },
                url: "/Settings/addExpenseCategory",
                success: function(data) {
                    let newExpenseCategoryId = jQuery.parseJSON(data);
                    (newExpenseCategoryId != '') ? createNewDivWithExpenseCategory(newExpenseCategoryId): openCategoryExistInfoModal();
                }
            });
        }
    });


    function createNewId(baseSpan, newId) {
        let fullId = baseSpan.concat(newId);

        return fullId;
    }

    function createNewDivWithExpenseCategory(newExpenseCategoryId) {
        let newIdInputWithCategory = createNewId("valueExpenseCategoryId", newExpenseCategoryId);
        let newEditCategoryLinkId = createNewId("editExpenseCategoryId", newExpenseCategoryId);
        let newDeleteCategoryLinkId = createNewId("deleteExpenseCategoryId", newExpenseCategoryId);
        newExpenseCategory = newExpenseCategory.toLowerCase();
        newExpenseCategory = newExpenseCategory.substr(0, 1).toUpperCase() + newExpenseCategory.substr(1);

        newCategoryDiv = ($(".onceOfExpenseCategoryRow").last()).clone();
        newCategoryDiv.insertAfter($(".onceOfExpenseCategoryRow").last());
        newCategoryDiv.find('input').val(newExpenseCategory);
        newCategoryDiv.find('input').prop('id', newIdInputWithCategory);
        newCategoryDiv.find('.editExpenseCategory').prop('id', newEditCategoryLinkId);
        newCategoryDiv.find('.deleteUserExpenseCategory').prop('id', newDeleteCategoryLinkId);

        $('#newExpenseCategory').val("");
    }

    function openCategoryExistInfoModal() {
        $("#category-exist-info").modal('show');
    }
});
