$(document).ready(function () {
    $("#formMain").on('change', function () {
        getIncomesAndOutcomesLastXDays($("#days").val(), $("#currencies").val());
    });
    getAccountsMain();
    getTransactionsMain();
    getIncomesAndOutcomesLastXDays();
});