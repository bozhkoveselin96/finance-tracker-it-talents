let _editButton = function (event) {
    let trId = $(this).closest("tr").attr("id");
    let tdDayForPayment = $("#" + trId + " .day_for_payment");
    let form = $("<input class='dayPayment' type='text' value='" + tdDayForPayment.text() + "' name='name'>");
    tdDayForPayment.html(form);
    let tdAmount = $("#" + trId + " .amount");
    let amount = [];
    amount = tdAmount.text().split(" ");
    let formAmount = $('<input class="ppAmount" type="number" value="'+ amount[0] +'">');
    tdAmount.html(formAmount);

    let status = $("#" + trId + " .status");
    let inputStatus = $('' +
        '<select class="form-control-sm">' +
        '<option value="0">Not active</option>' +
        '<option selected value="1">Active</option>' +
        '</select>');
    status.html(inputStatus);

    let button = $(this);
    button.text("Save");
    button.unbind(event);
    button.bind("click", function (event2) {
        let trId = button.closest("tr").attr("id");
        $.post("app/index.php?target=plannedPayment&action=edit",
            {
                edit : true,
                planned_payment_id : trId,
                day_for_payment : form.val(),
                amount : formAmount.val(),
                status : inputStatus.val(),
            }, function (data) {
                tdDayForPayment.text(data.data.day_for_payment);
                tdAmount.text(data.data.amount);
                tdAmount.append(' ' + data.data.currency);
                if (data.data.status == 1) {
                    status.text('Active');
                    status.removeClass('text-danger');
                    status.addClass('text-success');
                    status.addClass('font-weight-bold');

                } else {
                    status.text('Not active');
                    status.removeClass('text-success');
                    status.addClass('text-danger');
                    status.addClass('font-weight-bold');
                }

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
    if (confirm("Are you sure you want to delete selected planned payment?")) {
        $.post("app/index.php?target=plannedPayment&action=delete",
            {
                delete : true,
                planned_payment_id : trId,
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

function addPlannedPayment() {
    let selectAccount = $("#account");
    $.get("app/index.php?target=account&action=getAll", function (data) {
        $.each(data.data, function (key, value) {
            selectAccount.append($("<option />").val(this.id).text(this.name + ' (' + this.current_amount + ' ' + this.currency + ')'));
        })
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
        }else {
            showModal(error, xhr.responseJSON.message);
        }
    });
}

function showUserPlannedPayments() {
    $.get("app/index.php?target=plannedPayment&action=getAll",
        function (data) {
            let table = $("#planned_payments");

            $.each(data.data, function (key, value) {
                let tr = $("<tr />");
                tr.attr('id', value.id);

                let dayForPayment = $("<td />");
                dayForPayment.text(value.day_for_payment);
                dayForPayment.addClass('day_for_payment');
                let amount = $("<td data-order='" + value.amount +"'></td>");
                amount.text(value.amount);
                amount.append(' ' + value.currency);
                amount.addClass('amount');
                let account = $("<td />");
                account.text(value.account.name);

                let category = $("<td />");
                let icon = $("<i class='pull-right' />");
                icon.addClass(value.category.icon);
                category.text(value.category.name);
                category.append(icon);

                let status = $("<td />");
                if (value.status == 0) {
                    status.text('Not active');
                    status.addClass('text-danger');
                    status.addClass('font-weight-bold');
                } else {
                    status.text('Active');
                    status.addClass('text-success');
                    status.addClass('font-weight-bold');
                }
                status.addClass('status');

                let editItem = $("<td></td>");
                let deleteItem = $("<td></td>");

                let editItemButton = $("<button>Edit</button>");
                editItemButton.addClass('btn btn-primary');
                editItem.append(editItemButton);
                let deleteItemButton = $("<button>Delete</button>");
                deleteItemButton.addClass('btn btn-danger');
                deleteItem.append(deleteItemButton);
                deleteItemButton.bind("click", _deleteButton);
                editItemButton.bind("click", _editButton);

                tr.append(dayForPayment);
                tr.append(amount);
                tr.append(account);
                tr.append(category);
                tr.append(status);
                tr.append(editItem);
                tr.append(deleteItem);
                table.append(tr);
            });
            $('#dataTable').DataTable( {
                "order": [[ 4, "asc" ]],
                initComplete: function () {
                    this.api().columns('.selecting').every( function () {
                        var column = this;
                        var select = $('<select class="form-control form-control-sm"><option value=""></option></select>')
                            .appendTo( $(column.header()).empty() )
                            .on( 'change', function () {
                                var val = $.fn.dataTable.util.escapeRegex(
                                    $(this).val()
                                );

                                column
                                    .search( val ? '^'+val+'$' : '', true, false )
                                    .draw();
                            } );

                        column.data().unique().sort().each( function ( d, j ) {
                            select.append( '<option>'+d+'</option>' )
                        } );
                    } );
                }
            } );
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
}

$(document).ready(function () {
    $("form#add_planned_payment").on("submit", function (e) {
        e.preventDefault();
        let form = $(this);
        let action = form.attr("action");
        let data = form.serialize() + '&' + $("#submit").attr("name");
        $.post(action, data, function (data) {
            $("#addPlannedPayment").modal('hide');
            showModal('Success', data.msg);
            form.trigger("reset");

            let table = $("#planned_payments");
            let tr = $("<tr />");
            tr.attr('id', data.data.id);

            let dayForPayment = $("<td />");
            dayForPayment.text(data.data.day_for_payment);
            dayForPayment.addClass('day_for_payment');
            let amount = $("<td />");
            amount.text(data.data.amount);
            amount.addClass('amount');
            let account = $("<td />");
            account.text(data.data.account.name);

            let category = $("<td />");
            let icon = $("<i class='pull-right' />");
            icon.addClass(data.data.category.icon);
            category.text(data.data.category.name);
            category.append(icon);

            let status = $("<td />");
            if (data.data.status == 0) {
                status.text('Not active');
                status.addClass('text-danger');
                status.addClass('font-weight-bold');
            } else {
                status.text('Active');
                status.addClass('text-success');
                status.addClass('font-weight-bold');
            }
            status.addClass('status');


            let editItem = $("<td></td>");
            let deleteItem = $("<td></td>");

            let editItemButton = $("<button>Edit</button>");
            editItemButton.addClass('btn btn-primary');
            editItem.append(editItemButton);
            let deleteItemButton = $("<button>Delete</button>");
            deleteItemButton.addClass('btn btn-danger');
            deleteItem.append(deleteItemButton);
            deleteItemButton.bind("click", _deleteButton);
            editItemButton.bind("click", _editButton);

            tr.append(dayForPayment);
            tr.append(amount);
            tr.append(account);
            tr.append(category);
            tr.append(status);
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

    showUserPlannedPayments();

    addPlannedPayment();
});