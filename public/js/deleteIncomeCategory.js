$(document).ready(function() {

    let incomeCategoryId;
    let rowWithIncomeCategoryToDelete;
    let userIncomeCategories;


    $(document).on('click', '.deleteUserIncomeCategory', function() {
        let linkId = $(this).attr("id");
        incomeCategoryId = getIncomeCategoryId(linkId);
        rowWithIncomeCategoryToDelete = (($(this).parent()).parent()).parent();

        $.ajax({
            type: "POST",
            data: {
                incomeCategoryId: incomeCategoryId
            },
            url: "/Settings/tryDeleteUserIncomeCategory",
            success: function(data) {
                (data != 'false') ? openDeleteUsedIncomeCategoryModal(incomeCategoryId): rowWithIncomeCategoryToDelete.remove();
            }
        });
    });

    $(document).on('click', '#formDeleteUsedIncomeCategory', function() {
        if ($("#transferIncomes").is(':checked')) {
            $("#incomeCategorySelector").removeAttr('disabled');
        }
    });

    $(document).on('click', '#formDeleteUsedIncomeCategory', function() {
        if ($("#deleteIncomes").is(':checked')) {
            $("#incomeCategorySelector").attr('disabled', 'disabled');
        }
    });


    $("#formDeleteUsedIncomeCategory").on("submit", function(e) {
        let pathToAction = "";
        let categoryToCarryOverIncomes = "";

        if ($("#deleteIncomes").is(':checked')) {
            pathToAction = "/Settings/deleteUserIncomeCategoryWithIncomes";
        } else if ($("#transferIncomes").is(':checked')) {
            pathToAction = "/Settings/deleteUserIncomeCategoryWithMoveIncomesToAnotherCategory";
            categoryToCarryOverIncomes = $('#incomeCategorySelector').val();
        }

        $.ajax({
            type: "POST",
            url: pathToAction,
            data: {
                incomeCategoryId: incomeCategoryId,
                categoryToCarryOverIncomes: categoryToCarryOverIncomes
            },
            success: function(data) {
                rowWithIncomeCategoryToDelete.attr('hidden', 'hidden');
                $("#delete-used-income-category").modal('hide');
            }
        });
        e.preventDefault();

    });

    function getIncomeCategoryId(linkId) {
        let incomeCategoryId = linkId.replace("deleteIncomeCategoryId", "");
        return incomeCategoryId;
    }

    function getValueIncomeCategory(incomeCategoryId) {
        let basePartOfIdWithValue = "#valueIncomeCategoryId";
        let idWithValue = basePartOfIdWithValue.concat(incomeCategoryId);
        let valueIncomeCategory = $(idWithValue).val();
        return valueIncomeCategory;
    }

    function openDeleteUsedIncomeCategoryModal(incomeCategoryId) {
        let valueChosenIncomeCategoryToDelete = getValueIncomeCategory(incomeCategoryId);

        $('#incomeCategorySelector').empty();
        getIncomeCategoriesWithoutRemoveCategoryToModal(valueChosenIncomeCategoryToDelete);

        $("#incomeCategoryNameToDelete").html(valueChosenIncomeCategoryToDelete);
        $("#deleteIncomes").prop("checked", true);
        $("#incomeCategorySelector").attr('disabled', 'disabled');
        $("#delete-used-income-category").modal('show');
    }

    function getIncomeCategoriesWithoutRemoveCategoryToModal(valueChosenIncomeCategoryToDelete) {

        $.ajax({
            url: "/Settings/getUserIncomeCategories",
            success: function(data) {
                userIncomeCategories = jQuery.parseJSON(data);
                $.each(userIncomeCategories, function(i, incomeCategory) {
                    if (valueChosenIncomeCategoryToDelete != incomeCategory.category_type)
                        putIncomeCategoryToSelector(incomeCategory.category_type);
                });
            }
        });
    }

    function putIncomeCategoryToSelector(incomeCategory) {
        $('#incomeCategorySelector').append($('<option>', {
            value: incomeCategory,
            text: incomeCategory
        }));
    }

});
