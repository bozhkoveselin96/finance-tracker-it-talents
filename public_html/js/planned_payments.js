function addPlannedPayment() {
    let selectAccount = $("#account");
    $.get("app/index.php?target=account&action=getAll",
        function (data) {
        $.each(data.data, function (key, value) {
            selectAccount.append($("<option />").val(this.id).text(this.name + ' - ' + this.current_amount));
        })
    }, 'json')
    .fail(function (xhr, status, error) {
        alert(error);
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
        alert(error);
    });
}

function showUserPlannedPayments() {
    $.get("app/index.php?target=plannedPayment&action=getAll",
        function (data) {
            let table = $("<table />");
            table.attr("id", "budgets-table");

            table.append("<tr><th>Planned payments</th></tr>");
            $.each(data.data, function (key, value) {
                let tr = $("<tr />");
                tr.attr("id", value.id);
                $.each(value, function (k, v) {
                    let td = $("<td />");
                    if (k === 'status') {
                        if (v == 1) {
                            td.text("Active");
                        } else {
                            td.text("Not active");
                        }
                    } else {
                        td.text(v);
                    }

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