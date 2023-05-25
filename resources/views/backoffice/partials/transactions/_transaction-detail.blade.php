<div class="modal fade" id="trxDetailsPopup" tabindex="-1" aria-labelledby="trxDetailsPopupLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 31px; padding: 17px">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel">{{ t('transaction_details') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="transactionDetail">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="inputEmail" class="font-weight-bold">{{ t('date') }}</label>
                            <input name="date" data-provide="datepicker" data-date-format="yyyy-mm-dd"
                                   class="form-control grey-rounded-border datepicker" disabled autocomplete="off" value="">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="inputEmail"
                                   class="font-weight-bold">{{ t('transaction_type') }}</label>
                            <input type="text" class="form-control grey-rounded-border  transaction-type"
                                   name="transaction_type" disabled>
                        </div>
                    </div>

                    <div class="col-md-3 exchange-rate" hidden>
                        <div class="form-group">
                            <label for="inputEmail"
                                   class="font-weight-bold">{{ t('exchange_rate') }}</label>
                            <input type="text" name="exchange_rate"
                                   class="form-control grey-rounded-border exchange-rate" disabled>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="inputEmail" class="font-weight-bold">{{ t('from_type') }}</label>
                            <input type="text" class="form-control grey-rounded-border  from-type"
                                   name="from_type" disabled>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="inputEmail" class="font-weight-bold">{{ t('account') }}</label>
                            <input type="text" class="form-control grey-rounded-border from-account"
                                   name="from_account" disabled>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="inputEmail" class="font-weight-bold">{{ t('to_type') }}</label>
                            <input type="text" class="form-control grey-rounded-border to-type"
                                   name="to_type" disabled>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="inputEmail" class="font-weight-bold">{{ t('account') }}</label>
                            <input type="text" class="form-control grey-rounded-border to-account" name="to_account" disabled>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="inputEmail" class="font-weight-bold">{{ t('from_currency') }}</label>
                            <input type="text" class="form-control grey-rounded-border from-currency" name="from_currency" disabled>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="inputEmail" class="font-weight-bold">{{ t('amount') }}</label>
                            <input type="number" class="form-control grey-rounded-border from-amount" value="" disabled name="currency_amount">
                        </div>
                    </div>
                    <div class="col-md-3 to-currency" hidden>
                        <div class="form-group">
                            <label for="inputEmail"
                                   class="font-weight-bold to_currency">{{ t('to_currency') }}</label>
                            <input type="text" class="form-control grey-rounded-border to-currency"
                                   name="to_currency" disabled>
                        </div>
                    </div>
                    <div class="col-md-3 cryptocurrency-amount" hidden>
                        <div class="form-group">
                            <label for="inputEmail" class="font-weight-bold">{{ t('amount') }}</label>
                            <input type="text" class="form-control grey-rounded-border to-amount" name="cryptocurrency_amount" disabled>
                        </div>
                    </div>
                    <div class="col-md-4 crypto-address" hidden>
                        <div class="form-group">
                            <label for="inputEmail" class="font-weight-bold">{{ t('to_address') }}</label>
                            <input type="text" class="form-control grey-rounded-border crypto-address" name="to_address" disabled>
                        </div>
                    </div>
                </div>

                <div class="row commission-name">
                    <div class="col-md-4"><h4 class="mt-3 mb-5">{{ t('commissions') }}</h4></div>
                </div>

                <div class="row commission-row">
                    <div class="col-md-3 commissions">
                        <div class="form-group">
                            <label for="inputEmail"
                                   class="font-weight-bold exchange-fee-percent">{{ t('from_fee') }}
                                %</label>
                            <input type="number" class="form-control grey-rounded-border commission exchange-fee-percent"
                                   name="exchange_fee_percent" disabled
                                   id="" value="">

                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="inputEmail"
                                   class="font-weight-bold exchange-fee">{{ t('from_fee') }}</label>
                            <input type="number" class="form-control grey-rounded-border commission exchange-fee"
                                   disabled
                                   name="exchange_fee"
                                   id="" value="">
                        </div>
                    </div>
                    <div class="col-md-3 to-fee">
                        <div class="form-group">
                            <label for="inputEmail"
                                   class="font-weight-bold to-fee-percent">{{ t('to_fee') }} %</label>
                            <input type="number" class="form-control grey-rounded-border commission to-fee-percent"
                                   name="to_fee_percent" disabled
                                   id="" value="">
                        </div>
                    </div>
                    <div class="col-md-3 to-fee">
                        <div class="form-group">
                            <label for="inputEmail" class="font-weight-bold ">{{ t('to_fee') }}</label>
                            <input type="number" class="form-control grey-rounded-border commission to-fee"
                                   disabled
                                   name="to_fee"
                                   id="" value="">

                        </div>
                    </div>

                </div>

                <div class="row commission-row">
                    <div class="col-md-3 from-fee-minimum">
                        <div class="form-group">
                            <label for="inputEmail"
                                   class="font-weight-bold from-fee-min">{{ t('from_fee_minimum') }}</label>
                            <input type="number" class="form-control grey-rounded-border commission exchange-fee-min"
                                   name="exchange_fee_min" disabled
                                   id="" value="">

                        </div>
                    </div>
                    <div class="col-md-3"></div>
                    <div class="col-md-3 to-fee">
                        <div class="form-group">
                            <label for="inputEmail"
                                   class="font-weight-bold  to-fee-min">{{ t('to_fee_minimum') }}</label>
                            <input type="number" class="form-control grey-rounded-border commission to-fee-min"
                                   name="to_fee_min" disabled
                                   id="" value="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

