$(document).ready(function() {

    let income_id;


    $(document).on('click', '.editIncome', function() {

        income_id = $(this).attr("id");

        $.ajax({
            type: "POST",
            data: {
                income_id: income_id
            },
            url: "/Balance/getDataToEditIncome",
            success: function(data) {
                let dataEditIncome = jQuery.parseJSON(data);
                let comment = dataEditIncome.income_comment;
                let amount = dataEditIncome.amount;
                let date = dataEditIncome.date_of_income;
                let category = dataEditIncome.category_type;

                $('#editDateIncome').val(date);
                $('#editAmountIncome').val(amount);
                $('#editComment').val(comment);

                $(".incomeCategoryOption").each(function() {
                    if ($(this).val() == category) {
                        $(this).attr('selected', 'selected');
                    }
                });
            }
        });
    });


    $("#formIncome").on("submit", function(e) {

        let amount = $('#editAmountIncome').val();
        let date = $('#editDateIncome').val();
        let category = $('#incomeCategorySelector').val();
        let comment = $('#editComment').val();

        let error = $('.form-control.error');

        if (!error.length) {
            $.ajax({
                type: "POST",
                url: "/Balance/updateIncome",
                data: {
                    "amount": amount,
                    "date": date,
                    "category": category,
                    "comment": comment,
                    "income_id": income_id
                },
                success: function(data) {
                    $("#edit-income-modal").modal('hide');
                    $(getIncomeSpanId("#categoryIncomeId", income_id)).text(category);
                    $(getIncomeSpanId("#amountIncomeId", income_id)).text(amount);
                    $(getIncomeSpanId("#dateIncomeId", income_id)).text(date);

                    let commentSpanId = getIncomeSpanId("#commentIncomeId", income_id);
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

    function getIncomeSpanId(baseSpan, incomeId) {
        let spanId = baseSpan.concat(incomeId);

        return spanId;
    }

    function setCommentSpan(comment) {
        let baseComment = "Komentarz: ";
        let fullComment = baseComment.concat(comment);

        return fullComment;
    }

});
