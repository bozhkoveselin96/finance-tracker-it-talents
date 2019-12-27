function addBudget() {
    let selectCategory = $("#category");

    $.get("app/index.php?target=category&action=getAll",
        {
            user_id: sessionStorage.getItem("id"),
            category_type: 0,
        }
        , function (data) {
            if (data.status === true) {
                $.each(data.data, function (key, value) {
                    selectCategory.append($("<option />").val(value.id).text(value.name));
                });
            }
        }, 'json');
}

function showUserBudgets() {
    $.get("app/index.php?target=budget&action=getAll",
        {
            user_id: sessionStorage.getItem("id"),
        }
        , function (data) {
            if (data.status === true) {
                let table = $("<table />");
                table.attr("id", "planned_payments-table");

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
            }
        }, 'json');
}