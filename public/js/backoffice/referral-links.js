$(document).ready(function () {

    // set default dates
    var start = new Date();
    // set end date to max one year period:
    var end = new Date(new Date().setYear(start.getFullYear()+1));

    $('#activated_date').datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        weekStart: 1,
        startDate : start,
        endDate   : end
    // update "deactivated_date" defaults whenever "activated_date" changes
    }).on('changeDate', function(){
        // set the "deactivated_date" start to not be later than "activated_date" ends:
        $('#deactivated_date').datepicker('setStartDate', new Date($(this).val()));
    });

    $('#deactivated_date').datepicker({
        format: "yyyy-mm-dd",
        weekStart: 1,
        autoclose: true,
        startDate : start,
        endDate   : end
    // update "activated_date" defaults whenever "deactivated_date" changes
    }).on('changeDate', function(){
        // set the "activated_date" end to not be later than "deactivated_date" starts:
        $('#activated_date').datepicker('setEndDate', new Date($(this).val()));
    });

})


function copyText(btn) {

    var copyText = document.getElementById('inputToken');

    console.log(copyText, )

    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    setBtnSuccessfullyCopied(btn);
    copyText.blur();
}
function setBtnSuccessfullyCopied(btnEl) {
    $(btnEl).addClass("btn-successfully-copied");

    var $icon = $(btnEl).find("i.fa");
    $icon.removeClass("fa-copy").addClass("fa-check");

    setTimeout(function() {
        $(btnEl).removeClass("btn-successfully-copied");
        $icon.removeClass("fa-check").addClass("fa-copy");
    }, 2000);
}
