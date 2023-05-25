$(document).ready(function(){
    let body = $('body');
    const inputPlusData = {
        beneficialOwner : {
            divClass : 'beneficial-owner',
            inputId : 'beneficial_owner_',
            inputName : 'beneficial_owners[]',
            minBtnClass : 'minBeneficialOwnerBtn',
            plusBtnClass : 'plusBeneficialOwnerBtn',
            lastInput : 'beneficial-owners',
        },
        ceo : {
            divClass : 'ceo',
            inputId : 'ceo_',
            inputName : 'ceos[]',
            minBtnClass : 'minCeoBtn',
            plusBtnClass : 'plusCeoBtn',
            lastInput : 'ceos',
        },
        shareholder : {
            divClass : 'shareholder',
            inputId : 'shareholder_',
            inputName : 'shareholders[]',
            minBtnClass : 'minShareholderBtn',
            plusBtnClass : 'plusShareholderBtn',
            lastInput : 'shareholders',
        },
    };
    const inputMinData = {
        beneficialOwner : {
            divClass : 'beneficial-owner',
            inputId : 'beneficial_owner_',
            inputName : 'beneficial_owners[]',
            minBtnClass : 'minBeneficialOwnerBtn',
            plusBtnClass : 'plusBeneficialOwnerBtn',
            lastInput : 'beneficial-owners',
        },
        ceo : {
            divClass : 'ceo',
            inputId : 'ceo_',
            inputName : 'ceos[]',
            minBtnClass : 'minCeoBtn',
            plusBtnClass : 'plusCeoBtn',
            lastInput : 'ceos',
        },
        shareholder : {
            divClass : 'shareholder',
            inputId : 'shareholder_',
            inputName : 'shareholders[]',
            minBtnClass : 'minShareholderBtn',
            plusBtnClass : 'plusShareholderBtn',
            lastInput : 'shareholders',
        },
    };
    body.on( 'click', '.plusBeneficialOwnerBtn, .plusCeoBtn, .plusShareholderBtn', function() {
        let number = parseInt($(this).data('number')) + 1;
        let inputKey;
        switch (true){
            case $(this).hasClass('plusBeneficialOwnerBtn'):
                inputKey = 'beneficialOwner';
                break;
            case $(this).hasClass('plusCeoBtn'):
                inputKey = 'ceo';
                break;
            case $(this).hasClass('plusShareholderBtn'):
                inputKey = 'shareholder';
                break;
            default:
                return false;
        }

        let divClass = inputPlusData[inputKey]['divClass'];
        let inputId =  inputPlusData[inputKey]['inputId'];
        let inputName =  inputPlusData[inputKey]['inputName'];
        let minBtnClass =  inputPlusData[inputKey]['minBtnClass'];
        let plusBtnClass =  inputPlusData[inputKey]['plusBtnClass'];

        let newInput = '<div class="form-group col-md-3 ' + divClass + '">' +
            '<input autocomplete="off" class="form-control" type="text" data-number="' + number +' " required name="' + inputName + '" id="' + inputId + number + '">' +
            '<button type="button" class="' + minBtnClass + ' disabled_el" data-number="' + number +'">' +
                '<img src="/cratos.theme/images/minus.png" width="15" height="15" alt="">' +
            '</button>' +
            '<button type="button" class="' + plusBtnClass + ' disabled_el" data-number="' + number + '">\n' +
            '                                                <img src="/cratos.theme/images/plus.png" width="30" height="30" alt="">\n' +
            '                                            </button>' +
            '</div>';
        $(this).remove();
        let lastInput = $('.' + inputPlusData[inputKey]['lastInput']).find("input:last");
        console.log(inputKey)
        let parent = lastInput.parent();
        parent.after(newInput);
    });

    body.on( 'click', '.minBeneficialOwnerBtn, .minCeoBtn, .minShareholderBtn', function() {
        let number = $(this).data('number');
        let inputKey;
        switch (true){
            case $(this).hasClass('minBeneficialOwnerBtn'):
                inputKey = 'beneficialOwner';
                break;
            case $(this).hasClass('minCeoBtn'):
                inputKey = 'ceo';
                break;
            case $(this).hasClass('minShareholderBtn'):
                inputKey = 'shareholder';
                break;
            default:
                return false;
        }
        let inputId = inputMinData[inputKey]['inputId'] + number;

        $('#' + inputId).parent().remove();
        let plusBtnClass = inputMinData[inputKey]['plusBtnClass'];
        let plusBtn =  $('.' + plusBtnClass);

        plusBtn.remove();
        let inputClass = $('.' +inputMinData[inputKey]['lastInput'])
        let lastInput = inputClass.find("input:last")
        let plus = '<button type="button" class="' + plusBtnClass + ' disabled_el" data-number="' + lastInput.data('number') + '">' +
            '                                                <img src="/cratos.theme/images/plus.png" width="30" height="30" alt="">' +
            '                                            </button>';
        lastInput.parent().append(plus)
    });

})

