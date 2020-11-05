$(document).ready(function() {

    let expenseCategoryId;
    let rowWithExpenseCategoryToDelete;


    $(document).on('click', '.deleteUserExpenseCategory', function() {
        let linkId = $(this).attr("id");
        expenseCategoryId = getExpenseCategoryId(linkId);
        let clickedShape = $(this);
        rowWithExpenseCategoryToDelete = ((clickedShape.parent()).parent()).parent();

        $.ajax({
            type: "POST",
            data: {
                expenseCategoryId: expenseCategoryId
            },
            url: "/Settings/tryDeleteUserExpenseCategory",
            success: function(data) {
                (data != 'false') ? openDeleteUsedExpenseCategoryModal(expenseCategoryId): rowWithExpenseCategoryToDelete.remove();
            }
        });
    });

    $(document).on('click', '#formDeleteUsedExpenseCategory', function() {
        if ($("#transferExpenses").is(':checked')) {
            $("#expenseCategorySelector").removeAttr('disabled');
        }
    });

    $(document).on('click', '#formDeleteUsedExpenseCategory', function() {
        if ($("#deleteExpenses").is(':checked')) {
            $("#expenseCategorySelector").attr('disabled', 'disabled');
        }
    });


    $("#formDeleteUsedExpenseCategory").on("submit", function(e) {
        let pathToAction = "";
        let categoryToCarryOverExpenses = "";

        if ($("#deleteExpenses").is(':checked')) {
            pathToAction = "/Settings/deleteUserExpenseCategoryWithExpenses";
        } else if ($("#transferExpenses").is(':checked')) {
            pathToAction = "/Settings/deleteUserExpenseCategoryWithMoveExpensesToAnotherCategory";
            categoryToCarryOverExpenses = $('#expenseCategorySelector').val();
        }

        $.ajax({
            type: "POST",
            url: pathToAction,
            data: {
                expenseCategoryId: expenseCategoryId,
                categoryToCarryOverExpenses: categoryToCarryOverExpenses
            },
            success: function(data) {
                rowWithExpenseCategoryToDelete.attr('hidden', 'hidden');
                $("#delete-used-expense-category").modal('hide');
            }
        });
        e.preventDefault();

    });




    function getExpenseCategoryId(linkId) {
        let expenseCategoryId = linkId.replace("deleteExpenseCategoryId", "");
        return expenseCategoryId;
    }

    function getValueExpenseCategory(expenseCategoryId) {
        let basePartOfIdWithValue = "#valueExpenseCategoryId";
        let idWithValue = basePartOfIdWithValue.concat(expenseCategoryId);
        let valueExpenseCategory = $(idWithValue).val();
        return valueExpenseCategory;
    }

    function openDeleteUsedExpenseCategoryModal(expenseCategoryId) {
        let valueChosenExpenseCategoryToDelete = getValueExpenseCategory(expenseCategoryId);
        $("#expenseCategoryNameToDelete").html(valueChosenExpenseCategoryToDelete);
        $('.expenseCategoryOption').removeAttr('selected');
        $('.expenseCategoryOption').each(function() {
            if ($(this).val() == valueChosenExpenseCategoryToDelete) {
                $(this).hide();
            } else {
                $(this).show();
                $(this).attr('selected', 'selected');
            }
        });
        $("#deleteExpenses").prop("checked", true);
        $("#expenseCategorySelector").attr('disabled', 'disabled');
        $("#delete-used-expense-category").modal('show');
    }


});
