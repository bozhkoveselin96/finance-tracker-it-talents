function getIncomesAndOutcomes(diagramType = 'pie', fromDate = null, toDate = null) {
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

            $("#allIncomesAndOutcomes").remove();
            $("#firstChart").append('<canvas id="allIncomesAndOutcomes"></canvas>');
            let pie = $('#allIncomesAndOutcomes');

            let myChart = new Chart(pie, {
                type: diagramType,
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
                        display: false
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                let dataset = data.datasets[tooltipItem.datasetIndex];
                                let meta = dataset._meta[Object.keys(dataset._meta)[0]];
                                let total = meta.total;
                                let currentValue = dataset.data[tooltipItem.index];
                                let percentage = parseFloat((currentValue/total*100).toFixed(2));
                                return currentValue + ' (' + percentage + '%)';
                            },
                            title: function(tooltipItem, data) {
                                return data.labels[tooltipItem[0].index];
                            }
                        }
                    }
                }
            });
        },
        'json')
        .fail(function (xhr, status, error) {
            alert(error);
        });
}

function getIncomesByCategory(diagramType = 'pie', fromDate = null, toDate = null) {
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

            $("#IncomesByCategory").remove();
            $("#secondChart").append('<canvas id="IncomesByCategory"></canvas>');
            let pie = $('#IncomesByCategory');

            let myChart = new Chart(pie, {
                type: diagramType,
                data: {
                    labels: labelsTable,
                    datasets: [{
                        label: labelsTable,
                        data: dataTable
                    }]
                },
                options: {
                    title: {
                        display: false,
                    },
                    plugins: {
                        colorschemes: {
                            scheme: 'brewer.DarkTwo8'
                        }

                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                let dataset = data.datasets[tooltipItem.datasetIndex];
                                let meta = dataset._meta[Object.keys(dataset._meta)[0]];
                                let total = meta.total;
                                let currentValue = dataset.data[tooltipItem.index];
                                let percentage = parseFloat((currentValue/total*100).toFixed(2));
                                return currentValue + ' (' + percentage + '%)';
                            },
                            title: function(tooltipItem, data) {
                                return data.labels[tooltipItem[0].index];
                            }
                        }
                    }
                }
            });
        },
        'json')
        .fail(function (xhr, status, error) {
            alert(error);
        });
}

function getOutcomesByCategory(diagramType = 'pie', fromDate = null, toDate = null) {
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

            $("#OutcomesByCategory").remove();
            $("#thirdChart").append('<canvas id="OutcomesByCategory"></canvas>');
            let pie = $('#OutcomesByCategory');
            let myChart = new Chart(pie, {
                type: diagramType,
                data: {
                    labels: labelsTable,
                    datasets: [{
                        label: labelsTable,
                        data: dataTable
                    }]
                },
                options: {
                    title: {
                        display: false,
                    },
                    plugins: {
                        colorschemes: {
                            scheme: 'office.Excel16'
                        }
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                let dataset = data.datasets[tooltipItem.datasetIndex];
                                let meta = dataset._meta[Object.keys(dataset._meta)[0]];
                                let total = meta.total;
                                let currentValue = dataset.data[tooltipItem.index];
                                let percentage = parseFloat((currentValue/total*100).toFixed(2));
                                return currentValue + ' (' + percentage + '%)';
                            },
                            title: function(tooltipItem, data) {
                                return data.labels[tooltipItem[0].index];
                            }
                        }
                    }
                }
            });
        },
        'json')
        .fail(function (xhr, status, error) {
            alert(error);
        });
}