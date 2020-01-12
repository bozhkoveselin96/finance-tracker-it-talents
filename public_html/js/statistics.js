function getIncomesAndOutcomes(diagramType = 'pie', account_id, currency = 'BGN', daterange) {
    $.get("app/index.php?target=statistic&action=getIncomesOutcomes", {
            daterange: daterange,
            currency: currency,
            account_id: account_id
        },
        function (response) {
            let labelsTable = ['Outcomes', 'Incomes'];
            let dataTable = [response.data.outcomeSum, response.data.incomeSum];

            $("#allIncomesAndOutcomes").remove();
            $("#firstChart").append('<canvas id="allIncomesAndOutcomes"></canvas>');
            let pie = $('#allIncomesAndOutcomes');

            let myChart = new Chart(pie, {
                type: diagramType,
                data: {
                    labels: labelsTable,
                    datasets: [{
                        label: labelsTable,
                        backgroundColor: ["#ff0000", "#00ff00"],
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
                                return currentValue + ' ' + response.data.currency + ' (' + percentage + '%)';
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
            if (xhr.status === 401) {
                localStorage.removeItem("id");
                localStorage.removeItem("first_name");
                localStorage.removeItem("last_name");
                localStorage.removeItem("avatar_url");
                window.location.replace('login.html');
            }else {
                showModal(error, xhr.responseJSON.message);
            }
        });
}

function getIncomesByCategory(diagramType = 'pie', account_id, currency = 'BGN', daterange) {
    $.get("app/index.php?target=statistic&action=getSumByCategory", {
            daterange: daterange,
            currency: currency,
            account_id: account_id,
            category_type: 1
        },
        function (response) {
            let labelsTable = [];
            let dataTable = [];
            $.each(response.data, function (key, value) {
                labelsTable.push(key);
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
                                return currentValue + ' ' + response.msg + ' (' + percentage + '%)';
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
            if (xhr.status === 401) {
                localStorage.removeItem("id");
                localStorage.removeItem("first_name");
                localStorage.removeItem("last_name");
                localStorage.removeItem("avatar_url");
                window.location.replace('login.html');
            }else {
                showModal(error, xhr.responseJSON.message);
            }
        });
}

function getOutcomesByCategory(diagramType = 'pie', account_id, currency = 'BGN', daterange) {
    $.get("app/index.php?target=statistic&action=getSumByCategory", {
            daterange: daterange,
            currency: currency,
            account_id: account_id,
            category_type: 0
        },
        function (response) {
            let labelsTable = [];
            let dataTable = [];
            $.each(response.data, function (key, value) {
                labelsTable.push(key);
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
                                return currentValue + ' ' + response.msg + ' (' + percentage + '%)';
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
            if (xhr.status === 401) {
                localStorage.removeItem("id");
                localStorage.removeItem("first_name");
                localStorage.removeItem("last_name");
                localStorage.removeItem("avatar_url");
                window.location.replace('login.html');
            }else {
                showModal(error, xhr.responseJSON.message);
            }
        });
}

function getIncomesAndOutcomesLastXDays(days = 7, currency = 'BGN') {
    $.get("app/index.php?target=statistic&action=getDataForTheLastXDays", {
        days : days,
        currency: currency
    },
        function (response) {
            let labelsTable = [];

            let incomes = [];
            let outcomes = [];
            $.each(response.data, function (key, value) {
                incomes.push(value.income);
                outcomes.push(value.outcome);
                labelsTable.push(key);
            });

            let myMainChart = $("#mainChartLine");
            myMainChart.empty();
            let chart = $('<canvas id="incomesOutcomesXDays" width="100%" height="30"></canvas>');
            myMainChart.append(chart);
            let myChart = new Chart(chart, {
                type: 'line',
                data: {
                    labels: labelsTable,
                    datasets: [{
                        data: incomes,
                        label: "Income",
                        borderColor: "#00ff00",
                    }, {
                        data: outcomes,
                        label: "Outcome",
                        borderColor: "#ff0000",
                    }
                    ]
                },
                options: {
                    title: {
                        display: false
                    },
                    tooltips: {
                        enabled: true,
                        mode: 'single',
                        callbacks: {
                            label: function(tooltipItems, data) {
                                return tooltipItems.yLabel + ' ' + response.msg;
                            }
                        }
                    },
                }
            });
        },
        'json')
        .fail(function (xhr, status, error) {
            if (xhr.status === 401) {
                localStorage.removeItem("id");
                localStorage.removeItem("first_name");
                localStorage.removeItem("last_name");
                localStorage.removeItem("avatar_url");
                window.location.replace('login.html');
            }else {
                showModal(error, xhr.responseJSON.message);
            }
        });
}