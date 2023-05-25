$(document).ready(function() {

    $('.projectSelect').on('change', function () {
        let errorContainer = $('.projectSelectError');

        errorContainer.hide()
        let url = $('#managerPermissions').data('permissions')
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "GET",
            url: url,
            data: {
                "checkingProjectId": $(this).val(),
                'permission': $(this).data('permission')
            },
            success: () => {
                $(this).closest('form').find('button[type="submit"]').prop('disabled', false)
            },
            error: (response) => {
                errorContainer.text(response.responseJSON.permission_error)
                errorContainer.show()
                $(this).closest('form').find('button[type="submit"]').prop('disabled', true)

            }
        });
    })
})
