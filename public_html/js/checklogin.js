if (sessionStorage.getItem("id") === null) {
    $.post("app/index.php?target=user&action=checkLogin", function (data) {
        if (data.status === true) {
            sessionStorage.setItem("id", data.id);
            sessionStorage.setItem("name", data.full_name);
        }
    }, 'json');
}