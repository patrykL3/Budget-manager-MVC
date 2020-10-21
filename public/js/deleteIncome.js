$(document).ready(function() {

    let incomeId;


    $(document).on('click', '.deleteIncome', function() {
        incomeId = $(this).attr("id");
        let clickedShape = $(this);

        let pathWithData = "/Balance/deleteIncome";
        console.log(incomeId);

        $.ajax({
            type: "POST",
            data: {
                incomeId: incomeId
            },
            url: pathWithData,
            success: function(data) {
                (clickedShape.parent()).parent().attr('hidden', 'hidden');
            }
        });
    });
});
