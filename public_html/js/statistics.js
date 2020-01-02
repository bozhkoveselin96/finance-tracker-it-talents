function getIncomesAndOutcomes(fromDate = null, toDate = null) {
    $.get("app/index.php?target=statistic&action=getIncomesOutcomes", {
        from_date: fromDate,
        to_date: toDate
        },
        function (response) {
            let labelsTable = [];
            let dataTable = [];
            $.each(response, function (key, value) {
                $.each(value, function (k, v) {
                    labelsTable.push(k);
                    dataTable.push(v);
                })
            });

            let pie = $('#allIncomesAndOutcomes');
            let myChart = new Chart(pie, {
                type: 'pie',
                data: {
                    labels: labelsTable,
                    datasets: [{
                        label: labelsTable,
                        backgroundColor: ["#00ff00", "#ff0000"],
                        data: dataTable
                    }]
                },
                options: {
                    title: {
                        display: true,
                        text: 'Income and outcome'
                    }
                }
            });
        },
        'json')
        .fail(function (xhr, status, error) {
            alert(error);
        });
}

function getIncomesByCategory(fromDate = null, toDate = null) {
    $.get("app/index.php?target=statistic&action=getIncomesByCategory", {
            from_date: fromDate,
            to_date: toDate
        },
        function (response) {
            let labelsTable = [];
            let dataTable = [];
            $.each(response, function (key, value) {
                labelsTable.push(value.category_name);
                dataTable.push(value.amount);
            });

            let pie = $('#IncomesByCategory');
            let myChart = new Chart(pie, {
                type: 'pie',
                data: {
                    labels: labelsTable,
                    datasets: [{
                        label: labelsTable,
                        backgroundColor: ["#00ff00", "#ff0000"],
                        data: dataTable
                    }]
                },
                options: {
                    title: {
                        display: true,
                        text: 'Incomes by category'
                    }
                }
            });
        },
        'json')
        .fail(function (xhr, status, error) {
            alert(error);
        });
}

function getOutcomesByCategory(fromDate = null, toDate = null) {
    $.get("app/index.php?target=statistic&action=getOutcomesByCategory", {
            from_date: fromDate,
            to_date: toDate
        },
        function (response) {
            let labelsTable = [];
            let dataTable = [];
            $.each(response, function (key, value) {
                labelsTable.push(value.category_name);
                dataTable.push(value.amount);
            });

            let pie = $('#OutcomesByCategory');
            let myChart = new Chart(pie, {
                type: 'pie',
                data: {
                    labels: labelsTable,
                    datasets: [{
                        label: labelsTable,
                        backgroundColor: ["#00ff00", "#ff0000"],
                        data: dataTable
                    }]
                },
                options: {
                    title: {
                        display: true,
                        text: 'Outcomes by category'
                    }
                }
            });
        },
        'json')
        .fail(function (xhr, status, error) {
            alert(error);
        });
}