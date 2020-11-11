$(document).ready(function() {


    $(".show").click(function() {
        $(".standardHidden").collapse("hide");
    });

    $(document).on('click', '.delete', function() {
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

});
