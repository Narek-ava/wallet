@extends('cabinet.layouts.cabinet')
@section('title', t('title_exchange_page'))

@section('content')


    <div class="exchange 11">
        <div class="top_page flex jcsb">
            <div class="left">
                <h1>Exchange</h1>
                <p>Exchange applications are processed within 24 hours.</p>
            </div>
            <div class="right">
                <div class="confirmation confirmation_ok flex aife">
                    <div class="left">
                        <strong>Confirmation</strong>
                        <p>Your request to exchange of 20,4 EUR to BTC 0,00248618 was successfully added to the system. Wait for it to be processed.</p>
                    </div>
                    <a class="btn btn-active 2_7" onclick="print_str(&#39;27&#39;,&#39;content&#39;);">Verify</a>
                </div>
            </div>
        </div>
        <div class="change after flex">
            <div class="inner">
                <div class="top">
                    <strong>I want to exchange</strong>

                    <label class="ic_ ic_select">
                        <select name="from" id="from" onchange="set_title()"></select></label>
                </div>
                <div class="bot">
                    <label for="value">Exchange amount</label>
                    <div class="pl_m flex v_center">
                        <input type="number" step="0.1" min="0" id="val_from" value="0.0" oninput="/*num(this);*/ set_GLOBAL_PROFIT_EXCHANGE_PROC(); calc_new_to();">
                        <span id="cur_from"></span>
                    </div>
                </div>

                <p class="min_size" id="min_size1" style="display: none;">Minimum order size <span id="min_val_from"></span></p>
                <div style="display: none;" class="error_min_size_on">You must increase the amount of exchange</div>
                <p class="min_size" id="accuracy1" style="display: none;">Maximum  precision  <span id="acc_val_from"></span> decimal(s)</p>
            </div>
            <div class="inner">
                <div class="top">
                    <strong>Receive</strong>

                    <label class="ic_ ic_select">
                        <select name="to" id="to" onchange="set_title()"></select></label>
                </div>
                <div class="bot">
                    <label>Exchange amount</label>
                    <div class="pl_m flex v_center">
                        <input type="text" id="val_to" value="0.0" readonly="" class="req">
                        <span id="cur_to"></span>
                    </div>
                </div>
                <p class="min_size" id="min_size2" style="display: none;">Minimum order size <span id="min_val_to"></span></p>
                <p class="min_size" id="accuracy2" style="display: none;">Maximum  precision  <span id="acc_val_to"></span> decimal(s)</p>
            </div>
            <div class="result_exchange grid grid2x2">
                <div>
                    <p>You exchange</p>
                    <strong>
                        <span id="firstAm"></span>
                        <span id="first_Am"></span>
                    </strong>
                </div>
                <div>
                    <p>You receive</p>
                    <strong>
                        <span id="secondAm"></span>
                        <span id="second_Am"></span>
                    </strong>
                </div>
                <div style="display: none;" class="block_fee">
                    <p>Fee</p>
                    <strong id="fee_exchange_proc">0.00</strong>
                </div>
                <div>
                    <button class="btn btn-active" type="button" name="button" id="btn_exch" onclick="make_exch()">Confirm</button>
                </div>
            </div>
        </div>
        <div class="histo">
            <h2>History of exchanges</h2>
            <div class="list_transaction" id="list_transaction"><p>You have no exchages yet</p></div>
        </div>
        <div class=""></div>

        <div class="error_min_size_off" id="error_dec_div" style="top: -1500px;position: absolute;">
            <br>
            <p>*** The course is indicated for reference. During the bidding exchange price may vary</p>
            <p><b>There are restrictions to how price and volume can be entered on the order form.</b></p>
            <p>Trying to place an order with more decimal places than allowed will result in an error and the order will not be created.</p>
            <ul>
                <li><em>E.g. Maximum price precision for XBT/USD is 1 decimal. This means that you can place an order for 6500 USD or 6500.1 USD, but <strong>not</strong> 6500.01 USD.</em></li>
                <li><em>E.g. Maximum volume precision for XBT/USD is 8 decimals. This means that you can place an order for 1 BTC, or 0.1 XBT, or 0.00000001 BTC, but <strong>not</strong>&nbsp;0.000000001 BTC.</em></li>
            </ul>
            <p><b>For your convenience, in the "Exchange amount" field you can see the possible accuracy.</b></p>
            <p><b>In the field "Amount to getting" the result is also displayed with the appropriate accuracy.</b></p>
        </div>
        <div class="error_min_size_off" id="error_min_size_div" style="   top: -1500px;      position: absolute;  ">
            <p><b>What is the minimum order size?</b> </p>
            <p>If you are trading BTC, the volume of the order must be at least 0.002 BTC or larger.
                <br>
                If you are trading ETH, the volume of the order must be at least 0.02 ETH or larger. </p>
            <br>
            <p><em>If this rule is not met, the form field will be marked in red.</em></p>
        </div>
        <div class="error_list_dialog_21" style="display: none;">  <ul><li id="dial_err_1">You have insufficient funds. <br>Do you want to replenish your balance now?</li></ul> </div><div class="command_list_dialog_21" style="display: none;">  <ul><li id="com_dial_err_1">211</li></ul> </div><dialog class="alert_invest alert_dialog_1" style="display: none;">
            <h2></h2>
            <div class="buttons">
                <a class="send" onclick="dialog_function_yes(&#39;dialog_res&#39;,21)">Yes</a>
                <a class="cancel" onclick="hide_error_dialog()">Cancel</a>
            </div>
        </dialog>
        <!-- <div class="bgc 2" style="display: none;"></div> -->
        <div class="error_list_21" style="display: none;">  <ul><li id="err_1">Shares number to buy must be more null</li><li id="err_2">The number of free shares is less</li><li id="err_4">Invalid blockchain transaction
                    Try again later or call the Support</li><li id="err_5">Transaction completed successfully</li></ul> </div><dialog class="alert_1 messages mess_error">
            <h2></h2>
            <center><a class="cancel button_cancel" onclick="hide_error()">Close</a></center>
        </dialog>
        <div class="bgc" style="display: none;" onclick="hide_2f();"></div> </div>


@endsection
