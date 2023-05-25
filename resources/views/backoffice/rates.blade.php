@extends('backoffice.layouts.backoffice')
@section('title', t('title_rates_and_limits_page'))

@section('content')
    <form method="POST" action="{{ $_action }}" class="form-signin">
        @csrf
        @method($_method)

        <div class="row mb-5 pb-5">
            <div class="col-md-8">
                <h2 class="mb-3 large-heading-section">Rates and Limits category</h2>

                <div class="error-text-list">
                    @foreach($errors->all() as $message)
                        <p class="error-text">{{ $message }}</p>
                    @endforeach
                </div>

                <input class="form-control" style="font-size: 32px;" type="text"
                       name="_title"
                       required
                       value="{{ $_title ?? null }}">

            </div>
        </div>
        @isset($_category)
            <div class="row">
                {{ t('ui_rates_created_label') }}: {{ C\time($_category->created_at) }}
            </div>
            <div class="row">
                {{ t('ui_rates_updated_label') }}: {{ C\time($_category->updated_at) }}
            </div>
        @endisset

        <div class="row mt-5 pt-5 pb-5">
            <div class="col-md-12">
                <div class="row">
                    <div class="d-block col-md-12">

                        <table class="table dt-responsive nowrap table-bo-rates" style="width:100%">
                            <thead>
                            <tr>
                                <th scope="col" width="35%">
                                    Rate / Limit
                                </th>
                                <th scope="col" width="20%">
                                    Unit / per
                                </th>
                                <th scope="col" width="15%">Level 1</th>
                                <th scope="col" width="15%">Level 2</th>
                                <th scope="col" width="15%">Level 3</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td colspan="5">
                                    <b>Account</b>
                                </td>
                            </tr>

                            @php
                                $key = 'application_processing_fee';
                            @endphp
                            <tr>
                                <td>{{ C\_rates($key) }}</td>
                                <td>once</td>
                                <td>{!! C\rates_input($key, $$key, 1) !!}</td>
                                <td>{!! C\rates_input($key, $$key, 2) !!}</td>
                                <td>{!! C\rates_input($key, $$key, 3) !!}</td>
                            </tr>

                            @php
                                $key = 'account_maintenance';
                            @endphp
                            <tr>
                                <td>{{ C\_rates($key) }}</td>
                                <td>month</td>
                                <td>{!! C\rates_input($key, $$key, 1) !!}</td>
                                <td>{!! C\rates_input($key, $$key, 2) !!}</td>
                                <td>{!! C\rates_input($key, $$key, 3) !!}</td>
                            </tr>

                            @php
                                $key = 'account_closure';
                            @endphp
                            <tr>
                                <td>{{ C\_rates($key) }}</td>
                                <td>once</td>
                                <td>{!! C\rates_input($key, $$key, 1) !!}</td>
                                <td>{!! C\rates_input($key, $$key, 2) !!}</td>
                                <td>{!! C\rates_input($key, $$key, 3) !!}</td>
                            </tr>


                            <tr>
                                <td colspan="5"><b>Transactions</b></td>
                            </tr>

                            @php
                                $key = 'all_transactions_month_limit';
                            @endphp
                            <tr>
                                <td><b>{{ C\_rates($key) }}</b></td>
                                <td>Limit (month)</td>
                                <td>{!! C\rates_input($key, $$key, 1) !!}</td>
                                <td>{!! C\rates_input($key, $$key, 2) !!}</td>
                                <td>{!! C\rates_input($key, $$key, 3) !!}</td>
                            </tr>




                            @foreach($_operation_types as $type)
                                <tr>
                                    <td rowspan="3"> {{ C\_rates($type) }} </td>
                                    <td>Limit (transaction), EUR</td>
                                    @php
                                        $key = $type . '_limit';
                                    @endphp
                                    <td>{!! C\rates_input($key, $$key, 1) !!}</td>
                                    <td>{!! C\rates_input($key, $$key, 2) !!}</td>
                                    <td>{!! C\rates_input($key, $$key, 3) !!}</td>
                                </tr>
                                <tr>
                                    <td>Rate (%)</td>
                                    @php
                                        $key = $type . '_rate';
                                    @endphp
                                    <td>{!! C\rates_input($key, $$key, 1) !!}</td>
                                    <td>{!! C\rates_input($key, $$key, 2) !!}</td>
                                    <td>{!! C\rates_input($key, $$key, 3) !!}</td>
                                </tr>
                                <tr>
                                    @php
                                        $key = $type . '_min';
                                    @endphp
                                    <td>min rate (EUR)</td>
                                    <td>{!! C\rates_input($key, $$key, 1) !!}</td>
                                    <td>{!! C\rates_input($key, $$key, 2) !!}</td>
                                    <td>{!! C\rates_input($key, $$key, 3) !!}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <button class="btn btn-lg btn-primary themeBtn mr-3 mt-3"
                    type="submit">
                Save
            </button>
        </div>

    </form>

@endsection



