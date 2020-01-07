function addPlannedPayment() {
    let selectAccount = $("#account");
    $.get("app/index.php?target=account&action=getAll",
        function (data) {
        $.each(data.data, function (key, value) {
            selectAccount.append($("<option />").val(this.id).text(this.name + ' - ' + this.current_amount));
        })
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

    let selectCategory = $("#category");

    $.get("app/index.php?target=category&action=getAll",
    {
        category_type: 0,
    },
    function (data) {
        $.each(data.data, function (key, value) {
            selectCategory.append($("<option />").val(value.id).text(value.name));
        });
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
}

function showUserPlannedPayments() {
    $.get("app/index.php?target=plannedPayment&action=getAll",
        function (data) {
            let table = $("#planned_payments");

            $.each(data.data, function (key, value) {
                let tr = $("<tr />");
                //{"data":[{"day_for_payment":"15","amount":"240","account_name":"\u0411\u0430\u043d\u043a\u0430","category_name":"Loan","status":"1"}]}

                let dayForPayment = $("<td />");
                dayForPayment.text(value.day_for_payment);
                let amount = $("<td />");
                amount.text(value.amount);
                let account = $("<td />");
                account.text(value.account_name);
                let category = $("<td />");
                category.text(value.category_name);
                let status = $("<td />");
                if (value.status == 0) {
                    status.text('Not active');
                } else {
                    status.text('Active');
                }

                tr.append(dayForPayment);
                tr.append(amount);
                tr.append(account);
                tr.append(category);
                tr.append(status);
                table.append(tr);
            });

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
}