$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#permissions').select2({
        placeholder: "Select permissions",
        val: false,
        width:'100%',
    });

    $('#selectPermissions').change(function (){
        let check = $(this).is(':checked');
        let checkboxes = $('.checkboxPermissions');
        checkboxes.each(function (index, item){
            $(item).attr('checked', check)
        });
        let text = check ? 'Unselect all' : 'Select all';
        $('#labelSelectText').text(text)
    })

    $('.groupPermissionsInput').change(function (){
        let group = $(this).data('group');
        let check = $(this).is(':checked');
        let checkboxes = $('.' + group + 'Permissions');
        checkboxes.each(function (index, item){
            $(item).attr('checked', check)
        });
        let text = check ? 'Unselect all' : 'Select all';
        $('.' + group + 'Label').text(text)
    })

});



