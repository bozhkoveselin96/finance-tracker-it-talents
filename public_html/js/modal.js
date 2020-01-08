function showModal(title, data) {
    $("#dialogModal .modal-title").text(title);
    $("#dialogModal .modal-body").text(data);
    $("#dialogModal").modal('show');
}