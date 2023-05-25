<div class="modal fade modal-center" id="showScriptCryptoForm{{$paymentForm->id}}" role="dialog">
    <div class="modal-dialog modal-dialog-center">
        <h2></h2>
        <!-- Modal content-->
        <div class="modal-content" style="border:none;padding: 25px;width: 900px;border-radius: 30px;">
            <div class="modal-body d-flex align-items-center flex-column">
                <textarea id="paymentFormScript{{ $paymentForm->id }}" rows="5"  disabled="disabled" style="width: 100%; padding: 20px; overflow:hidden; border-radius: 10px; background-color: #f1f1f1">
                    <div id="cratos-form{{ $paymentForm->id }}" class="cratos-form"></div>
                    <script type="text/javascript" src="{{ $paymentForm->project->domainFullPath() . '/cdn/cratos-crypto-form-connector.js' }}" formId="{{ $paymentForm->id }}" domain="{{ $paymentForm->project->domainFullPath() }}"></script>
                </textarea>
                <p class="copy-successful mt-2 text-success" style="display: none">
                    {{ t('copied_script') }}
                </p>
                <button data-form-id="{{ $paymentForm->id }}" class="btn themeBtnWithoutHover width20 mt-4 copyScriptButton">
                    {{ t('copy') }}
                </button>
            </div>
        </div>
    </div>
</div>
