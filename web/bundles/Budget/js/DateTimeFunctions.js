/**
 * Created by Lezas on 2016-10-15.
 */

function getCurrentMonthFirstDay() {
    var date = new Date();
    var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);

    return firstDay.getFullYear() + '-' + (("0" + (firstDay.getMonth() + 1)).slice(-2)) + '-' + (("0" + (firstDay.getDate())).slice(-2));

}

function getCurrentMonthLastDay() {
    var date = new Date();
    var lastDay = new Date(date.getFullYear(), date.getMonth()+1 , 0);

    return lastDay.getFullYear() + '-' + (("0" + (lastDay.getMonth() + 1)).slice(-2)) + '-' + (("0" + (lastDay.getDate())).slice(-2));

}

function getPreviousMonthFirstDay() {
    var date = new Date();
    var firstDay = new Date(date.getFullYear(), date.getMonth()-1, 1);

    return firstDay.getFullYear() + '-' + (("0" + (firstDay.getMonth() + 1)).slice(-2)) + '-' + (("0" + (firstDay.getDate())).slice(-2));

}

function getPreviousMonthLastDay() {
    var date = new Date();
    var lastDay = new Date(date.getFullYear(), date.getMonth() , 0);

    return lastDay.getFullYear() + '-' + (("0" + (lastDay.getMonth() + 1)).slice(-2)) + '-' + (("0" + (lastDay.getDate())).slice(-2));

}

$('#select-this-month').click(function(){

    var dateFrom = getCurrentMonthFirstDay() + " 00:00";
    var dateTo = getCurrentMonthLastDay() + " 23:59";

    $('#date-from').val(dateFrom);
    $('#date-to').val(dateTo);
})

$('#select-previous-month').click(function(){

    var dateFrom = getPreviousMonthFirstDay() + " 00:00";
    var dateTo = getPreviousMonthLastDay() + " 23:59";

    $('#date-from').val(dateFrom);
    $('#date-to').val(dateTo);
})

//WEEK

function getCurrentWeekFirstDay() {
    var curr = new Date; // get current date
    var first = curr.getDate() - curr.getDay(); // First day is the day of the month - the day of the week
    var firstDay = new Date(curr.setDate(first));
    return firstDay.getFullYear() + '-' + (("0" + (firstDay.getMonth()+1 )).slice(-2)) + '-' + (("0" + (firstDay.getDate()+1)).slice(-2));

}

function getCurrentWeekLastDay() {
    var curr = new Date; // get current date
    var first = curr.getDate() - curr.getDay(); // First day is the day of the month - the day of the week
    var last = first + 6;

    var lastDay = new Date(curr.setDate(last));

    return lastDay.getFullYear() + '-' + (("0" + (lastDay.getMonth() + 1)).slice(-2)) + '-' + (("0" + (lastDay.getDate()+1)).slice(-2));

}

function getPreviousWeekFirstDay() {
    var curr = new Date; // get current date
    var first = curr.getDate() - curr.getDay(); // First day is the day of the month - the day of the week
    var last = first -7;

    var lastDay = new Date(curr.setDate(last));

    return lastDay.getFullYear() + '-' + (("0" + (lastDay.getMonth() + 1)).slice(-2)) + '-' + (("0" + (lastDay.getDate()+1)).slice(-2));

}

function getPreviousWeekLastDay() {
    var curr = new Date; // get current date
    var first = curr.getDate() - curr.getDay(); // First day is the day of the month - the day of the week
    var last = first -1;

    var lastDay = new Date(curr.setDate(last));

    return lastDay.getFullYear() + '-' + (("0" + (lastDay.getMonth() + 1)).slice(-2)) + '-' + (("0" + (lastDay.getDate()+1)).slice(-2));

}

$('#select-this-week').click(function(){

    var dateFrom = getCurrentWeekFirstDay() + " 00:00";
    var dateTo = getCurrentWeekLastDay() + " 23:59";

    $('#date-from').val(dateFrom);
    $('#date-to').val(dateTo);
})

$('#select-previous-week').click(function(){

    var dateFrom = getPreviousWeekFirstDay() + " 00:00";
    var dateTo = getPreviousWeekLastDay() + " 23:59";
    $('#date-from').val(dateFrom);
    $('#date-to').val(dateTo);
})

//YEAR

function getCurrentYearFirstDay() {
    firstDay = new Date(new Date().getFullYear(), 0, 1);

    return firstDay.getFullYear() + '-' + (("0" + (firstDay.getMonth()+1 )).slice(-2)) + '-' + (("0" + (firstDay.getDate())).slice(-2));
}

function getCurrentYearLastDay() {
    firstDay = new Date(new Date().getFullYear(), 11, 31);

    return firstDay.getFullYear() + '-' + (("0" + (firstDay.getMonth() +1)).slice(-2)) + '-' + (("0" + (firstDay.getDate())).slice(-2));
}

$('#select-this-year').click(function(){

    var dateFrom = getCurrentYearFirstDay() + " 00:00";
    var dateTo = getCurrentYearLastDay() + " 23:59";

    $('#date-from').val(dateFrom);
    $('#date-to').val(dateTo);
})

//TODAY

function getToday() {
    firstDay = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());

    return firstDay.getFullYear() + '-' + (("0" + (firstDay.getMonth()+1 )).slice(-2)) + '-' + (("0" + (firstDay.getDate())).slice(-2));
}

$('#select-today').click(function(){

    var dateFrom = getToday() + " 00:00";
    var dateTo = getToday() + " 23:59";

    $('#date-from').val(dateFrom);
    $('#date-to').val(dateTo);
})

//LIFETIME

$('#select-lifetime').click(function(){
    $('#date-from').val("Lifetime");
    $('#date-to').val("Lifetime");
})