$(document).ready(function () {
    let generateTokenUrl = '/backoffice/api-clients/token';

    $('#regenerateTokenButton').click(function () {
        $.ajax({
            url: generateTokenUrl,
            type: 'get',
            success: (data) => {
                $('#inputToken').val(data.token);
            },
        })
    })

    $('#projectId').on('change', function () {
        let projectId = $(this).val();
        let url = $(this).data('url')

        window.location.href = url + '?project_id=' + projectId
    })
})


function copyText(btn) {
    console.log(btn)
    var id = btn.id;
    var copyText = document.getElementById(id + 'Input');
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
