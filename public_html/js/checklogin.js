$.post("app/index.php?target=user&action=checkLogin", function (data) {
    sessionStorage.setItem("id", data.id);
    sessionStorage.setItem('first_name', data.first_name);
    sessionStorage.setItem('last_name', data.last_name);
    sessionStorage.setItem('avatar_url', data.avatar_url);
}, 'json')
    .fail(function (xhr, status, error) {
        sessionStorage.removeItem('id');
        sessionStorage.removeItem('first_name');
        sessionStorage.removeItem('last_name');
        sessionStorage.removeItem('avatar_url');
        window.location.replace('login.html');
    });