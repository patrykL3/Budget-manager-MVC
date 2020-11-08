$(document).ready(function() {

    let incomeCategoryId;
    let incomeValue;
    let rowWithIncomeCategoryToEdit;

    $(document).on('click', '.editIncomeCategory', function() {
        let linkId = $(this).attr("id");
        incomeCategoryId = getIncomeCategoryId(linkId);
        incomeValue = $('#valueIncomeCategoryId' + incomeCategoryId).val();

        $("#editingIncomeCategoryIconsId" + incomeCategoryId).removeAttr('hidden');
        $("#valueIncomeCategoryId" + incomeCategoryId).removeAttr('disabled');
        $("#valueIncomeCategoryId" + incomeCategoryId).focus();
        rowWithIncomeCategoryToEdit = (($(this).parent()).parent()).parent();
    });

    $(document).on('focusout', '.valueIncomeCategory', function() {
        $('#valueIncomeCategoryId' + incomeCategoryId).val(incomeValue);
    });

    $(document).on('mousedown', '.saveIncomeCategory', function() {
        let newCategoryType = $('#valueIncomeCategoryId' + incomeCategoryId).val();

        $.ajax({
            type: "POST",
            url: "/Settings/updateIncomeCategory",
            data: {
                incomeCategoryId: incomeCategoryId,
                newCategoryType: newCategoryType
            },
            success: function(data) {
                if (data && data != 'empty') {
                    let newIncomeCategoryId = data;
                    rowWithIncomeCategoryToEdit.remove();
                    createNewDivWithIncomeCategory(newIncomeCategoryId, newCategoryType);
                } else if (data === 'empty') {
                    openNoCategoryTypeInfoModal();
                } else {
                    openCategoryExistInfoModal();
                }
            }
        });
    });

    function createNewDivWithIncomeCategory(newIncomeCategoryId, newCategoryType) {
        let newIdInputWithCategory = createNewId("valueIncomeCategoryId", newIncomeCategoryId);
        let newEditCategoryLinkId = createNewId("editIncomeCategoryId", newIncomeCategoryId);
        let newDeleteCategoryLinkId = createNewId("deleteIncomeCategoryId", newIncomeCategoryId);
        let newEditingIncomeCategoryIconsId = createNewId("editingIncomeCategoryIconsId", newIncomeCategoryId);
        let newSaveIncomeCategoryId = createNewId("saveIncomeCategoryId", newIncomeCategoryId);
        newCategoryType = newCategoryType.toLowerCase();
        newCategoryType = newCategoryType.substr(0, 1).toUpperCase() + newCategoryType.substr(1);

        newCategoryDiv = ($(".onceOfIncomeCategoryRow").last()).clone();
        newCategoryDiv.insertAfter($(".onceOfIncomeCategoryRow").last());
        newCategoryDiv.find('input').val(newCategoryType);
        newCategoryDiv.find('input').prop('id', newIdInputWithCategory);
        newCategoryDiv.find('.editIncomeCategory').prop('id', newEditCategoryLinkId);
        newCategoryDiv.find('.deleteUserIncomeCategory').prop('id', newDeleteCategoryLinkId);
        newCategoryDiv.find('.editingCategoryIcons').prop('id', newEditingIncomeCategoryIconsId);
        newCategoryDiv.find('.saveIncomeCategory').prop('id', newSaveIncomeCategoryId);

    }

    function createNewId(baseSpan, newId) {
        let fullId = baseSpan.concat(newId);

        return fullId;
    }

    function getIncomeCategoryId(linkId) {
        let incomeCategoryId = linkId.replace("editIncomeCategoryId", "");
        return incomeCategoryId;
    }

    function openCategoryExistInfoModal() {
        $("#category-exist-info").modal('show');
    }

    function openNoCategoryTypeInfoModal() {
        $("#no-category-type-info").modal('show');
    }

});
