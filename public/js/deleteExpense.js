$(document).ready(function() {

    let expenseId;


    $(document).on('click', '.deleteExpense', function() {
        let linkId = $(this).attr("id");
        expenseId = getExpenseId(linkId);
        let clickedShape = $(this);

        $.ajax({
            type: "POST",
            data: {
                expenseId: expenseId
            },
            url: "/Balance/deleteExpense",
            success: function(data) {
                (clickedShape.parent()).parent().attr('hidden', 'hidden');
            }
        });
    });

    function getExpenseId(linkId) {
        let expenseId = linkId.replace("deleteExpenseId", "");
        return expenseId;
    }


});
