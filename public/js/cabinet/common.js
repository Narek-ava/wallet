$.urlParam = function(name){
	try {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
	    return results[1] || 0;
    } catch (error) {
        return undefined;
    }
}

function initSelectCountry() {
    $('select[name="country"],select[name="u_country"]').each(function() {
        var options = {
            containerCssClass : 'country-select2',
            templateSelection: function (data) {
                if (!data.id) {
                    return $('<span>' + data.text + '</span>');
                }

                var $result = $(
                    '<span><img src="/cratos.theme/images/flag/' + data.id + '.png" class="img-flag">&nbsp' + data.text + '</span>'
                );
                return $result;
            },
            templateResult: function (data) {
                if (!data.id) {
                    return $('<span>' + data.text + '</span>');
                }

                var $result = $(
                    '<span><img src="/cratos.theme/images/flag/' + data.id + '.png" class="img-flag">&nbsp' + data.text + '</span>'
                );

                return $result;
            },
        };
        if ($(this).closest(".modal").length > 0) {
            var selectContainer = $("<div></div>");
            $(this).after(selectContainer);
            options.dropdownParent = selectContainer;
        }

        $(this).select2(options);
    });
}

function initSelectCitizenship() {
    $('select[name="citizenship"]').each(function() {
        var options = {
            containerCssClass : 'citizenship-select2',
            templateSelection: function (data) {
                if (!data.id) {
                    return $('<span>' + data.text + '</span>');
                }

                var $result = $(
                    '<span><img src="/cratos.theme/images/flag/' + data.id + '.png" class="img-flag">&nbsp' + data.text + '</span>'
                );
                return $result;
            },
            templateResult: function (data) {
                if (!data.id) {
                    return $('<span>' + data.text + '</span>');
                }

                var $result = $(
                    '<span><img src="/cratos.theme/images/flag/' + data.id + '.png" class="img-flag">&nbsp' + data.text + '</span>'
                );

                return $result;
            },
        };
        if ($(this).closest(".modal").length > 0) {
            var selectContainer = $("<div></div>");
            $(this).after(selectContainer);
            options.dropdownParent = selectContainer;
        }

        $(this).select2(options);
    });
}

function initSidebarToggler() {
    $(".navbar-toggler").on("click", function() {
        var $sidebar = $("#sidebar");
        if ($sidebar.hasClass("open")) {
            $sidebar.removeClass("open");
            $("html").removeClass("overflow-hidden");
        } else {
            $sidebar.addClass("open");
            $("html").addClass("overflow-hidden");
        }
    });
}

function initMobileScrollingFixesForDatepicker() {
    // it's need for do not hide bootstrap datepicker, when scrolling on mobile devices
    // another solution - remove touchstart event from bootstrap datepicker library
    $(document).on("touchstart", function(e) {
        e.stopImmediatePropagation()
    })
}

function initPositionMenuItemsFixesForMobile() {
    $(".notifications-count").closest(".nav-item").addClass("pl-4 pl-lg-0");
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

//copy text of input by clicking the icon
function copyText(btn) {
    var id = btn.id;
    var copyText = document.getElementById('text' + id);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    setBtnSuccessfullyCopied(btn);
    copyText.blur();
}

$(function () {
    initPositionMenuItemsFixesForMobile();
    initSelectCountry();
    initSelectCitizenship();
    initSidebarToggler();
    initMobileScrollingFixesForDatepicker();
});


async function getClientBlockChainFee(currency) {
    const result = await fetch(API + 'get-blockchain-fee/' + currency);
    return await result.json();
}

function calculateCommission(amount, percent_commission, fixed_commission, min_commission, max_commission) {
    amount = parseFloat(amount);
    percent_commission = percent_commission ? parseFloat(percent_commission) : 0;
    fixed_commission = fixed_commission ? parseFloat(fixed_commission) : 0;
    min_commission = min_commission ? parseFloat(min_commission) : 0;
    max_commission = max_commission ? parseFloat(max_commission) : 0;

    let result = parseFloat(fixed_commission) + (amount * percent_commission / 100);
    if (min_commission && result < min_commission) {
        return min_commission;
    }
    if (max_commission && result > max_commission) {
        return max_commission;
    }
    return result;

}
