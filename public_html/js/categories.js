function getAllCategories(category_type) {
    $.get("app/index.php?target=category&action=getAll",
        {
            category_type: category_type,
        },
        function (data) {
            let table = $("<table />");
            table.attr("id", "categories-table-"+category_type);

            if (category_type === 1) {
                table.append("<tr><th>Income</th></tr>");
            } else {
                table.append("<tr><th>Outcome</th></tr>");
            }

            $.each(data.data, function (key, value) {
                let tr = $("<tr />");
                tr.attr("id", value.id);
                $.each(value, function (k, v) {
                    let td = $("<td />").text(v);
                    td.addClass(k);
                    if (k == 'icon') {
                        td.text('');
                        let icon = $("<i />");
                        icon.addClass(v);
                        td.append(icon);
                    }
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