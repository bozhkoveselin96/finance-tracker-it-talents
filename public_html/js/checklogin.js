$.post("app/index.php?target=user&action=checkLogin", function (data) {
    sessionStorage.setItem("id", data.id);
    sessionStorage.setItem("name", data.full_name);
}, 'json')
    .fail(function (xhr, status, error) {
        sessionStorage.removeItem('id');
        sessionStorage.removeItem('name');
    });