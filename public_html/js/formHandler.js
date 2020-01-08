$(document).ready(function () {
    $("form").on("submit", function (e) {
        e.preventDefault();
        let form = $(this);
        let action = form.attr("action");
        let data = form.serialize() + '&' + $("#submit").attr("name");
        $.post(action, data, function (data) {
            if(data.target === 'login') {
                localStorage.setItem("id", data.id);
                localStorage.setItem("first_name", data.first_name);
                localStorage.setItem("last_name", data.last_name);
                localStorage.setItem("avatar_url", data.avatar_url);
                showModal('Success', 'You logged in successfully!');
                setTimeout(function () {
                    window.location.replace('index.html');
                }, 2000);
            } else if(data.target === 'addaccount') {
                $("#addAcountModal").modal('hide');
                showModal('Success', 'You added account successfully!');
                $("#accounts").empty();
                getAllAccounts();
            } else if(data.target === 'category') {
                $("#addCategoryModal").modal('hide');
                showModal('Success', 'You added category successfully!');
                $("#categories").empty();
                getAllCategories(1);
                getAllCategories(0);
            } else if(data.target === 'transaction') {
                $("#addTransactionModal").modal('hide');
                showModal('Success', 'You added transaction successfully!');
                $("#transactions").empty();
                getTransactionsMain();
            } else if (data.target === 'planned_payment') {
                $("#addPlannedPayment").modal('hide');
                showModal('Success', 'You added planned payment successfully!');
                $("#planned_payments").empty();
                showUserPlannedPayments();
            } else if(data.target === 'budget') {
                $("#addBudget").modal('hide');
                showModal('Success', 'You added budget successfully!');
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
            }else {
                showModal(error, xhr.responseJSON.message);
            }
        });
    });
});