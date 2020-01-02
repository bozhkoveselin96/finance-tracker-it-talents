function getAllAccounts() {
    $.get("app/index.php?target=account&action=getAll",
        function (response) {
            let table = $("<table />");
            table.attr("id", "accounts-table");

            $.each(response.data, function (key, value) {
                let tr = $("<tr />");
                tr.attr("id", value.id);
                $.each(value, function (k, v) {
                    let td = $("<td />").text(v);
                    td.addClass(k);
                    tr.append(td);
                });
                let editItem = $("<td></td>");
                let editItemButton = $("<button>Edit</button>");
                editItem.append(editItemButton);
                let deleteItem = $("<td></td>");
                let deleteItemButton = $("<button>Delete</button>");
                deleteItem.append(deleteItemButton);
                deleteItemButton.bind("click", function () {
                    let trId = $(this).closest("tr").attr("id");
                    let accountName = $("#" + trId + " .name").text();
                    if (confirm("Are you sure you want to delete " + accountName + "?")) {
                        $.post("app/index.php?target=account&action=delete",
                            {
                                delete : true,
                                account_id : trId,
                            }, function (data) {
                                $("#"+trId).fadeOut(1500);
                            }, 'json')
                            .fail(function (xhr, status, error) {
                                alert(error);
                            });
                    }
                });
                editItemButton.bind("click", function (event) {
                    let trId = $(this).closest("tr").attr("id");
                    let tdRename = $("#" + trId + " .name");
                    let form = $("<input class='renamer' type='text' value='" + tdRename.text() + "' name='name'>");
                    tdRename.html(form);
                    $(this).text("Save");
                    $(this).unbind(event);
                    $(this).bind("click", function (event2) {
                        let trId = $(this).closest("tr").attr("id");
                        let renamer = $("#" + trId + " .renamer");
                        $.post("app/index.php?target=account&action=edit",
                            {
                                edit : true,
                                account_id : trId,
                                name : renamer.val(),
                            }, function (data) {
                                getAllAccounts();
                            }, 'json')
                            .fail(function (xhr, status, error) {
                                alert(error);
                            });
                    })
                });
                tr.append(editItem);
                tr.append(deleteItem);
                table.append(tr);
            });

            $("#container").html(table);
        },
        'json')
        .fail(function (xhr, status, error) {
            alert(error);
        });
}