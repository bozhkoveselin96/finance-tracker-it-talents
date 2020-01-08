function getAllCategories() {
    $.get("app/index.php?target=category&action=getAll",
        function (data) {
            let table = $("#categories");

            $.each(data.data, function (key, value) {
                let tr = $("<tr />");
                tr.attr("id", value.id);

                let name = $("<td></td>");
                name.text(value.name);

                let icon = $("<td></td>");
                let iconI = $("<i />");
                iconI.addClass(value.icon);
                icon.append(iconI);

                let type = $("<td></td>");
                if (value.type == 0) {
                    type.text('Outcome');
                } else {
                    type.text('Income');
                }

                tr.append(name);
                tr.append(icon);
                tr.append(type);

                table.append(tr);
            });
        }, 'json')
        .fail(function (xhr, status, error) {
            if (status === 401) {
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
    $("form#addcategory").on("submit", function (e) {
        e.preventDefault();
        let form = $(this);
        let action = form.attr("action");
        let data = form.serialize() + '&' + $("#submit").attr("name");
        $.post(action, data, function (data) {
            $("#addCategoryModal").modal('hide');
            showModal('Success', 'You added category successfully!');
            $("#categories").empty();
            getAllCategories();
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