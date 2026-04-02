<?php
/**
 * @var string $first_name
 * @var string $login
 * @var string $password
 * @var string $email
 * @var string $site_name
 * @var string $cabinet_url
 * @var string $home_url
 * @var string $mail_logo
 */

$greeting_name = !empty($first_name) ? $first_name : $email;
$mail_title = sprintf(__('Welcome, %s!', 'f-shop'), $greeting_name);
$mail_message = sprintf(__('You have successfully registered on the website "%s".', 'f-shop'), $site_name);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo esc_html(sprintf(__('Registration on the website "%s"', 'f-shop'), $site_name)); ?></title>

    <style type="text/css">
        img {
            max-width: 600px;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        a img {
            border: none;
        }

        table {
            border-collapse: collapse !important;
        }

        #outlook a {
            padding: 0;
        }

        .ReadMsgBody {
            width: 100%;
        }

        .ExternalClass {
            width: 100%;
        }

        .backgroundTable {
            margin: 0 auto;
            padding: 0;
            width: 100% !important;
        }

        table td {
            border-collapse: collapse;
        }

        .ExternalClass * {
            line-height: 115%;
        }

        .container-for-gmail-android {
            min-width: 600px;
        }

        * {
            font-family: Helvetica, Arial, sans-serif;
        }

        body {
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: none;
            width: 100% !important;
            margin: 0 !important;
            height: 100%;
            color: #676767;
        }

        td {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #777777;
            text-align: center;
            line-height: 21px;
        }

        a {
            color: #676767;
            text-decoration: none !important;
        }

        .header-lg,
        .header-sm {
            font-size: 32px;
            font-weight: 700;
            line-height: normal;
            padding: 35px 0 0;
            color: #4d4d4d;
        }

        .header-sm {
            padding: 10px 40px 0;
            font-size: 18px;
            line-height: 1.5;
            font-weight: 400;
        }

        .content-padding {
            padding: 20px 0 5px;
        }

        .free-text {
            width: 100% !important;
            padding: 10px 60px 0;
        }

        .button {
            padding: 30px 0;
        }

        .mini-container {
            width: 560px;
            padding: 10px 20px 10px;
        }

        .mini-block {
            border: 1px solid #e5e5e5;
            border-radius: 5px;
            background-color: #ffffff;
            padding: 0;
            text-align: left;
            width: 100%;
        }

        .title-dark {
            text-align: left;
            border-bottom: 1px solid #cccccc;
            color: #4d4d4d;
            font-weight: 700;
            padding: 18px 24px;
            font-size: 18px;
        }

        .detail-label,
        .detail-value {
            text-align: left;
            padding: 16px 24px;
            border-bottom: 1px solid #eeeeee;
            vertical-align: top;
            font-size: 16px;
            line-height: 1.5;
        }

        .detail-label {
            width: 180px;
            color: #4d4d4d;
            font-weight: 700;
        }

        .detail-value {
            color: #676767;
            word-break: break-word;
        }

        .detail-last td {
            border-bottom: none;
        }

        .force-width-gmail {
            min-width: 600px;
            height: 0 !important;
            line-height: 1px !important;
            font-size: 1px !important;
        }
    </style>

    <style type="text/css" media="screen">
        @import url(http://fonts.googleapis.com/css?family=Oxygen:400,700);
    </style>

    <style type="text/css" media="screen">
        @media screen {
            * {
                font-family: 'Oxygen', 'Helvetica Neue', 'Arial', 'sans-serif' !important;
            }
        }
    </style>

    <style type="text/css" media="only screen and (max-width: 480px)">
        @media only screen and (max-width: 480px) {
            table[class*="container-for-gmail-android"] {
                min-width: 290px !important;
                width: 100% !important;
            }

            img[class="force-width-gmail"] {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
            }

            table[class="w320"] {
                width: 320px !important;
            }

            td[class="header-lg"] {
                font-size: 24px !important;
                padding-bottom: 5px !important;
            }

            td[class="header-sm"] {
                font-size: 16px !important;
                padding: 8px 20px 0 !important;
            }

            td[class="content-padding"] {
                padding: 5px 0 !important;
            }

            td[class="button"] {
                padding: 5px 5px 30px !important;
            }

            td[class*="free-text"] {
                padding: 10px 18px 20px !important;
            }

            td[class="mini-container"] {
                padding: 0 15px 15px !important;
                display: block !important;
                width: 290px !important;
            }

            td[class="detail-label"],
            td[class="detail-value"] {
                display: block !important;
                width: auto !important;
                padding: 10px 16px !important;
            }
        }
    </style>
</head>

<body bgcolor="#f7f7f7">
<table align="center" cellpadding="0" cellspacing="0" class="container-for-gmail-android" width="100%">
    <tr>
        <td align="left" valign="top" width="100%"
            style="background:repeat-x url(http://s3.amazonaws.com/swu-filepicker/4E687TRe69Ld95IDWyEg_bg_top_02.jpg) #ffffff;">
            <center>
                <img src="http://s3.amazonaws.com/swu-filepicker/SBb2fQPrQ5ezxmqUTgCr_transparent.png" class="force-width-gmail" alt="">
                <table cellspacing="0" cellpadding="0" width="100%" bgcolor="#ffffff"
                    background="http://s3.amazonaws.com/swu-filepicker/4E687TRe69Ld95IDWyEg_bg_top_02.jpg"
                    style="background-color:transparent">
                    <tr>
                        <td width="100%" height="80" valign="top" style="text-align:center; vertical-align:middle;">
                            <center>
                                <table cellpadding="0" cellspacing="0" width="600" class="w320">
                                    <tr>
                                        <td style="text-align:center; vertical-align:middle; padding:10px 0;">
                                            <a href="<?php echo esc_url($home_url); ?>">
                                                <?php if (!empty($mail_logo)) { ?>
                                                    <img width="137" height="47"
                                                        src="<?php echo esc_url($mail_logo); ?>"
                                                        alt="<?php echo esc_attr($site_name); ?>"
                                                        style="height:50px; width:auto;">
                                                <?php } else { ?>
                                                    <span style="font-size:28px; font-weight:700; color:#4d4d4d;"><?php echo esc_html($site_name); ?></span>
                                                <?php } ?>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </center>
                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
    <tr>
        <td align="center" valign="top" width="100%" style="background-color:#f7f7f7;" class="content-padding">
            <center>
                <table cellspacing="0" cellpadding="0" width="600" class="w320">
                    <tr>
                        <td class="header-lg">
                            <?php echo esc_html($mail_title); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="header-sm">
                            <?php echo esc_html($mail_message); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="mini-container">
                            <table cellpadding="0" cellspacing="0" width="100%" class="mini-block" style="border-collapse:separate !important;">
                                <tr>
                                    <td class="title-dark" colspan="2">
                                        <?php esc_html_e('Registration details', 'f-shop'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="detail-label"><?php esc_html_e('Email', 'f-shop'); ?></td>
                                    <td class="detail-value"><?php echo esc_html($email); ?></td>
                                </tr>
                                <tr>
                                    <td class="detail-label"><?php esc_html_e('Login', 'f-shop'); ?></td>
                                    <td class="detail-value"><?php echo esc_html($login); ?></td>
                                </tr>
                                <tr>
                                    <td class="detail-label"><?php esc_html_e('Password', 'f-shop'); ?></td>
                                    <td class="detail-value"><?php echo esc_html($password); ?></td>
                                </tr>
                                <tr class="detail-last">
                                    <td class="detail-label"><?php esc_html_e('Personal account', 'f-shop'); ?></td>
                                    <td class="detail-value">
                                        <a href="<?php echo esc_url($cabinet_url); ?>"><?php echo esc_html($cabinet_url); ?></a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="button">
                            <div>
                                <!--[if mso]>
                                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                                             xmlns:w="urn:schemas-microsoft-com:office:word" href="<?php echo esc_url($cabinet_url); ?>"
                                             style="height:45px;v-text-anchor:middle;width:220px;" arcsize="15%"
                                             strokecolor="#ffffff" fillcolor="#ff6f6f">
                                    <w:anchorlock/>
                                    <center style="color:#ffffff;font-family:Helvetica, Arial, sans-serif;font-size:14px;font-weight:regular;">
                                        <?php echo esc_html(__('Go to account', 'f-shop')); ?>
                                    </center>
                                </v:roundrect>
                                <![endif]-->
                                <a href="<?php echo esc_url($cabinet_url); ?>"
                                   style="background-color:#ff6f6f;border-radius:5px;color:#ffffff;display:inline-block;font-family:'Cabin', Helvetica, Arial, sans-serif;font-size:14px;font-weight:regular;line-height:45px;text-align:center;text-decoration:none;width:220px;-webkit-text-size-adjust:none;mso-hide:all;">
                                    <?php esc_html_e('Go to account', 'f-shop'); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="free-text">
                            <?php echo esc_html(sprintf(__('If you did not register on the website "%s", please ignore this email.', 'f-shop'), $site_name)); ?>
                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
    <tr>
        <td align="center" valign="top" width="100%" style="background-color:#f7f7f7; height:100px;">
            <center>
                <table cellspacing="0" cellpadding="0" width="600" class="w320">
                    <tr>
                        <td style="padding:25px 0 25px">
                            <?php echo __('The online store works on', 'f-shop'); ?> <strong><a href="https://f-shop.top/" target="_blank">F-SHOP</a></strong>
                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
</table>
</body>
</html>
