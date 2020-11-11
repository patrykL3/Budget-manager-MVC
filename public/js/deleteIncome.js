$(document).ready(function() {

    let incomeId;


    $(document).on('click', '.deleteIncome', function() {
        let linkId = $(this).attr("id");
        incomeId = getIncomeId(linkId);
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

    function getIncomeId(linkId) {
        let incomeId = linkId.replace("deleteIncomeId", "");
        return incomeId;
    }
});
