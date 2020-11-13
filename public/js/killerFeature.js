$(document).ready(function() {
    let limit = 0;
    let leftToSpend = 0;
    let sumExpensesAndNewAmount = 0;
    let currentMonthExpense = 0;
    let currentCategory = "";

    $("#killerFeature").hide();


    $("#expenseCategory").change(function() {
        currentCategory = $("#expenseCategory").val();
        $("#killerFeature").hide("slow");
        if (isCurrentMonthExpense()) {
            setKiller(currentCategory);
        }
    });

    $("#date-to-fill").change(function() {
        if (isCurrentMonthExpense() && $("#killerFeature").css("display") != "flex") {
            setKiller(currentCategory);
        } else if (!isCurrentMonthExpense() && $("#killerFeature").css("display") == "flex") {
            $("#killerFeature").hide("slow");
        }
    });

    $('#amount').bind("keyup", function(event) {
        let amount = getAmount();
        currentMonthExpense = parseFloat(currentMonthExpense);
        sumExpensesAndNewAmount = currentMonthExpense + amount;

        setBackgroundKiller(limit, sumExpensesAndNewAmount);
        $("#sumExpensesAndNewAmount").text(sumExpensesAndNewAmount);
    });

    function setKiller(category) {

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
                limit = userExpenseCategoryData.killer_feature_value;
                leftToSpend = getLeftToSpend(limit, userExpenseCategoryData.current_month_expense);
                currentMonthExpense = parseFloat(userExpenseCategoryData.current_month_expense);
                sumExpensesAndNewAmount = currentMonthExpense + amount;

                $("#limitValue").text("Limit: " + limit);
                $("#currentMonthExpense").text("Dotychczas wydano: " + userExpenseCategoryData.current_month_expense);
                $("#leftToSpend").text("Pozostało do wydania: " + leftToSpend);
                $("#sumExpensesAndNewAmount").text(sumExpensesAndNewAmount);
                ($("#killerFeature").css("display") == "flex") ? setBackgroundKillerWithDelay(limit, sumExpensesAndNewAmount): setBackgroundKiller(limit, sumExpensesAndNewAmount);
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

        if (leftToSpend < 0) leftToSpend = 0;
        leftToSpend = (leftToSpend).toFixed(2);

        return leftToSpend;
    }

    function setBackgroundKillerWithDelay(limit, sumExpensesAndNewAmount) {
        setTimeout(function() {
            setBackgroundKiller(limit, sumExpensesAndNewAmount);
        }, 400);
    }

    function setBackgroundKiller(limit, sumExpensesAndNewAmount) {
        if (limit < sumExpensesAndNewAmount) {
            $("#killerFeature").children().removeClass("bg-success");
            $("#killerFeature").children().addClass("bg-danger");
        } else {
            $("#killerFeature").children().removeClass("bg-danger");
            $("#killerFeature").children().addClass("bg-success");
        }
    }

    function isCurrentMonthExpense() {
        let date = new Date();
        let currentMonth = date.getMonth() + 1;
        if (currentMonth < 10) currentMonth = "0" + currentMonth;
        let currentYear = date.getFullYear();
        let currentPeriod = currentYear + "-" + currentMonth;
        let newExpensePeriod = ($('#date-to-fill').val()).replace(/...$/, "");
        let isCurrentMonthExpense;

        (currentPeriod === newExpensePeriod) ? isCurrentMonthExpense = true: isCurrentMonthExpense = false;

        return isCurrentMonthExpense;
    }

});
