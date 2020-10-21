$(document).ready(function() {

    let expense_id;


    $(document).on('click', '.editExpense', function() {

        expense_id = $(this).attr("id");

        $.ajax({
            type: "POST",
            data: {
                expense_id: expense_id
            },
            url: "/Balance/getDataToEditExpense",
            success: function(data) {
                let dataEditExpense = jQuery.parseJSON(data);
                let comment = dataEditExpense.expense_comment;
                let amount = dataEditExpense.amount;
                let date = dataEditExpense.date_of_expense;
                let category = dataEditExpense.category_type;
                let paymentCategory = dataEditExpense.payment_category_type;

                $('#editDateExpense').val(date);
                $('#editAmountExpense').val(amount);
                $('#editExpenseComment').val(comment);

                $(".expenseCategoryOption").each(function() {
                    if ($(this).val() == category) {
                        $(this).attr('selected', 'selected');
                    }
                });
                $(".expensePaymentCategoryOption").each(function() {
                    if ($(this).val() == paymentCategory) {
                        $(this).attr('selected', 'selected');
                    }
                });
            }
        });
    });


    $("#formExpense").on("submit", function(e) {

        let amount = $('#editAmountExpense').val();
        let date = $('#editDateExpense').val();
        let category = $('#expenseCategorySelector').val();
        let payment_category = $('#expensePaymentCategorySelector').val();
        let comment = $('#editExpenseComment').val();

        let error = $('.form-control.error');

        if (!error.length) {
            $.ajax({
                type: "POST",
                url: "/Balance/updateExpense",
                data: {
                    "amount": amount,
                    "date": date,
                    "category": category,
                    "payment_category": payment_category,
                    "comment": comment,
                    "expense_id": expense_id
                },
                success: function(data) {
                    $("#edit-expense-modal").modal('hide');
                    $(getExpenseSpanId("#categoryExpenseId", expense_id)).text(category);
                    $(getExpenseSpanId("#amountExpenseId", expense_id)).text(amount);
                    $(getExpenseSpanId("#dateExpenseId", expense_id)).text(date);
                    $(getExpenseSpanId("#paymentCategoryExpenseId", expense_id)).text(setPaymentMethodSpan(payment_category));

                    let commentSpanId = getExpenseSpanId("#commentExpenseId", expense_id);
                    if (comment != "") {
                        $(commentSpanId).text(setCommentSpan(comment));
                    } else {
                        $(commentSpanId).text("");
                    }
                }
            });
            e.preventDefault();
        }
    });


    function getExpenseSpanId(baseSpan, expenseId) {
        let spanId = baseSpan.concat(expenseId);

        return spanId;
    }

    function setCommentSpan(comment) {
        let baseComment = "Komentarz: ";
        let fullComment = baseComment.concat(comment);

        return fullComment;
    }

    function setPaymentMethodSpan(paymentCategory) {
        let basePaymentMethodSpan = "Płatność: ";
        let fullPaymentMethodSpan = basePaymentMethodSpan.concat(paymentCategory);

        return fullPaymentMethodSpan;
    }

});
