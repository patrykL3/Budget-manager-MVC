$(document).ready(function() {

    let expenseId;


    $(document).on('click', '.deleteExpense', function() {
        expenseId = $(this).attr("id");
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
});
