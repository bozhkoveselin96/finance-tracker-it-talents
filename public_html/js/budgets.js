let _deleteButtonBudget = function (event) {
    let trId = $(this).closest("tr").attr("id");
    if (confirm("Are you sure you want to delete selected budget?")) {
        $.post("app/index.php?target=budget&action=delete",
            {
                delete : true,
                budget_id : trId,
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
                } else if (xhr.status === 500) {
                    showModal('Server error', 'Sorry, something went wrong. We will try our best to fix this. Please try again later.');
                } else {
                    showModal(error, xhr.responseJSON.message);
                }
            });
    }
};

function percentage(partialValue, totalValue) {
    return (100 * partialValue) / totalValue;
}

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
            } else if (xhr.status === 500) {
                showModal('Server error', 'Sorry, something went wrong. We will try our best to fix this. Please try again later.');
            } else {
                showModal(error, xhr.responseJSON.message);
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
                    category.text(value.category.name);
                    let icon = $("<i />");
                    icon.addClass(value.category.icon);
                    category.prepend(icon);

                    let budgetAmount = value.amount;
                    let amount = $("<td />");
                    amount.text(budgetAmount);
                    amount.append("&nbsp; " + value.currency);
                    let budgetSpent = value.budget_status;
                    let spent = $("<td />");
                    spent.text(budgetSpent);
                    spent.append("&nbsp; " + value.currency);

                    let percent = Math.round(percentage(budgetSpent, budgetAmount));
                    let bgcolor = '5cb85c';
                    if (budgetSpent > budgetAmount) {
                        bgcolor = 'D9534F';
                    }
                    let html = '             <td><div style="margin-top: 7px;" class="progress">\n' +
                        '                <div style="background-color: #'+bgcolor+'; width:'+percent+'%" class="progress-bar progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">\n' +
                        '                    '+percent+'%' +
                        '                </div>\n' +
                        '            </div></td>';

                    let progress = $(html);
                    let fromDate = $("<td />");
                    fromDate.text(value.from_date);
                    let toDate = $("<td />");
                    toDate.text(value.to_date);

                    let deleteItem = $("<td></td>");
                    let deleteItemButton = $("<button>Delete</button>");
                    deleteItemButton.addClass('btn btn-danger');
                    deleteItem.append(deleteItemButton);
                    deleteItemButton.bind("click", _deleteButtonBudget);

                    tr.append(category);
                    tr.append(amount);
                    tr.append(spent);
                    tr.append(progress);
                    tr.append(fromDate);
                    tr.append(toDate);
                    tr.append(deleteItem);

                    table.append(tr);
                });
            $('#dataTable').DataTable( {
                "order": [[ 4, "desc" ]],
                initComplete: function () {
                    this.api().columns(".selecting").every( function () {
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
            } else if (xhr.status === 500) {
                showModal('Server error', 'Sorry, something went wrong. We will try our best to fix this. Please try again later.');
            } else {
                showModal(error, xhr.responseJSON.message);
            }
        });
}

$(document).ready(function () {
    $('input[name="daterange"]').daterangepicker({drops : 'up'});
    $('input[name="daterange"]').val('');
    $("form#add_budget").on("submit", function (e) {
        e.preventDefault();
        let form = $(this);
        let action = form.attr("action");
        let data = form.serialize() + '&' + $("#submit").attr("name");
        $.post(action, data, function (data) {
            $("#addBudget").modal('hide');
            showModal('Success', data.msg);
            form.trigger("reset");
            let table = $("#budgets");
            let tr = $("<tr />");
            tr.attr("id", data.data.id);

            let category = $("<td />");
            category.text(data.data.category.name);
            let icon = $("<i />");
            icon.addClass(data.data.category.icon);
            category.prepend(icon);

            let budgetAmount = data.data.amount;
            let amount = $("<td />");
            amount.text(budgetAmount);
            amount.append("&nbsp; " + data.data.currency);
            let fromDate = $("<td />");
            fromDate.text(data.data.from_date);
            let toDate = $("<td />");
            toDate.text(data.data.to_date);

            let budgetSpent = data.data.budget_status;
            let spent = $("<td />");
            spent.text(budgetSpent);
            spent.append("&nbsp; " + data.data.currency);

            let percent = Math.round(percentage(budgetSpent, budgetAmount));
            let bgcolor = '5cb85c';
            if (budgetSpent > budgetAmount) {
                bgcolor = 'D9534F';
            }
            let html = '             <td><div style="margin-top: 7px;" class="progress">\n' +
                '                <div style="background-color: #'+bgcolor+'; width:'+percent+'%" class="progress-bar progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">\n' +
                '                    '+percent+'%' +
                '                </div>\n' +
                '            </div></td>';

            let progress = $(html);

            let deleteItem = $("<td></td>");
            let deleteItemButton = $("<button>Delete</button>");
            deleteItemButton.addClass('btn btn-danger');
            deleteItem.append(deleteItemButton);
            deleteItemButton.bind("click", _deleteButtonBudget);

            tr.append(category);
            tr.append(amount);
            tr.append(spent);
            tr.append(progress);
            tr.append(fromDate);
            tr.append(toDate);
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
                } else if (xhr.status === 500) {
                    showModal('Server error', 'Sorry, something went wrong. We will try our best to fix this. Please try again later.');
                } else {
                    showModal(error, xhr.responseJSON.message);
                }
            });
    });

    showUserBudgets();
    addBudget();
});