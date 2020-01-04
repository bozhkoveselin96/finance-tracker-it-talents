function addTransaction() {
    let selectAccount = $("#account");
    $.get("app/index.php?target=account&action=getAll",
        function (data) {
        $.each(data.data, function (key, value) {
            selectAccount.append($("<option />").val(this.id).text(this.name + ' - ' + this.current_amount));
        })
    }, 'json');

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
                }, 'json');
        }
    });
}

function showUserTransactions() {
    $.get("app/index.php?target=transaction&action=showUserTransactions",
        function (data) {
            let table = $("<table />");
            table.attr("id", "transactions-table");

            table.append("<tr><th>Transactions</th></tr>");

            $.each(data.data, function (key, value) {
                let tr = $("<tr />");
                tr.attr("id", value.id);
                $.each(value, function (k, v) {
                    let td = $("<td />").text(v);
                    td.addClass(k);
                    tr.append(td);
                });
                table.append(tr);
            });

            $("#container").append(table);
        }, 'json')
        .fail(function (xhr, status, error) {
            alert(error);
        });
}

function getTransactionsMain() {
    $.get("app/index.php?target=transaction&action=showUserTransactions",
        function (data) {
            let table = $("#transactions");

            $.each(data.data, function (key, value) {
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
            alert(error);
        });
}