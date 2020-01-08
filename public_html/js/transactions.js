function addTransaction() {
    let selectAccount = $("#account");
    $.get("app/index.php?target=account&action=getAll",
        function (response) {
        $.each(response, function (key, value) {
            selectAccount.append($("<option />").val(this.id).text(this.name + ' - ' + this.current_amount));
        })
    }, 'json')
        .fail(function (xhr, status, error) {
            showModal(error, xhr.responseJSON.message);
        });

    let type = $("#type");
    type.on("change", function () {
        let selectCategory = $("#category");
        selectCategory.empty();

        if (this.value == 0 || this.value == 1) {
            $.get("app/index.php?target=category&action=getAll",
                {
                    category_type: this.value,
                }
                , function (data) {
                    $.each(data.data, function (key, value) {
                        selectCategory.append($("<option />").val(value.id).text(value.name));
                    });
                }, 'json')
                .fail(function (xhr, status, error) {
                    showModal(error, xhr.responseJSON.message);
                });
        }
    });
}

function getTransactionsMain() {
    $.get("app/index.php?target=transaction&action=showUserTransactions",
        function (response) {
            let table = $("#transactions");

            $.each(response.data, function (key, value) {
                let tr = $("<tr />");

                let amount = $("<td></td>");
                amount.text(value.amount);
                let transactionType = $("<td></td>");
                if (value.transaction_type == 0) {
                    transactionType.text('Outcome');
                } else {
                    transactionType.text('Income');
                }
                let accountName = $("<td></td>");
                accountName.text(value.account_name);
                let categoryName = $("<td></td>");
                categoryName.text(value.category_name);
                let note = $("<td></td>");
                note.text(value.note);
                let timeEvent = $("<td></td>");
                timeEvent.text(value.time_event);

                tr.append(transactionType);
                tr.append(amount);
                tr.append(accountName);
                tr.append(categoryName);
                tr.append(note);
                tr.append(timeEvent);

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