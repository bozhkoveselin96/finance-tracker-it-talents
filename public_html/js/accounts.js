let _editButton = function (event) {
    let trId = $(this).closest("tr").attr("id");
    let tdRename = $("#" + trId + " .name");
    let form = $("<input class='renamer' type='text' value='" + tdRename.text() + "' name='name'>");
    tdRename.html(form);
    let button = $(this);
    button.text("Save");
    button.unbind(event);
    button.bind("click", function (event2) {
        let trId = button.closest("tr").attr("id");
        let renamer = $("#" + trId + " .renamer");
        $.post("app/index.php?target=account&action=edit",
            {
                edit : true,
                account_id : trId,
                name : renamer.val(),
            }, function (data) {
                tdRename.text(data.data.name);
                button.unbind();
                button.text('Edit');
                button.bind('click', _editButton);
            }, 'json')
            .fail(function (xhr, status, error) {
                if (xhr.status === 401) {
                    localStorage.removeItem("id");
                    localStorage.removeItem("first_name");
                    localStorage.removeItem("last_name");
                    localStorage.removeItem("avatar_url");
                    window.location.replace('login.html');
                } else {
                    showModal(error, xhr.responseJSON.message);
                }
            });
    })
};

let _deleteButton = function (event) {
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
                } else {
                    showModal(error, xhr.responseJSON.message);
                }
            });
    }
};

function getAllAccounts() {
    $.get("app/index.php?target=account&action=getAll",
        function (response) {
            let table = $("#accounts");
            let selectAccount = $("#from_account");
            $.each(response.data, function (key, value) {

                if (selectAccount.length) {
                    selectAccount.append($("<option />").val(this.id).text(this.name + ' (' + this.current_amount + ' ' + this.currency + ')'));
                }

                let tr = $("<tr />");
                tr.attr("id", value.id);

                let accName = $("<td></td>");
                accName.addClass('name');
                accName.text(value.name);

                let accAmount = $("<td></td>");
                accAmount.addClass('amount');
                accAmount.text(value.current_amount);
                accAmount.append('&nbsp;' + value.currency);


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
                deleteItemButton.bind("click", _deleteButton);
                editItemButton.bind("click", _editButton);
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
            } else {
                showModal(error, xhr.responseJSON.message);
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
                cardFooterText.append('&nbsp;' + value.currency);

                cardFooter.append(cardFooterText);

                secondDiv.append(cardBody);
                secondDiv.append(cardFooter);
                mainDiv.append(secondDiv);

                $("#accountsMain").append(mainDiv);
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
            } else {
                showModal(error, xhr.responseJSON.message);
            }
        });
}

$(document).ready(function () {
    $("form#addaccount").on("submit", function (e) {
        e.preventDefault();
        let form = $(this);
        let action = form.attr("action");
        let data = form.serialize() + '&' + $("#submit").attr("name");
        $.post(action, data, function (data) {
            $("#addAcountModal").modal('hide');
            showModal('Success', data.msg);
            form.trigger("reset");
            let table = $("#accounts");

            let tr = $("<tr />");
            tr.attr("id", data.data.id);

            let accName = $("<td></td>");
            accName.addClass('name');
            accName.text(data.data.name);

            let accAmount = $("<td></td>");
            accAmount.text(data.data.current_amount);
            accAmount.append('&nbsp;' + data.data.currency);


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
            deleteItemButton.bind("click", _deleteButton);
            editItemButton.bind("click", _editButton);
            tr.append(editItem);
            tr.append(deleteItem);
            table.prepend(tr);

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

    $("form#addtransfer").on("submit", function (e) {
        e.preventDefault();
        let form = $(this);
        let action = form.attr("action");
        let data = form.serialize() + '&' + $("#maketransfersubmit").attr("name");
        $.post(action, data, function (data) {
            $("#makeTransfer").modal('hide');
            showModal('Success', data.msg);
            form.trigger("reset");
            let fromAccountId = data.data.fromTransaction.account.id;
            let toAccountId = data.data.toTransaction.account.id;
            $("tr#"+fromAccountId+" .amount").html(data.data.fromTransaction.account.current_amount + "&nbsp;" + data.data.fromTransaction.account.currency);
            $("tr#"+toAccountId+" .amount").html(data.data.toTransaction.account.current_amount + "&nbsp;" + data.data.toTransaction.account.currency);
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

    let time_event = $('#time_event');
    if(time_event.length) {
        time_event.datetimepicker();
    }

    let selectAccount = $("#from_account");
    let toAccount = $("#to_account");
    if(selectAccount.length && toAccount.length) {
        selectAccount.on("change", function () {
            toAccount.empty();
            toAccount.append('<option value="-1">Select to which account</option>');
            if (selectAccount.val() == -1) {
                return false;
            }
            $.get("app/index.php?target=account&action=getAll",
                function (response) {
                    $.each(response.data, function (key, value) {
                        if (value.id != selectAccount.val()) {
                            toAccount.append($("<option />").val(this.id).text(this.name + ' (' + this.current_amount + ' ' + this.currency + ')'));
                        }
                    })
                }, 'json')
                .fail(function (xhr, status, error) {
                    showModal(error, xhr.responseJSON.message);
                });
        });
    }

    if ($("#accounts").length) {
        getAllAccounts();
    }

    if ($("#accountsMain").length) {
        getAccountsMain();
    }
});