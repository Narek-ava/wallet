$(document).on({
    ajaxStop: function(){
        $("body").removeClass("loading");
    }
});

$(document).ready(function() {
    $('.loader').on('click', function () {
        $("body").addClass("loading");
    })
})


