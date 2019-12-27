function getAllCategories(category_type) {
    $.get("app/index.php?target=category&action=getAll",
        {
            user_id: sessionStorage.getItem("id"),
            category_type: category_type,
        }
        , function (data) {
            if (data.status === true) {
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
                        tr.append(td);
                    });
                    table.append(tr);
                });

                $("#container").append(table);
            }
        }, 'json');
}