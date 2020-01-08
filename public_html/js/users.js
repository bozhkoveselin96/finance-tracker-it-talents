$(document).ready(function () {
    $("input#first_name").attr('value', localStorage.getItem('first_name'));
    $("input#last_name").attr('value', localStorage.getItem('last_name'));
    $("img#img").attr('src', 'app/' + localStorage.getItem('avatar_url'));

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
                showModal('Success', 'Edit succesfull!' + isEditPassMsg);

                localStorage.setItem("first_name", response.first_name);
                localStorage.setItem("last_name", response.last_name);
                localStorage.setItem("avatar_url", response.avatar_url);
                window.location.replace('editprofile.html');
            },
            error: function (xhr, status, error) {
                if (xhr.status === 401) {
                    localStorage.removeItem("id");
                    localStorage.removeItem("first_name");
                    localStorage.removeItem("last_name");
                    localStorage.removeItem("avatar_url");
                    window.location.replace('login.html');
                } else {
                    showModal(error, xhr.responseJSON.message);
                }
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
                window.location.replace("login.html");
            },
            error: function (xhr, status, error) {
                showModal(error, xhr.responseJSON.message);
                $("#btnSubmit").prop("disabled", false);
            }
        });

    });

    $("#logout").on('click', function (event) {
        event.preventDefault();

        $.post("app/index.php?target=user&action=logout", function (data) {
                localStorage.removeItem("id");
                localStorage.removeItem("first_name");
                localStorage.removeItem("last_name");
                localStorage.removeItem("avatar_url");
                window.location.replace('login.html');
        })
        .fail(function (xhr, status, error) {
            showModal(error, xhr.responseJSON.message);
        });
    });
});