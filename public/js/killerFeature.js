$(document).ready(function() {
    let limit = 0;
    let leftToSpend = 0;
    let sumExpensesAndNewAmount = 0;
    let currentMonthExpense = 0;
    let currentCategory = "";

    $("#killerFeature").hide();

    $("#expenseCategory").change(function() {
        currentCategory = $("#expenseCategory").val();
        setKiller(currentCategory);
    });

    $('#amount').bind("keyup", function(event) {
        let amount = getAmount();
        currentMonthExpense = parseFloat(currentMonthExpense);
        sumExpensesAndNewAmount = currentMonthExpense + amount;

        setBackgroundKiller(leftToSpend, amount);
        $("#sumExpensesAndNewAmount").text(sumExpensesAndNewAmount);
    });

    function setKiller(category) {
        $("#killerFeature").hide("slow");

        $.ajax({
            type: "POST",
            url: "/Expense/getKillerData",
            success: function(data) {
                userExpensesCategoriesData = jQuery.parseJSON(data);
                assignDataToKiller(userExpensesCategoriesData, category);
            }
        });

    }

    function assignDataToKiller(userExpensesCategoriesData, category) {
        $.each(userExpensesCategoriesData, function(i, userExpenseCategoryData) {
            if (category == userExpenseCategoryData.category_type && userExpenseCategoryData.killer_feature == 1) {
                let amount = getAmount();
                leftToSpend = getLeftToSpend(userExpenseCategoryData.killer_feature_value, userExpenseCategoryData.current_month_expense);
                currentMonthExpense = parseFloat(userExpenseCategoryData.current_month_expense);
                sumExpensesAndNewAmount = currentMonthExpense + amount;

                $("#limitValue").text("Limit: " + userExpenseCategoryData.killer_feature_value);
                $("#currentMonthExpense").text("Dotychczas wydano: " + userExpenseCategoryData.current_month_expense);
                $("#leftToSpend").text("Pozostało do wydania: " + leftToSpend);
                $("#sumExpensesAndNewAmount").text(sumExpensesAndNewAmount);
                ($("#killerFeature").css("display") == "flex") ? setBackgroundKillerWithDelay(leftToSpend, amount): setBackgroundKiller(leftToSpend, amount);
                $("#killerFeature").show("slow");
            }
        });
    }

    function getAmount() {
        let amount = $("#amount").val();
        amount = amount.replace(/,/g, ".");
        (parseFloat(amount)) ? amount = parseFloat(amount): amount = 0;

        return amount;
    }

    function getLeftToSpend(killerFeatureValue, currentMonthExpense) {
        let leftToSpend = killerFeatureValue - currentMonthExpense;
        if (leftToSpend < 0) {
            leftToSpend = 0;
        }

        return leftToSpend;
    }

    function setBackgroundKillerWithDelay(leftToSpend, amount) {
        setTimeout(function() {
            setBackgroundKiller(leftToSpend, amount);
        }, 400);
    }

    function setBackgroundKiller(leftToSpend, amount) {
        if (leftToSpend < amount) {
            $("#killerFeature").children().removeClass("bg-success");
            $("#killerFeature").children().addClass("bg-danger");
        } else {
            $("#killerFeature").children().removeClass("bg-danger");
            $("#killerFeature").children().addClass("bg-success");
        }
    }

});
