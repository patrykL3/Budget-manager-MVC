$(document).ready(function() {

    let newPaymentCategory;


    $(document).on('click', '#addNewPaymentCategory', function() {
        newPaymentCategory = $('#newPaymentCategory').val();
        if (newPaymentCategory != "") {
            $.ajax({
                type: "POST",
                data: {
                    newPaymentCategory: newPaymentCategory
                },
                url: "/Settings/addPaymentCategory",
                success: function(data) {
                    let newPaymentCategoryId = jQuery.parseJSON(data);
                    (newPaymentCategoryId != '') ? createNewDivWithPaymentCategory(newPaymentCategoryId): openCategoryExistInfoModal();
                }
            });
        }
    });


    function createNewId(baseSpan, newId) {
        let fullId = baseSpan.concat(newId);

        return fullId;
    }

    function createNewDivWithPaymentCategory(newPaymentCategoryId) {
        let newIdInputWithCategory = createNewId("valuePaymentCategoryId", newPaymentCategoryId);
        let newEditCategoryLinkId = createNewId("editPaymentCategoryId", newPaymentCategoryId);
        let newDeleteCategoryLinkId = createNewId("deletePaymentCategoryId", newPaymentCategoryId);
        newPaymentCategory = newPaymentCategory.toLowerCase();
        newPaymentCategory = newPaymentCategory.substr(0, 1).toUpperCase() + newPaymentCategory.substr(1);

        newCategoryDiv = ($(".onceOfPaymentCategoryRow").last()).clone();
        newCategoryDiv.insertAfter($(".onceOfPaymentCategoryRow").last());
        newCategoryDiv.find('input').val(newPaymentCategory);
        newCategoryDiv.find('input').prop('id', newIdInputWithCategory);
        newCategoryDiv.find('.editPaymentCategory').prop('id', newEditCategoryLinkId);
        newCategoryDiv.find('.deleteUserPaymentCategory').prop('id', newDeleteCategoryLinkId);

        $('#newPaymentCategory').val("");
    }

    function openCategoryExistInfoModal() {
        $("#category-exist-info").modal('show');
    }
});
