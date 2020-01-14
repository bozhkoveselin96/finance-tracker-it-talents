let _editButton = function (event) {
    let trId = $(this).closest("tr").attr("id");
    let tdRename = $("#" + trId + " .name");
    let form = $("<input class='renamer' type='text' value='" + tdRename.text() + "' name='name'>");
    tdRename.html(form);
    let tdIcon = $("#" + trId + " .icon");
    let formIcon = $('<div class="icon-picker iconPickerGroup" data-pickerid="fa" data-iconsets=\'{"fa":"Pick Icon"}\'>\n' +
        '               <input class="icon_picker_edit" type="hidden" name="icon" />\n' +
        '             </div>\n');
    tdIcon.html(formIcon);
    $(formIcon).qlIconPicker({
        'save': 'class'
    });
    let button = $(this);
    button.text("Save");
    button.unbind(event);
    button.bind("click", function (event2) {
        let trId = button.closest("tr").attr("id");
        let renamer = $("#" + trId + " .renamer");
        let iconpicker = $("#" + trId + " .icon_picker_edit");
        $.post("app/index.php?target=category&action=edit",
            {
                edit : true,
                category_id : trId,
                name : renamer.val(),
                icon : iconpicker.val(),
            }, function (data) {
                let accounts = $("#accounts");
                tdRename.text(data.data.name);

                let iconI = $("<i />");
                iconI.addClass(data.data.icon);
                tdIcon.html(iconI);

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
    let category_name = $("#" + trId + " .name").text();
    if (confirm("Are you sure you want to delete " + category_name + "?")) {
        $.post("app/index.php?target=category&action=delete",
            {
                delete : true,
                category_id : trId,
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

function getAllCategories() {
    $.get("app/index.php?target=category&action=getAll",
        function (data) {
            let table = $("#categories");

            $.each(data.data, function (key, value) {
                let tr = $("<tr />");
                tr.attr("id", value.id);

                let name = $("<td></td>");
                name.text(value.name);
                name.addClass("name");

                let icon = $("<td></td>");
                let iconI = $("<i />");
                iconI.addClass(value.icon);
                icon.append(iconI);
                icon.addClass('icon');

                let type = $("<td></td>");
                if (value.type == 0) {
                    type.text('Outcome');
                } else {
                    type.text('Income');
                }
                tr.append(name);
                tr.append(icon);
                tr.append(type);
                let editItem = $("<td></td>");
                let deleteItem = $("<td></td>");

                if (value.owner === localStorage.getItem('id')) {
                    let editItemButton = $("<button>Edit</button>");
                    editItemButton.addClass('btn btn-primary');
                    editItem.append(editItemButton);
                    let deleteItemButton = $("<button>Delete</button>");
                    deleteItemButton.addClass('btn btn-danger');
                    deleteItem.append(deleteItemButton);
                    deleteItemButton.bind("click", _deleteButton);
                    editItemButton.bind("click", _editButton);
                }

                tr.append(editItem);
                tr.append(deleteItem);

                table.append(tr);
            });

            $('#dataTable').DataTable( {
                "order": [[ 0, "asc" ]],
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
                            select.append( '<option value="'+d+'">'+d+'</option>' )
                        } );
                    } );
                }
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
            showModal('Success', data.msg);
            form.trigger("reset");
            let table = $("#categories");

            let tr = $("<tr />");
            tr.attr("id", data.data.id);

            let name = $("<td></td>");
            name.text(data.data.name);
            name.addClass("name");

            let icon = $("<td></td>");
            let iconI = $("<i />");
            iconI.addClass(data.data.icon);
            icon.append(iconI);
            icon.addClass('icon');

            let type = $("<td></td>");
            if (data.data.type == 0) {
                type.text('Outcome');
            } else {
                type.text('Income');

            }

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

            tr.append(name);
            tr.append(icon);
            tr.append(type);
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

    $("#avatar_url").attr('src', 'app/' + localStorage.getItem('avatar_url'));
    $('.icon-picker').qlIconPicker({
        'save': 'class'
    });

    getAllCategories();
});