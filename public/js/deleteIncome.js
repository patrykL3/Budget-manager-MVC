$(document).ready(function() {

    let incomeId;


    $(document).on('click', '.deleteIncome', function() {
        incomeId = $(this).attr("id");
        let clickedShape = $(this);

        $.ajax({
            type: "POST",
            data: {
                incomeId: incomeId
            },
            url: "/Balance/deleteIncome",
            success: function(data) {
                (clickedShape.parent()).parent().attr('hidden', 'hidden');
            }
        });
    });
});
