$(document).ready(function () {
    $("input#first_name").attr('value', sessionStorage.getItem('first_name'));
    $("input#last_name").attr('value', sessionStorage.getItem('last_name'));
    $("img#img").attr('src', 'app/' + sessionStorage.getItem('avatar_url'));

    $("#btnEdit").click(function (event) {
        event.preventDefault();

        var form = $('#formEdit')[0];

        var data = new FormData(form);
        data.append("edit", 'true');

        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: "app/index.php?target=user&action=edit",
            data: data,
            dataType: 'json',
            processData: false,
            contentType: false,
            cache: false,
            timeout: 600000,
            success: function (response) {
                let isEditPassMsg = ' Password is changed!';
                if (response.password_edited === false) {
                    isEditPassMsg = ' Password is not changed!'
                }
                alert('Edit succesfull!' + isEditPassMsg);

                sessionStorage.setItem("first_name", response.first_name);
                sessionStorage.setItem("last_name", response.last_name);
                sessionStorage.setItem("avatar_url", response.avatar_url);
                $.get('view/user/edit.html', function (data) {
                    $("#container").html(data);
                });
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });

    });

    $("#btnRegister").click(function (event) {
        event.preventDefault();

        var form = $('#formRegister')[0];

        var data = new FormData(form);
        data.append("register", 'true');

        $("#btnSubmit").prop("disabled", true);

        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: "app/index.php?target=user&action=register",
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 600000,
            success: function (data) {
                alert('Registration succesfull!');
                $.get('view/user/login.html', function (data) {
                    $("#container").html(data);
                });
            },
            error: function (xhr, status, error) {
                alert(error);
            }
        });

    });

    $("#logout").off().on('click', function (event) {
        event.preventDefault();

        $.post("app/index.php?target=user&action=logout", function (data) {
            sessionStorage.removeItem('id');
            sessionStorage.removeItem('first_name');
            sessionStorage.removeItem('last_name');
            sessionStorage.removeItem('avatar_url');
            alert('See ya!');
            $("#menu").empty();
            $.get('view/user/login.html', function (data) {
                $("#container").html(data);
            });
        })
        .fail(function (xhr, status, error) {
            alert(error);
        });
    });
});