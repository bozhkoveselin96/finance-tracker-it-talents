$(document).ready(function () {
    if ($("#btnEdit").length && localStorage.getItem('id') === null) {
        window.location.replace('login.html');
    }
    $("#avatar_url").attr('src', 'app/' + localStorage.getItem('avatar_url'));
    $("input#first_name").attr('value', localStorage.getItem('first_name'));
    $("input#email").attr('value', localStorage.getItem('email'));
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
                showModal('Success', response.msg);
                localStorage.setItem("first_name", response.data.first_name);
                localStorage.setItem("last_name", response.data.last_name);
                localStorage.setItem("avatar_url", response.data.avatar_url);

                $("input#first_name").attr('value', response.data.first_name);
                $("input#last_name").attr('value', response.data.last_name);
                $("img#img").attr('src', 'app/' + response.data.avatar_url);
                $("#avatar_url").attr('src', 'app/' + response.data.avatar_url);
            },
            error: function (xhr, status, error) {
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
            dataType: 'json',
            cache: false,
            timeout: 600000,
            success: function (data) {
                showModal('Success', data.msg);
                let buttonLogin = $("#toLogin");
                buttonLogin.text('To login');
                buttonLogin.on("click", function () {
                    window.location.replace('login.html');
                });

                setTimeout(function () {
                    window.location.replace("login.html");
                }, 2000);
            },
            error: function (xhr, status, error) {
                if (xhr.status === 500) {
                    showModal('Server error', 'Sorry, something went wrong. We will try our best to fix this. Please try again later.');
                } else {
                    showModal(error, xhr.responseJSON.message);
                }
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
                localStorage.removeItem("email");
                window.location.replace('login.html');
        }, 'json')
        .fail(function (xhr, status, error) {
            if (xhr.status === 500) {
                showModal('Server error', 'Sorry, something went wrong. We will try our best to fix this. Please try again later.');
            } else {
                showModal(error, xhr.responseJSON.message);
            }
        });
    });

    $("#delete-user").on(
        'click',
        function (event) {
            event.preventDefault();
            $.post('app/index.php?target=user&action=delete',
                {
                    delete:true
                },
                function (data) {
                localStorage.removeItem("id");
                localStorage.removeItem("first_name");
                localStorage.removeItem("last_name");
                localStorage.removeItem("avatar_url");
                localStorage.removeItem("email");
                window.location.replace('login.html');
            },'json')
            .fail(function (xhr, status, error) {
                if (xhr.status === 500) {
                    showModal('Server error', 'Sorry, something went wrong. We will try our best to fix this. Please try again later.');
                } else {
                    showModal(error, xhr.responseJSON.message);
                }
            });
        }
    );

    $("form#login").on("submit", function (e) {
        e.preventDefault();
        let form = $(this);
        let action = form.attr("action");
        let data = form.serialize() + '&' + $("#submit").attr("name");
        $.post(action, data, function (data) {
            localStorage.setItem("id", data.data.id);
            localStorage.setItem("first_name", data.data.first_name);
            localStorage.setItem("last_name", data.data.last_name);
            localStorage.setItem("avatar_url", data.data.avatar_url);
            localStorage.setItem("email", data.data.email);
            showModal('Success', data.msg);
            let buttonLogin = $("#toLogin");
            buttonLogin.text('To main');
            buttonLogin.on("click", function () {
                window.location.replace('index.html');
            });
            setTimeout(function () {
                window.location.replace('index.html');
            }, 2000);
        }, 'json')
            .fail(function (xhr, status, error) {
                if (xhr.status === 500) {
                    showModal('Server error', 'Sorry, something went wrong. We will try our best to fix this. Please try again later.');
                } else {
                    showModal(error, xhr.responseJSON.message);
                }
            });
    });

    $("form#forgotPass").on("submit", function (e) {
        e.preventDefault();
        let form = $(this);
        let action = form.attr("action");
        let data = form.serialize() + '&' + $("#submit").attr("name");
        $.post(action, data, function (data) {
            showModal('Success', data.msg);
            setTimeout(function () {
                window.location.replace('login.html');
            }, 5000);
        }, 'json')
            .fail(function (xhr, status, error) {
                if (xhr.status === 500) {
                    showModal('Server error', 'Sorry, something went wrong. We will try our best to fix this. Please try again later.');
                } else {
                    showModal(error, xhr.responseJSON.message);
                }
            });
    });

    $("form#changeForgottenPass").on("submit", function (e) {
        e.preventDefault();
        let form = $(this);

        var url = new URL(window.location.href);
        var token = url.searchParams.get("token");

        let action = form.attr("action");
        let data = form.serialize() + '&' + $("#submit").attr("name") + '&token=' + token;
        $.post(action, data, function (data) {
            showModal('Success', data.msg);
            let buttonLogin = $("#toLogin");
            buttonLogin.text('To login');
            buttonLogin.on("click", function () {
                window.location.replace('login.html');
            });
            setTimeout(function () {
                window.location.replace('login.html');
            }, 2000);
        }, 'json')
            .fail(function (xhr, status, error) {
                if (xhr.status === 500) {
                    showModal('Server error', 'Sorry, something went wrong. We will try our best to fix this. Please try again later.');
                } else {
                    showModal(error, xhr.responseJSON.message);
                }
            });
    });
});