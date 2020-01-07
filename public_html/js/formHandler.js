$(document).ready(function () {
    $("form").on("submit", function (e) {
        e.preventDefault();
        let form = $(this);
        let action = form.attr("action");
        let data = form.serialize() + '&' + $("#submit").attr("name");
        $.post(action, data, function (data) {
            if(data.target === 'login') {
                alert("Login succesfull!");
                localStorage.setItem("id", data.id);
                localStorage.setItem("first_name", data.first_name);
                localStorage.setItem("last_name", data.last_name);
                localStorage.setItem("avatar_url", data.avatar_url);
                window.location.replace('index.html');
            } else if(data.target === 'addaccount') {
                alert('Account added succesfully!');
                $("#addAcountModal").modal('hide');
                $("#accounts").empty();
                getAllAccounts();
            } else if(data.target === 'category') {
                alert("Category added!");
                $("#addCategoryModal").modal('hide');
                $("#categories").empty();
                getAllCategories(1);
                getAllCategories(0);
            } else if(data.target === 'transaction') {
                alert('Transaction added!');
                $("#addTransactionModal").modal('hide');
                $("#transactions").empty();
                getTransactionsMain();
            } else if (data.target === 'planned_payment') {
                alert('Planned payment added succesfully!');
                $("#addPlannedPayment").modal('hide');
                $("#planned_payments").empty();
                showUserPlannedPayments();
            } else if(data.target === 'budget') {
                alert("Budget added succesfully!");
                $("#addBudget").modal('hide');
                $("#budgets").empty();
                showUserBudgets();
            }
        }, 'json')
        .fail(function (xhr, status, error) {
            if (xhr.status === 401) {
                localStorage.removeItem("id");
                localStorage.removeItem("first_name");
                localStorage.removeItem("last_name");
                localStorage.removeItem("avatar_url");
                window.location.replace('login.html');
            }
        });
    });
});