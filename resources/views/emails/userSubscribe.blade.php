<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
    <style>
        body {
            background-color: #f5f8fa;
            color: #74787E;
            height: 100%;
            hyphens: auto;
            line-height: 1.4;
            margin: 0;
            -moz-hyphens: auto;
            -ms-word-break: break-all;
            width: 100% !important;
            -webkit-hyphens: auto;
            -webkit-text-size-adjust: none;
            word-break: break-all;
            word-break: break-word;
        }
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 600px% !important;
            }

            .footer {
                width: 600px% !important;
            }
            .wrapper{
                width: 600px !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
        p, ul, a{
            font-family: Avenir,Helvetica,sans-serif;
            box-sizing: border-box;
            color: #74787e;
            font-size: 16px;
            line-height: 1.5em;
            margin-top: 0;
            text-align: left;
        }
        h2{
            font-family: Avenir,Helvetica,sans-serif;
            box-sizing: border-box;
            color: #2f3133;
            font-size: 19px;
            font-weight: bold;
            margin-top: 0;
            text-align: left;
        }
    </style>

    <table class="wrapper" style="margin: auto;" width="50%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="header"  style="background-color: #555;color: #bbbfc3;font-size: 19px;font-weight: bold;text-align:center;padding: 25px 0;">
                            <a href="{{ url('/') }}" style="font-family: Avenir,Helvetica,sans-serif;box-sizing: border-box;color: #bbbfc3;font-size: 19px;font-weight: bold;text-decoration: none;">
                                <img src="http://app.fusedtools.com/assets/images/logo.png"/>
                            </a>
                        </td>
                    </tr>
                    <!-- Email Body -->
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; padding: 35px;border-bottom: 1px solid #edeff2; border-top: 1px solid #edeff2">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" >
                                <!-- Body content -->
                                <tr>
                                    <td class="content-cell">
                                        <h2>Hello {{ $name }},</h2>
                                        <p>We are glad to inform you that your subscription has been successfully processed! </p>
                                        @php /* <p>You are now subscribed to plan. <strong>{{ $product->name }} - {{ $product->charge_freq }}ly</strong></p> */ @endphp
                                        <p>You are now subscribed to plan. <strong>{{ $product->label }} - {{ ucfirst($product->billing_period) }}</strong></p>
                                        <p>Features: </p>
                                        <ul> 
                                    
                                        @php /*
                                            <li>Scripts: <strong>{{$product->monthly_task_limit}}</strong> Task Allowance / month</li>
                                            <li>Docs: <strong>{{$product->monthly_doc_limit}}</strong> Docs / month</li>
                                            <li>CSV Import: <strong>{{$product->daily_record_limit}}</strong> Records / day</li> */ @endphp
                                            <li>Token: <strong>{{$product->monthly_token_amount}}</strong> token / month</li>
                                            <li>Infusionsoft Order As Xero Invoice</li>
                                            <li>Xero Invoice Creator</li>
                                            <li>All Geo Tools</li>
                                            @if(strtoupper($product->label) !== 'BASIC')
                                                <li><strong>Xero Invoice Sync</strong></li>
                                            @endif
                                        </ul>
                                        <br/>
                                        <p>Visit the <a href='#'>billing</a> page for more details.</p>
                                        <br/>
                                        <p>
                                            Thanks,
                                            <br>
                                            FusedTools Team
                                        </p>
                                        <br/>
                                        <p style="font-size: 10px; font-style: italic;">This is an automated response. Please do not reply.</p>
                                        <p style="font-size: 10px; font-style: italic;">You can visit our <a href="{{ url('support') }}">support page</a> for your concerns.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #aeaeae;font-size: 12px;text-align: center;padding: 20px 0;background-color: #555;">
                            <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" >
                                <tr >
                                    <td class="content-cell" align="center">
                                        <p style="font-family: Avenir,Helvetica,sans-serif;box-sizing: border-box;line-height: 1.5em;margin-top: 0;color: #f97d25;font-size: 12px;text-align: center;">
                                            &copy; 2018 Fused Software. All rights reserved. 
                                            <a style="font-size: 12px; color: #f97d25" href="#">Terms & Privacy Policy</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
