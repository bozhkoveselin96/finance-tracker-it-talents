$(document).ready(function () {
    $("#container").on("submit", 'form', function (e) {
        e.preventDefault();
        let form = $(this);
        let action = form.attr("action");
        let data = form.serialize() + '&' + $(":submit").attr("name");
        $.post(action, data, function (data) {
            if (data.target === 'register') {
                alert('Registration succesfull!');
                $.get('view/user/login.html', function (data) {
                    $("#container").html(data);
                });
            } else if(data.target === 'login') {
                alert("Login succesfull!");
                sessionStorage.setItem("name", data.full_name);
                sessionStorage.setItem("id", data.id);
                $.get("view/menu.html", function (data) {
                    $("#menu").html(data);
                });

                $.get("view/main.html", function (data) {

                    $("#container").html(data);
                });
            } else if(data.target === 'addaccount') {
                alert('Account added succesfully!');
                getAllAccounts();
            } else if(data.target === 'category') {
                alert("Category added!");
                $("#container").empty();
                getAllCategories(1);
                getAllCategories(0);
            } else if(data.target === 'transaction') {
                alert('Transaction added!');
                $("#container").empty();
                showUserTransactions();
            } else if (data.target === 'planned_payment') {
                alert('Planned payment added succesfully!');
                $("#container").empty();
                showUserPlannedPayments();
            } else if(data.target === 'budget') {
                alert("Budget added succesfully!");
                $("#container").empty();
                showUserBudgets();
            }
        }, 'json')
        .fail(function (xhr, status, error) {
            alert(error);
        });
    });
});