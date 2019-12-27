$(document).ready(function () {
    $("#menu, #container").on("click", '.loader', function(event) {
        event.preventDefault();
        let href = 'view/' + $(this).attr("href");
        $.get(href, function (data) {
            let cont = $("#container");
            cont.html(data);
        });
    });
});