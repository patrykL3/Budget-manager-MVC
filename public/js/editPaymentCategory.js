$(document).ready(function() {

    let paymentCategoryId;
    let paymentValue;
    let rowWithPaymentCategoryToEdit;

    $(document).on('click', '.editPaymentCategory', function() {
        let linkId = $(this).attr("id");
        paymentCategoryId = getPaymentCategoryId(linkId);
        paymentValue = $('#valuePaymentCategoryId' + paymentCategoryId).val();

        $("#editingPaymentCategoryIconsId" + paymentCategoryId).removeAttr('hidden');
        $("#valuePaymentCategoryId" + paymentCategoryId).removeAttr('disabled');
        $("#valuePaymentCategoryId" + paymentCategoryId).focus();
        rowWithPaymentCategoryToEdit = (($(this).parent()).parent()).parent();
    });

    $(document).on('focusout', '.valuePaymentCategory', function() {
        $('#valuePaymentCategoryId' + paymentCategoryId).val(paymentValue);
    });

    $(document).on('mousedown', '.savePaymentCategory', function() {
        let newCategoryType = $('#valuePaymentCategoryId' + paymentCategoryId).val();

        $.ajax({
            type: "POST",
            url: "/Settings/updatePaymentCategory",
            data: {
                paymentCategoryId: paymentCategoryId,
                newCategoryType: newCategoryType
            },
            success: function(data) {
                if (data && data != 'empty') {
                    let newPaymentCategoryId = data;
                    rowWithPaymentCategoryToEdit.remove();
                    createNewDivWithPaymentCategory(newPaymentCategoryId, newCategoryType);
                } else if (data === 'empty') {
                    openNoCategoryTypeInfoModal();
                } else {
                    openCategoryExistInfoModal();
                }
            }
        });
    });

    function createNewDivWithPaymentCategory(newPaymentCategoryId, newCategoryType) {
        let newIdInputWithCategory = createNewId("valuePaymentCategoryId", newPaymentCategoryId);
        let newEditCategoryLinkId = createNewId("editPaymentCategoryId", newPaymentCategoryId);
        let newDeleteCategoryLinkId = createNewId("deletePaymentCategoryId", newPaymentCategoryId);
        let newEditingPaymentCategoryIconsId = createNewId("editingPaymentCategoryIconsId", newPaymentCategoryId);
        let newSavePaymentCategoryId = createNewId("savePaymentCategoryId", newPaymentCategoryId);
        newCategoryType = newCategoryType.toLowerCase();
        newCategoryType = newCategoryType.substr(0, 1).toUpperCase() + newCategoryType.substr(1);

        newCategoryDiv = ($(".onceOfPaymentCategoryRow").last()).clone();
        newCategoryDiv.insertAfter($(".onceOfPaymentCategoryRow").last());
        newCategoryDiv.find('input').val(newCategoryType);
        newCategoryDiv.find('input').prop('id', newIdInputWithCategory);
        newCategoryDiv.find('.editPaymentCategory').prop('id', newEditCategoryLinkId);
        newCategoryDiv.find('.deleteUserPaymentCategory').prop('id', newDeleteCategoryLinkId);
        newCategoryDiv.find('.editingCategoryIcons').prop('id', newEditingPaymentCategoryIconsId);
        newCategoryDiv.find('.savePaymentCategory').prop('id', newSavePaymentCategoryId);

    }

    function createNewId(baseSpan, newId) {
        let fullId = baseSpan.concat(newId);

        return fullId;
    }

    function getPaymentCategoryId(linkId) {
        let paymentCategoryId = linkId.replace("editPaymentCategoryId", "");
        return paymentCategoryId;
    }

    function openCategoryExistInfoModal() {
        $("#category-exist-info").modal('show');
    }

    function openNoCategoryTypeInfoModal() {
        $("#no-category-type-info").modal('show');
    }

});
