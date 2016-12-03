
function getIncomeList($dateFrom, $dateTo, $Ids){
    /** global: incomeDateFrom */
    incomeDateFrom = $dateFrom;
    /** global: incomeDateTo */
    incomeDateTo = $dateTo;
    /** global: ajax_get_Income_List_URL */
    $.ajax({
        data: {date_from : $dateFrom, date_to : $dateTo, ids : $Ids},
        url: ajax_get_Income_List_URL,
        dataType: "json",
        async: true,
    }).done(function(data){
        /** global: IncomeTable */
        IncomeTable.destroy();
        UpdateIncomeTable(data.data);
        UpdateIncomeTableBasicInfo(data.dateFrom, data.dateTo, data.sum)
        return data;
    });

}

function getExpenseList($dateFrom, $dateTo, $Ids){
    incomeDateFrom = $dateFrom;
    incomeDateTo = $dateTo;
    /** global: ajax_get_Expense_List_URL */
    $.ajax({
        data: {date_from : $dateFrom, date_to : $dateTo, ids : $Ids},
        url: ajax_get_Expense_List_URL,
        dataType: "json",
        async: true,
    }).done(function(data){
        /** global: ExpenseTable */
        ExpenseTable.destroy();
        UpdateExpenseTable(data.data);
        UpdateExpenseTableBasicInfo(data.dateFrom, data.dateTo, data.sum)
        return data;
    });

}


$('#button_show_income').click(function(){

    var dateFrom = getDateFromInputValue();
    var dateTo = getDateToInputValue();
    var Ids = getCategoryIds("#income-category-list");
    var data = getIncomeList(dateFrom,dateTo,Ids);

})

$('#button_show_expenses').click(function(){

    var dateFrom = getDateFromInputValue();
    var dateTo = getDateToInputValue();
    var Ids = getCategoryIds("#expense-category-list");
    var data = getExpenseList(dateFrom,dateTo,Ids);

})

function UpdateIncomeTable($data) {
    IncomeTable = $('#income-table').DataTable( {
        responsive: true,
        data: $data,
        columns: [
            { title: "Date" },
            { title: "Description" },
            { title: "Category" },
            { title: "Money" },
        ]
    } );
}

function UpdateIncomeTableBasicInfo(dateFrom, dateTo, money) {
    $('#income-table-data-range').html(dateFrom + " : " + dateTo);
    $('#income-table-data-money').html(money);
}

function UpdateExpenseTable($data) {
    ExpenseTable = $('#expense-table').DataTable( {
        responsive: true,
        data: $data,
        columns: [
            { title: "Date" },
            { title: "Description" },
            { title: "Category" },
            { title: "Money" },
        ]
    } );
}

function UpdateExpenseTableBasicInfo(dateFrom, dateTo, money) {
    $('#expense-table-data-range').html(dateFrom + " : " + dateTo);
    $('#expense-table-data-money').html(money);
}

function getCategoryIds($listName){
    var Ids = [];

    $($listName).find(".active").each(function(){Ids.push($(this).data("id"))});

    return Ids;
}

function getDateFromInputValue() {
    return $("#time_input").children("input").val();
}

function getDateToInputValue() {
    return $("#time_input2").children("input").val();
}