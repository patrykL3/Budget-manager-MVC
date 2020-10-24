$(document).ready(function() {

    $('#edit-categories-of-expenses').click(function() {
        if ($('#categories-of-expenses').attr('hidden') === 'hidden')
            $('#categories-of-expenses').removeAttr('hidden');
        else
            $('#categories-of-expenses').attr('hidden', 'hidden');
    });

    $('#edit-categories-of-incomes').click(function() {
        if ($('#categories-of-incomes').attr('hidden') === 'hidden')
            $('#categories-of-incomes').removeAttr('hidden');
        else
            $('#categories-of-incomes').attr('hidden', 'hidden');
    });

    $('#edit-categories-of-payment').click(function() {
        if ($('#categories-of-payment').attr('hidden') === 'hidden')
            $('#categories-of-payment').removeAttr('hidden');
        else
            $('#categories-of-payment').attr('hidden', 'hidden');
    });

    $(".show").click(function() {
        let clickedShapeId = $(this).attr('id');
        idToShow = clickedShapeId.replace('edit-', '');
        $(".standardHidden").attr('hidden', 'hidden');
        //$(".standardHidden").hide('slow');
        $(("#").concat(idToShow)).removeAttr('hidden');
        //$(("#").concat(idToShow)).show('slow');
    });



    $(".delete").click(function() {
        let clickedShape = $(this);
        (clickedShape.parent()).parent().attr('hidden', 'hidden');
    });

    $(".editWithoutModal").click(function() {
        let clickedShape = $(this);
        (clickedShape.parent()).attr('hidden', 'hidden');
        $(".editingIcons").removeAttr('hidden');
        //getCategoryId();
        $("#incomeCategory10").removeAttr('disabled');
        $("#incomeCategory10").focus();
    });

    $(".cancelEdit").click(function() {
        $(".editingIcons").attr('hidden', 'hidden');
        $(".standardDisabled").attr('disabled', 'disabled');
        $(".basicIcons").removeAttr('hidden');
    });

});
