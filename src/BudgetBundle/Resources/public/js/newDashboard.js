budgetLoader = {
    resetForm: function (form) {
        form[0].reset();
    },
    getDateRange: function () {
        var dateFrom = $('#date_range_dateFrom').val();
        var dateTo = $('#date_range_dateTo').val();

        return [dateFrom, dateTo];
    },
    closeSideNavs: function () {
        resetSideNavs();
    },
    updateIncomeData: function () {
        var dateRange = budgetLoader.getDateRange();
        var data = {
            date_from: dateRange[0],
            date_to: dateRange[1]
        };
        $.ajax({
            url: '/api/income-data',
            data: data,
        }).success(function (data) {
            if (data.success) {
                $('#income-data').html(data.view);
                $('#income-total-number').html(data.total);
            }
        });
    },
    updateExpenseData: function () {
        var dateRange = budgetLoader.getDateRange();
        var data = {
            date_from: dateRange[0],
            date_to: dateRange[1]
        };
        $.ajax({
            url: '/api/expense-data',
            data: data
        }).success(function (data) {
            if (data.success) {
                $('#expense-data').html(data.view);
                $('#expense-total-number').html(data.total);
                $('#donut').html('');
                Morris.Donut({
                    element: 'donut',
                    data: data.chartData
                });
            }
        });
    },
    init: function () {
        $('body').on('click', '#income_submit', function (e) {
            e.preventDefault();
            var form = $(this).closest("form");
            var valid = form.valid();
            if (valid === true) {
                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serialize()
                }).success(function (data) {
                    if (data.success) {
                        budgetLoader.resetForm(form);
                        budgetLoader.updateIncomeData();
                        budgetLoader.closeSideNavs()

                    }
                });
            }
        })
            .on('click', '#income_submit_add_next', function (e) {
                e.preventDefault();
                var form = $(this).closest("form");
                var valid = form.valid();
                if (valid === true) {
                    $.ajax({
                        type: form.attr('method'),
                        url: form.attr('action'),
                        data: form.serialize()
                    }).success(function (data) {

                        if (data.success) {
                            budgetLoader.resetForm(form);
                            budgetLoader.updateIncomeData();
                        }
                    });
                }
            })
            .on('click', '#expense_submit', function (e) {
                e.preventDefault();
                var form = $(this).closest("form");
                var valid = form.valid();
                if (valid === true) {
                    $.ajax({
                        type: form.attr('method'),
                        url: form.attr('action'),
                        data: form.serialize()
                    }).success(function (data) {
                        if (data.success) {
                            budgetLoader.resetForm(form);
                            budgetLoader.updateExpenseData();
                            budgetLoader.closeSideNavs()
                        }
                    });
                }
            })
            .on('click', '#expense_submit_add_next', function (e) {
                e.preventDefault();
                var form = $(this).closest("form");
                var valid = form.valid();
                if (valid === true) {
                    $.ajax({
                        type: form.attr('method'),
                        url: form.attr('action'),
                        data: form.serialize()
                    }).success(function (data) {
                        if (data.success) {
                            budgetLoader.resetForm(form);
                            budgetLoader.updateExpenseData();
                        }
                    });
                }
            })
            .on('click', '#date_range_submit', function (e) {
                e.preventDefault();
                var form = $(this).closest("form");
                var valid = form.valid();
                if (valid === true) {
                    var dateRange = budgetLoader.getDateRange();
                    var currentUrl = window.location.href;
                    var newUrl = updateQueryStringParameter(currentUrl, 'date_from', dateRange[0]);
                    newUrl = updateQueryStringParameter(newUrl, 'date_to', dateRange[1]);
                    window.location.replace(newUrl);
                }
            })
    }
};

Morris.Donut({
    element: 'donut',
    data: chartData
});

jQuery(document).ready(function () {
    budgetLoader.init();

    $('#add-income').click(function (e) {
        resetSideNavs();
        document.getElementById("mySidenav").style.width = "500px";
        document.getElementById("main").style.marginRight = "500px";
    });
    $('#add-expense').click(function (e) {
        resetSideNavs();
        document.getElementById("expenseSidenav").style.width = "500px";
        document.getElementById("main").style.marginRight = "500px";
    });

    $('#income_dateTime').datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        stepping: 0.1
    });

    $('#expense_dateTime').datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        stepping: 1
    });
});

resetSideNavs = function () {
    document.getElementById("mySidenav").style.width = "0";
    document.getElementById("expenseSidenav").style.width = "0";
    document.getElementById("main").style.marginRight = "0";
};

function closeNav() {
    resetSideNavs();
}

function updateQueryStringParameter(uri, key, value) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
    }
    else {
        return uri + separator + key + "=" + value;
    }
}