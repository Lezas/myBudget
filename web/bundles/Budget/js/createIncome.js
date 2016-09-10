var ROUNDING = 30 * 60 * 1000; /*ms*/
start = moment();
start = moment(Math.ceil((+start) / ROUNDING) * ROUNDING);
jQuery(document).ready(function() {
    $('#time_input').datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        stepping: 1
    });
});