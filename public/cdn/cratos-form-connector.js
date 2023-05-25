var formId = document.currentScript.getAttribute('formId');
var domain = document.currentScript.getAttribute('domain');
var iframeUrl = domain + '/payment/form/' + formId;
var iframe = document.createElement('iframe');
iframe.setAttribute("src", iframeUrl);
iframe.setAttribute("width", '100%');

const paymentForms = document.getElementsByClassName('cratos-form');

if (paymentForms.length) {
    for (let paymentForm of paymentForms) {
        paymentForm.style.minHeight = '850px';
        paymentForm.style.minWidth = '400px';
    }
}

iframe.setAttribute("height", '850px');
iframe.setAttribute("scrolling", 'on');
iframe.setAttribute("style", 'border:none');
document.getElementById('cratos-form' + formId).appendChild(iframe);
