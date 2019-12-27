function addTransaction() {
    let selectAccount = $("#account");
    $.get("app/index.php?target=account&action=getAll", {user_id: sessionStorage.getItem("id")}, function (data) {
        if (data.status === true) {
            $.each(data.data, function (key, value) {
                selectAccount.append($("<option />").val(this.id).text(this.name + ' - ' + this.current_amount));
            })
        }
    }, 'json');

    let type = $("#type");
    type.on("change", function () {
        let selectCategory = $("#category");
        selectCategory.empty();

        if (this.value == 0 || this.value == 1) {
            $.get("app/index.php?target=category&action=getAll",
                {
                    user_id: sessionStorage.getItem("id"),
                    category_type: this.value,
                }
                , function (data) {
                    if (data.status === true) {
                        $.each(data.data, function (key, value) {
                            selectCategory.append($("<option />").val(value.id).text(value.name));
                        });
                    }
                }, 'json');
        }

    });
}

function showUserTransactions() {
    $.get("app/index.php?target=transaction&action=showUserTransactions",
        {
            user_id: sessionStorage.getItem("id"),
        }
        , function (data) {
            if (data.status === true) {
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
            }
        }, 'json');
}