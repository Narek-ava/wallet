@extends('cabinet.layouts.cabinet')
@section('title', t('title_balance_page'))

@section('content')


    <div class="row mb-5 pb-5">
        <div class="col-md-12">
            <h2 class="mb-3 mt-2 large-heading-section page-title">{{ t('title_balance_page') }}</h2>
            <div class="row">
                <div class="col-lg-5 d-block d-md-flex">
                    <div class="balance mr-4 mt-2">
                        <div class="value">
                            $ 0.00
                        </div>

                        <button
                            class="btn btn-lg btn-primary themeBtnGray register-buttons mb-4 mb-md-0"
                            type="submit">{{ t('title_deposit_page') }}
                        </button>

                    </div>

                    <div class="balance mr-4 mt-2">
                        <div class="value-red">
                            $ 0.00
                        </div>

                        <button
                            class="btn btn-lg btn-primary themeBtnGray register-buttons mb-4 mb-md-0"
                            type="submit">{{ t('ui_cabinet_menu_withdrawal') }}
                        </button>

                    </div>


                    <div class="balance mr-4 mt-2">
                        <div class="value-red">
                            $ 0.00
                        </div>

                        <button
                            class="btn btn-lg btn-primary themeBtnGray register-buttons mb-4 mb-md-0"
                            type="submit">{{ t('compliance_rates_percents_export') }}
                        </button>

                    </div>


                </div>
                <div class="col-lg-7">

                    <div class="compliance common-shadow-theme">
                        <div class="info-label">
                            <i class="fa fa-exclamation" aria-hidden="true"></i>
                        </div>
                        <div class="col"><h2 class="mb-3">{{ t('ui_menu_compliance') }}</h2></div>

                        <div class="row m-0">

                            <div class="col-lg-9">

                                <p class="font-weight-bold">{{ t('ui_use_deposit') }}</p>

                            </div>

                            <div class="col-lg-3">
                                <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4"
                                        type="submit">{{ t('ui_2fa_verify_button') }}
                                </button>
                            </div>


                        </div>


                    </div>
                </div>


            </div>


        </div>

    </div>


    <div class="row mt-5 mb-5">
        <div class="col-md-12">

            <div class="row ">


                <div class="common-shadow-theme btc mb-4">
                    <div class="label">
                        <img src="/cratos.theme/images/btc.png" alt="">
                    </div>
                    <div class="d-block">
                        <h3>BTC 0.00000000</h3>
                        <div class="price-1"> $ 0.00</div>
                        <div class="price-2"> € 0.00</div>
                    </div>

                </div>


                <div class="common-shadow-theme btc mb-4">
                    <div class="label">
                        <img src="/cratos.theme/images/ltc.png" alt="">
                    </div>
                    <div class="d-block">
                        <h3>BTC 0.00000000</h3>
                        <div class="price-1"> $ 0.00</div>
                        <div class="price-2"> € 0.00</div>
                    </div>

                </div>


                <div class="common-shadow-theme btc mb-4">
                    <div class="label">
                        <img src="/cratos.theme/images/xrp.png" alt="">
                    </div>
                    <div class="d-block">
                        <h3>BTC 0.00000000</h3>
                        <div class="price-1"> $ 0.00</div>
                        <div class="price-2"> € 0.00</div>
                    </div>

                </div>


                <div class="common-shadow-theme btc mb-4">
                    <div class="label">
                        <img src="/cratos.theme/images/eth.png" alt="">
                    </div>
                    <div class="d-block">
                        <h3>BTC 0.00000000</h3>
                        <div class="price-1"> $ 0.00</div>
                        <div class="price-2"> € 0.00</div>
                    </div>

                </div>


                <div class="common-shadow-theme btc mb-4">
                    <div class="label">
                        <img src="/cratos.theme/images/bch.png" alt="">
                    </div>
                    <div class="d-block">
                        <h3>BTC 0.00000000</h3>
                        <div class="price-1"> $ 0.00</div>
                        <div class="price-2"> € 0.00</div>
                    </div>

                </div>


            </div>


        </div>

    </div>


    <div class="row mt-5 pt-5 pb-5">
        <div class="col-md-12">

            <div class="row ">

                <div class="col-md-12">
                    <h1 class="large-heading-section mb-5">{{ t('ui_history_transaction') }}</h1>
                    <p class="mb-5">{{ t('ui_non_transaction') }}</p>


                    <div class="common-shadow-theme history-list d-block mb-5">

                        <div class="d-block table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th scope="col">{{ t('transaction_history_table_heading_number') }}</th>
                                    <th scope="col">{{ t('transaction_history_table_heading_date_time') }}</th>
                                    <th scope="col">{{ t('transaction_history_table_heading_amount') }}</th>
                                    <th scope="col">{{ t('transaction_history_table_heading_type') }}</th>
                                    <th scope="col">{{ t('transaction_history_table_heading_status') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>48</td>
                                    <td>10.06.2019 <span>18:43</span></td>
                                    <td>0.00000000 ETH</td>
                                    <td>{{ t('ui_cabinet_menu_withdrawal') }} 0 USD to</td>
                                    <td class="orange">{{ t('ui_waiting') }}</td>
                                </tr>
                                <tr>
                                    <td>48</td>
                                    <td>10.06.2019 18:43</td>
                                    <td>0.00000000 ETH</td>
                                    <td>{{ t('ui_cabinet_menu_withdrawal') }} 0 USD to</td>
                                    <td class="orange">{{ t('ui_waiting') }}</td>
                                </tr>
                                <tr>
                                    <td>48</td>
                                    <td>10.06.2019 18:43</td>
                                    <td>0.00000000 ETH</td>
                                    <td>{{ t('ui_cabinet_menu_withdrawal') }} 0 USD to</td>
                                    <td class="orange">{{ t('ui_waiting') }}</td>
                                </tr>

                                </tbody>
                            </table>


                        </div>


                    </div>

                    <ul class="pagination pt-4">
                        <li class="page-item"><a class="page-link" href="#">{{ t('wire_transfer_previous') }}</a></li>
                        <li class="page-item pl-1">page</li>
                        <li class="page-item strong"><a class="page-link" href="#">1</a></li>
                        <li class="page-item strong">of</li>
                        <li class="page-item strong"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">{{ t('wire_transfer_next') }}</a></li>
                    </ul>

                </div>


            </div>


        </div>

    </div>


@endsection
