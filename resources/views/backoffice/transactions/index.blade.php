@extends('backoffice.layouts.backoffice')
@section('title', t('operations'))
@section('content')
    <div class="row mb-4 pb-3">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section"> {{ t('operations') }} </h2>
            <div class="row">
                <div class="col-lg-5 d-flex justify-content-between">
                    <div class="balance mb-4">
                        {{ t('backoffice_profile_page_header_body') }}
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>

    @include('backoffice.partials.transactions.index', ['client' => true, 'showProviderTypes' => true, 'showReport' => true])
    <div class="overlay"></div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            let hash = window.location.hash;
            let reportSetInterval;
            let report;
            if (hash) {
                let element = $('#' + hash.slice(1));
                $('.transaction-history-tab').each(function () {
                    if ($(this).hasClass('tab-active')) {
                        $(this).removeClass('tab-active').addClass('tab-inactive');
                    }
                });
                element.parent().addClass('tab-active').removeClass('tab-inactive');
                element.click();
            }
            $('body').on('click', '.transaction-history-tab', function () {
                window.location.hash = $(this).children(":first").attr('id');
                let currentUrl = window.location.href;
                let url = new URL(currentUrl);
                url.searchParams.set("page", "1");
                window.location = url.href;
            });

            $('.history-list-report').click(function (e) {
                e.preventDefault();
                var paymentFormId = $(this).data('payment-form-id');
                var operation = $('li.tab-active > a').attr('data-id');

                if (paymentFormId) {
                    $(this).before(`<input type="hidden" name="payment_form_id" value="${paymentFormId}">
                                <input type="hidden" name="operation" value="${operation}">`)
                    $(this).closest('form').submit();
                    $('input[name="payment_form_id"]').remove();
                    $('input[name="operation"]').remove();
                    return false;
                }

                $('.overlay').show();
                var data = {};

                data.operation = operation;
                data._token = '{{ csrf_token() }}';
                var form = $(this).closest('form');
                data.profile_id = form.find('[name="profile_id"]').val();
                data.substatus = form.find('[name="substatus"]').val();
                data.number = form.find('[name="number"]').val();
                data.from = form.find('[name="from"]').val();
                data.to = form.find('[name="to"]').val();
                data.transaction_type = form.find('[name="transaction_type"]').val();
                var params = new window.URLSearchParams(window.location.search);
                data.page = params.get('page') ?? 1;

                $.ajax({
                    url: '{{ route('backoffice.download.operation.report.pdf') }}',
                    type:'post',
                    data: data,
                    success:function (data) {
                        let reportRequestId = data['reportRequestId']
                        report = data['report']
                        checkReport(reportRequestId)
                    }
                })
                function checkReport(reportRequestId) {
                    reportSetInterval = setInterval(function (){
                        $.ajax({
                            url: '{{ route('backoffice.report.check.status') }}',
                            type:'post',
                            xhrFields: {
                                responseType: 'blob'
                            },
                            data: {'_token': '{{ csrf_token() }}', reportRequestId },
                            success:function (data) {
                                if(!data.isPending){
                                    clearInterval(reportSetInterval)
                                    const blob = new Blob([data]);
                                    const link = document.createElement('a');
                                    link.href = window.URL.createObjectURL(blob);
                                    link.download = report;
                                    link.click();
                                    link.remove();
                                    $('.overlay').hide();
                                }
                            }
                        })
                    }, 5000)
                }


            });
        });
    </script>
@endsection
