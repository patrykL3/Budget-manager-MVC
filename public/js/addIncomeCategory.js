$(document).ready(function() {

    let newIncomeCategory;


    $(document).on('click', '#addNewIncomeCategory', function() {
        newIncomeCategory = $('#newIncomeCategory').val();
        if (newIncomeCategory != "") {
            $.ajax({
                type: "POST",
                data: {
                    newIncomeCategory: newIncomeCategory
                },
                url: "/Settings/addIncomeCategory",
                success: function(data) {
                    let newIncomeCategoryId = jQuery.parseJSON(data);
                    (newIncomeCategoryId != '') ? createNewDivWithIncomeCategory(newIncomeCategoryId): openCategoryExistInfoModal();
                }
            });
        }
    });


    function createNewId(baseSpan, newId) {
        let fullId = baseSpan.concat(newId);

        return fullId;
    }

    function createNewDivWithIncomeCategory(newIncomeCategoryId) {
        let newIdInputWithCategory = createNewId("valueIncomeCategoryId", newIncomeCategoryId);
        let newEditCategoryLinkId = createNewId("editIncomeCategoryId", newIncomeCategoryId);
        let newDeleteCategoryLinkId = createNewId("deleteIncomeCategoryId", newIncomeCategoryId);
        newIncomeCategory = newIncomeCategory.toLowerCase();
        newIncomeCategory = newIncomeCategory.substr(0, 1).toUpperCase() + newIncomeCategory.substr(1);

        newCategoryDiv = ($(".onceOfIncomeCategoryRow").last()).clone();
        newCategoryDiv.insertAfter($(".onceOfIncomeCategoryRow").last());
        newCategoryDiv.find('input').val(newIncomeCategory);
        newCategoryDiv.find('input').prop('id', newIdInputWithCategory);
        newCategoryDiv.find('.editIncomeCategory').prop('id', newEditCategoryLinkId);
        newCategoryDiv.find('.deleteUserIncomeCategory').prop('id', newDeleteCategoryLinkId);

        $('#newIncomeCategory').val("");
    }

    function openCategoryExistInfoModal() {
        $("#category-exist-info").modal('show');
    }
});
