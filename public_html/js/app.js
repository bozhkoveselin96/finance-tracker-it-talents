$(document).ready(function () {
    let url = "view/user/login.html";
    if (sessionStorage.getItem("id") !== null) {
        url = "view/main.html";
        $.get("view/menu.html", function (data) {
            $("#menu").html(data);
        });
    }
    $.get(url, function (data) {
        $("#container").html(data);
    });
});