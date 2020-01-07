function getAllAccounts() {
    $.get("app/index.php?target=account&action=getAll",
        function (response) {
            let table = $("#accounts");

            $.each(response.data, function (key, value) {
                let tr = $("<tr />");
                tr.attr("id", value.id);

                let accName = $("<td></td>");
                accName.addClass('name');
                accName.text(value.name);

                let accAmount = $("<td></td>");
                accAmount.text(value.current_amount);

                tr.append(accName);
                tr.append(accAmount);

                let editItem = $("<td></td>");
                let editItemButton = $("<button>Edit</button>");
                editItemButton.addClass('btn btn-primary');
                editItem.append(editItemButton);
                let deleteItem = $("<td></td>");
                let deleteItemButton = $("<button>Delete</button>");
                deleteItemButton.addClass('btn btn-danger');
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
                                if (xhr.status === 401) {
                                    localStorage.removeItem("id");
                                    localStorage.removeItem("first_name");
                                    localStorage.removeItem("last_name");
                                    localStorage.removeItem("avatar_url");
                                    window.location.replace('login.html');
                                }
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
                                $("#accounts").empty();
                                getAllAccounts();
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
                    })
                });
                tr.append(editItem);
                tr.append(deleteItem);
                table.append(tr);
            });
        },
        'json')
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

function getAccountsMain() {
    $.get("app/index.php?target=account&action=getAll",
        function (response) {


            $.each(response.data, function (key, value) {
                let mainDiv = $("<div class=\"col-xl-3 col-sm-6 mb-3\"></div>");
                let secondDiv = $("<div class=\"card text-white bg-success o-hidden h-100\"></div>");

                let cardBody = $("<div class=\"card-body\"></div>");
                let cardBodyIcon = $("<div class=\"card-body-icon\"></div>");
                let cardBodyI = $("<i class=\"fas fa-fw fa-comment-dollar\"></i>");
                let cardBodyText = $("<div class=\"mr-5\"></div>");
                cardBodyText.text(value.name);
                cardBodyIcon.append(cardBodyI);
                cardBody.append(cardBodyIcon);
                cardBody.append(cardBodyText);


                let cardFooter = $("<span class=\"card-footer text-white clearfix small z-1\"></span>");
                let cardFooterText = $("<span class=\"float-left\"></span>");
                cardFooterText.text(value.current_amount);
                cardFooter.append(cardFooterText);

                secondDiv.append(cardBody);
                secondDiv.append(cardFooter);
                mainDiv.append(secondDiv);

                $("#accounts").append(mainDiv);
            });


        },
        'json')
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