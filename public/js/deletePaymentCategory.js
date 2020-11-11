$(document).ready(function() {

    let paymentCategoryId;
    let rowWithPaymentCategoryToDelete;


    $(document).on('click', '.deleteUserPaymentCategory', function() {
        let linkId = $(this).attr("id");
        paymentCategoryId = getPaymentCategoryId(linkId);
        let clickedShape = $(this);
        rowWithPaymentCategoryToDelete = ((clickedShape.parent()).parent()).parent();

        $.ajax({
            type: "POST",
            data: {
                paymentCategoryId: paymentCategoryId
            },
            url: "/Settings/tryDeleteUserPaymentCategory",
            success: function(data) {
                (data != 'false') ? openDeleteUsedPaymentCategoryModal(paymentCategoryId): rowWithPaymentCategoryToDelete.remove();
            }
        });
    });

    $(document).on('click', '#formDeleteUsedPaymentCategory', function() {
        if ($("#transferPayments").is(':checked')) {
            $("#paymentCategorySelector").removeAttr('disabled');
        }
    });

    $(document).on('click', '#formDeleteUsedPaymentCategory', function() {
        if ($("#deleteExpensesWithPayments").is(':checked')) {
            $("#paymentCategorySelector").attr('disabled', 'disabled');
        }
    });


    $("#formDeleteUsedPaymentCategory").on("submit", function(e) {
        let pathToAction = "";
        let categoryToCarryOverPayments = "";

        if ($("#deleteExpensesWithPayments").is(':checked')) {
            pathToAction = "/Settings/deleteUserPaymentCategoryWithExpenses";
        } else if ($("#transferPayments").is(':checked')) {
            pathToAction = "/Settings/deleteUserPaymentCategoryWithMovePaymentsToAnotherCategory";
            categoryToCarryOverPayments = $('#paymentCategorySelector').val();
        }

        $.ajax({
            type: "POST",
            url: pathToAction,
            data: {
                paymentCategoryId: paymentCategoryId,
                categoryToCarryOverPayments: categoryToCarryOverPayments
            },
            success: function(data) {
                rowWithPaymentCategoryToDelete.remove();
                $("#delete-used-payment-category").modal('hide');
            }
        });
        e.preventDefault();

    });


    function getPaymentCategoryId(linkId) {
        let paymentCategoryId = linkId.replace("deletePaymentCategoryId", "");
        return paymentCategoryId;
    }

    function getValuePaymentCategory(paymentCategoryId) {
        let basePartOfIdWithValue = "#valuePaymentCategoryId";
        let idWithValue = basePartOfIdWithValue.concat(paymentCategoryId);
        let valuePaymentCategory = $(idWithValue).val();
        return valuePaymentCategory;
    }

    function openDeleteUsedPaymentCategoryModal(paymentCategoryId) {
        let valueChosenPaymentCategoryToDelete = getValuePaymentCategory(paymentCategoryId);

        $('#paymentCategorySelector').empty();
        getPaymentCategoriesWithoutRemoveCategoryToModal(valueChosenPaymentCategoryToDelete);

        $("#paymentCategoryNameToDelete").html(valueChosenPaymentCategoryToDelete);
        $('.paymentCategoryOption').removeAttr('selected');
        $("#deleteExpensesWithPayments").prop("checked", true);
        $("#paymentCategorySelector").attr('disabled', 'disabled');
        $("#delete-used-payment-category").modal('show');
    }

    function getPaymentCategoriesWithoutRemoveCategoryToModal(valueChosenPaymentCategoryToDelete) {

        $.ajax({
            url: "/Settings/getUserPaymentCategories",
            success: function(data) {
                userPaymentCategories = jQuery.parseJSON(data);
                $.each(userPaymentCategories, function(i, paymentCategory) {
                    if (valueChosenPaymentCategoryToDelete != paymentCategory.payment_category_type)
                        putPaymentCategoryToSelector(paymentCategory.payment_category_type);
                });
            }
        });
    }

    function putPaymentCategoryToSelector(paymentCategory) {
        $('#paymentCategorySelector').append($('<option>', {
            value: paymentCategory,
            text: paymentCategory
        }));
    }


});
