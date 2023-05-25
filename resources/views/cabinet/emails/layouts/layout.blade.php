<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>:: Welcome to <?= $projectName ?? config('app.name') ?> ::</title>
    <style>
        h1 {
            font-size: 22px !important;
            font-weight: 700 !important;
            color: black;
            text-align: center !important;
        }
    </style>
</head>
<body style="background-color: #f9f9f9; font-family: Arial, Helvetica, sans-serif">
<center>
    <table class="ce__container" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td></td>
            <td width="550" align="center">
                <br />
                <table class="ce__logo" cellpadding="" cellspacing="0" border="0">
                    <tr>
                        <td></td>
                        <td width="100">
                                    <img style="width: 98px; height: 98px; max-width: 98px" src="{{  $logoPng ?? config('app.url') . '/cratos.theme/images/emaillogo.png' }}"
                                         class="img-fluid projectLogo" alt="">
{{--                            <img src="<?= $logoPng ?? config('app.url') . '/cratos.theme/images/emaillogo.png' ?>" width="98" height="20"--}}
{{--                                style="width: 98px; height: 20px; max-width: 98px"--}}
{{--                            />--}}
                        </td>
                        <td></td>
                    </tr>
                </table>
                <br />
                <table cellpadding="0" cellspacing="0" style="background-color: white; border: solid 1px #f8f8f8; border-radius: 28px;">
                    <tbody>
                    <tr>
                        <td>
                            <table cellpadding="0" cellspacing="0" style="border: solid 1px #f8f8f8; border-radius: 27px;">
                                <tbody>
                                <tr>
                                    <td>
                                        <table cellpadding="0" cellspacing="0" style="border: solid 1px #f8f8f8; border-radius: 26px;">
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <table cellpadding="0" cellspacing="0" style="border: solid 1px #f8f8f8; border-radius: 25px;">
                                                        <tbody>
                                                        <tr>
                                                            <td>
                                                                <table cellpadding="0" cellspacing="0" style="border: solid 1px #f7f7f7; border-radius: 24px;">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td>
                                                                            <table cellpadding="0" cellspacing="0" style="border: solid 1px #f6f6f6; border-radius: 23px;">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td>
                                                                                        <table cellpadding="0" cellspacing="0" style="border: solid 1px #f5f5f5; border-radius: 22px;">
                                                                                            <tbody>
                                                                                            <tr>
                                                                                                <td>
                                                                                                    <table cellpadding="0" cellspacing="0" style="border: solid 1px #f4f4f4; border-radius: 21px;">
                                                                                                        <tbody>
                                                                                                        <tr>
                                                                                                            <td>
                                                                                                                <table cellpadding="0" cellspacing="0" style="border: solid 1px #f3f3f3; border-radius: 20px;">
                                                                                                                    <tbody>
                                                                                                                    <tr>
                                                                                                                        <td>
                                                                                                                            <table cellpadding="0" cellspacing="0" style="border: solid 1px #f2f2f2; border-radius: 19px;">
                                                                                                                                <tbody>
                                                                                                                                <tr>
                                                                                                                                    <td>
                                                                                                                                        <table cellpadding="0" cellspacing="0" style="border: solid 1px #f1f1f1; border-radius: 18px;">
                                                                                                                                            <tbody>
                                                                                                                                            <tr>
                                                                                                                                                <td>
                                                                                                                                                    <table cellpadding="0" cellspacing="0" style="border: solid 1px #f0f0f0; border-radius: 17px;">
                                                                                                                                                        <tbody>
                                                                                                                                                        <tr>
                                                                                                                                                            <td>
                                                                                                                                                                <table class="ce__card" cellpadding="0" cellspacing="0" border="0">
                                                                                                                                                                    <tr>
                                                                                                                                                                        <td width="550">
                                                                                                                                                                            <table cellpadding="0" cellspacing="0" border="0">
                                                                                                                                                                                <tr>
                                                                                                                                                                                    <td></td>
                                                                                                                                                                                    <td width="550" align="center">

                                                                                                                                                                                        <table
                                                                                                                                                                                            class="ce__card-body"
                                                                                                                                                                                            cellspacing="0"
                                                                                                                                                                                            border="0"
                                                                                                                                                                                            style="font-size: 15px; color: #777777"
                                                                                                                                                                                        >
                                                                                                                                                                                            <tr>
                                                                                                                                                                                                <td></td>
                                                                                                                                                                                                <td width="375" align="center" style="padding-top:5px !important;">
                                                                                                                                                                                                    @yield('content')
                                                                                                                                                                                                </td>
                                                                                                                                                                                                <td></td>
                                                                                                                                                                                            </tr>
                                                                                                                                                                                        </table>
                                                                                                                                                                                        <br />
                                                                                                                                                                                        <table
                                                                                                                                                                                            class="ce__card-footer"
                                                                                                                                                                                            cellpadding="5"
                                                                                                                                                                                            cellspacing="0"
                                                                                                                                                                                            border="0"
                                                                                                                                                                                            style="border-top: solid 1px #ededed"
                                                                                                                                                                                        >
                                                                                                                                                                                            <tr>
                                                                                                                                                                                                <td width="550" align="center">
                                                                                                                                                                                                    <br />
                                                                                                                                                                                                    <table cellpadding="0" cellspacing="0" border="0" style="font-size: 11px; color: #b3b3b3">
                                                                                                                                                                                                        <tr>
                                                                                                                                                                                                            <td></td>
                                                                                                                                                                                                            <td width="370" align="center">
                                                                                                                                                                                                                {!! t( 'security_warning_text' ,[ 'link' => t('contact_to_support_link')]) !!}
                                                                                                                                                                                                                <br />
                                                                                                                                                                                                                <br />
                                                                                                                                                                                                            </td>
                                                                                                                                                                                                            <td></td>
                                                                                                                                                                                                        </tr>
                                                                                                                                                                                                    </table>
                                                                                                                                                                                                    <br />
                                                                                                                                                                                                </td>
                                                                                                                                                                                            </tr>
                                                                                                                                                                                        </table>
                                                                                                                                                                                    </td>
                                                                                                                                                                                    <td></td>
                                                                                                                                                                                </tr>
                                                                                                                                                                            </table>
                                                                                                                                                                        </td>
                                                                                                                                                                    </tr>
                                                                                                                                                                </table>
                                                                                                                                                            </td>
                                                                                                                                                        </tr>
                                                                                                                                                        </tbody>
                                                                                                                                                    </table>
                                                                                                                                                </td>
                                                                                                                                            </tr>
                                                                                                                                            </tbody>
                                                                                                                                        </table>
                                                                                                                                    </td>
                                                                                                                                </tr>
                                                                                                                                </tbody>
                                                                                                                            </table>
                                                                                                                        </td>
                                                                                                                    </tr>
                                                                                                                    </tbody>
                                                                                                                </table>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                        </tbody>
                                                                                                    </table>
                                                                                                </td>
                                                                                            </tr>
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <table class="ce__footer" cellpadding="0" cellspacing="0" border="0" style="font-size: 11px; color: #b3b3b3">
                    <tr>
                        <td align="center">
                            <br />
                            <br />
                           {!! t('cratos_contact_address') !!}
                            <br />
                            <a href="{{ t('terms_and_conditions_link') }}" style="color: #7e7e7e; font-weight: 700; font-size: 11px"> {{ t('terms_and_conditions') }} </a>, <a href="{{ t('aml_policy_link') }}" style="color: #7e7e7e; font-weight: 700; font-size: 11px"> {{ t('aml_policy') }}</a>,
                            <a href="{{ t('privacy_policy_link') }}" style="color: #7e7e7e; font-weight: 700; font-size: 11px"> {{ t('privacy_policy') }} </a>.
                        </td>
                    </tr>
                </table>
            </td>
            <td></td>
        </tr>
    </table>
</center>
</body>
</html>
