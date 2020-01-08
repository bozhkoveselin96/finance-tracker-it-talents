function addPlannedPayment() {
    let selectAccount = $("#account");
    $.get("app/index.php?target=account&action=getAll", function (data) {
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
        }else {
            showModal(error, xhr.responseJSON.message);
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
        }else {
            showModal(error, xhr.responseJSON.message);
        }
    });
}

function showUserPlannedPayments() {
    $.get("app/index.php?target=plannedPayment&action=getAll",
        function (data) {
            let table = $("#planned_payments");

            $.each(data.data, function (key, value) {
                let tr = $("<tr />");

                let dayForPayment = $("<td />");
                dayForPayment.text(value.day_for_payment);
                let amount = $("<td />");
                amount.text(value.amount);
                let account = $("<td />");
                account.text(value.account.name);

                let category = $("<td />");
                let icon = $("<i />");
                icon.addClass(value.category.icon);
                category.text(value.category.name);
                category.prepend(icon);

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
            }else {
                showModal(error, xhr.responseJSON.message);
            }
        });
}

$(document).ready(function () {
    $("form#add_planned_payment").on("submit", function (e) {
        e.preventDefault();
        let form = $(this);
        let action = form.attr("action");
        let data = form.serialize() + '&' + $("#submit").attr("name");
        $.post(action, data, function (data) {
            $("#addPlannedPayment").modal('hide');
            showModal('Success', 'You added planned payment successfully!');
            $("#planned_payments").empty();
            showUserPlannedPayments();
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