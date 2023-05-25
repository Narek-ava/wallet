/**
 * @param apiUrl - 'https://test-api.sumsub.com' (sandbox)
 or 'https://api.sumsub.com' (production)
 * @param flowName - the flow name chosen at Step 1 (e.g. 'basic-kyc')
 * @param accessToken - access token that you generated on the backend in Step 2
 * @param applicantEmail - applicant email (not required)
 * @param applicantPhone - applicant phone, if available (not required)
 * @param customI18nMessages - customized locale messages for current session (not required)
 */
var applicantId = '';
var submit = false;
function launchWebSdk(apiUrl, flowName, accessToken, applicantEmail, applicantPhone, customI18nMessages, contextId) {
    let snsWebSdkInstance = snsWebSdk.init(accessToken,
        // token update callback, must return Promise
        // Access token expired
        // get a new one and pass it to the callback to re-initiate the WebSDK
        () => this.getNewAccessToken());

    // if(window.env != 'prod') {
    //     snsWebSdkInstance = snsWebSdkInstance.onTestEnv();
    // }
    snsWebSdkInstance = snsWebSdkInstance.withConf({
        lang: 'en',
        email: applicantEmail,
        phone: applicantPhone,
        i18n: customI18nMessages,
        onMessage: (type, payload) => {
            console.log('WebSDK onMessage', type, payload)
        },
        uiConf: {
            //customCss: "https://url.com/styles.css"
            // URL to css file in case you need change it dynamically from the code
            // the similar setting at Applicant flow will rewrite customCss
            // you may also use to pass string with plain styles `customCssStr:`
        },
    })
        .on('onError', (error) => {
            console.log('onError', payload)
        })
        .onMessage((type, payload) => {

            if(type == 'idCheck.onApplicantLoaded') {
                applicantId = payload.applicantId;
            }

            if(type == 'idCheck.onApplicantResubmitted' || type == 'idCheck.onApplicantSubmitted') {
                submit = true;
            }

            if(submit && (type == 'idCheck.applicantStatus' || type == 'idCheck.onVideoIdentModeratorJoined')) {
                submit = false;
                var dataPost = {
                    payload,
                    applicantId,
                    contextId,
                    type,
                };
                var url = API + 'compliance-request';
                if(localStorage.getItem('paymentFormAttemptId')) {
                    url = API + 'payment-form-compliance-request';
                    dataPost.paymentFormAttemptId = localStorage.getItem('paymentFormAttemptId');
                }
                $.ajax({
                    url: url,
                    type: 'post',
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: dataPost,
                }).done(function (data) {
                    console.log('done', data);
                }).fail(function (data) {
                    console.log('fail', data)
                });
            }

            console.log('onMessage', type, payload)
        })
        .build();

    // you are ready to go:
    // just launch the WebSDK by providing the container element for it
    snsWebSdkInstance.launch('#compliance-websdk-container')
}

function getNewAccessToken() {
    let newAccessToken = '...';
    return Promise.resolve(newAccessToken)// get a new token from your backend
}
