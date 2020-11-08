$(document).ready(function() {



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




    $(document).on('click', '.editWithoutModal', function() {
        $(".basicIcons").removeAttr('hidden');
        $(".editingCategoryIcons").attr('hidden', 'hidden');
        $(".standardDisabled").attr('disabled', 'disabled');
        ($(this).parent()).attr('hidden', 'hidden');
    });




$(document).on('focusout', '.standardDisabled', function() {
    $(".editingCategoryIcons").attr('hidden', 'hidden');
    $(".standardDisabled").attr('disabled', 'disabled');
    $(".basicIcons").removeAttr('hidden');
});
/*
    $(document).on('click', '.focusOutCategory', function() {
        $(".editingCategoryIcons").attr('hidden', 'hidden');
        $(".standardDisabled").attr('disabled', 'disabled');
        $(".basicIcons").removeAttr('hidden');
    });
*/
});
