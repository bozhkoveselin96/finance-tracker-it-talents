function addBudget() {
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

function showUserBudgets() {
    $.get("app/index.php?target=budget&action=getAll",
        function (data) {
                let table = $("#budgets");

                $.each(data.data, function (key, value) {
                    let tr = $("<tr />");
                    tr.attr("id", value.id);

                    let category = $("<td />");
                    category.text(value.name);
                    let amount = $("<td />");
                    amount.text(value.amount);
                    let spent = $("<td />");
                    spent.text(value.budget_status);
                    let fromDate = $("<td />");
                    fromDate.text(value.from_date);
                    let toDate = $("<td />");
                    toDate.text(value.to_date);

                    tr.append(category);
                    tr.append(amount);
                    tr.append(spent);
                    tr.append(fromDate);
                    tr.append(toDate);

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