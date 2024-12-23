<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><?= /** @var string $order_title */
		$order_title ?></title>

    <style type="text/css">
        /* Take care of image borders and formatting, client hacks */
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


        /* General styling */
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

        .pull-left {
            text-align: left;
        }

        .pull-right {
            text-align: right;
        }

        .header-lg,
        .header-md,
        .header-sm {
            font-size: 32px;
            font-weight: 700;
            line-height: normal;
            padding: 35px 0 0;
            color: #4d4d4d;
        }

        .header-md {
            font-size: 24px;
        }

        .header-sm {
            padding: 5px 0;
            font-size: 18px;
            line-height: 1.3;
        }

        .content-padding {
            padding: 20px 0 5px;
        }

        .mobile-header-padding-right {
            width: 290px;
            text-align: right;
            padding-left: 10px;
        }

        .mobile-header-padding-left {
            width: 290px;
            text-align: left;
            padding-left: 10px;
        }

        .free-text {
            width: 100% !important;
            padding: 10px 60px 0px;
        }

        .button {
            padding: 30px 0;
        }

        .mini-block {
            border: 1px solid #e5e5e5;
            border-radius: 5px;
            background-color: #ffffff;
            padding: 12px 15px 15px;
            text-align: left;
            width: 253px;
        }

        .mini-container-left {
            width: 278px;
            padding: 10px 0 10px 15px;
        }

        .mini-container-right {
            width: 278px;
            padding: 10px 14px 10px 15px;
        }

        .product {
            text-align: left;
            vertical-align: top;
            width: 175px;
        }

        .total-space {
            padding-bottom: 8px;
            display: inline-block;
        }

        .item-table {
            padding: 50px 20px;
            width: 560px;
        }

        .item {
            width: 300px;
        }

        .mobile-hide-img {
            text-align: left;
            width: 125px;
        }

        .mobile-hide-img img {
            border: 1px solid #e6e6e6;
            border-radius: 4px;
        }

        .title-dark {
            text-align: left;
            border-bottom: 1px solid #cccccc;
            color: #4d4d4d;
            font-weight: 700;
            padding-bottom: 5px;
        }

        .item-col {
            padding-top: 20px;
            text-align: left;
            vertical-align: top;
        }

        .force-width-gmail {
            min-width: 600px;
            height: 0px !important;
            line-height: 1px !important;
            font-size: 1px !important;
        }

    </style>

    <style type="text/css" media="screen">
        @import url(http://fonts.googleapis.com/css?family=Oxygen:400,700);
    </style>

    <style type="text/css" media="screen">
        @media screen {
            /* Thanks Outlook 2013! */
            * {
                font-family: 'Oxygen', 'Helvetica Neue', 'Arial', 'sans-serif' !important;
            }
        }
    </style>

    <style type="text/css" media="only screen and (max-width: 480px)">
        /* Mobile styles */
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

            td[class*="mobile-header-padding-left"] {
                width: 160px !important;
                padding-left: 0 !important;
            }

            td[class*="mobile-header-padding-right"] {
                width: 160px !important;
                padding-right: 0 !important;
            }

            td[class="header-lg"] {
                font-size: 24px !important;
                padding-bottom: 5px !important;
            }

            td[class="content-padding"] {
                padding: 5px 0 5px !important;
            }

            td[class="button"] {
                padding: 5px 5px 30px !important;
            }

            td[class*="free-text"] {
                padding: 10px 18px 30px !important;
            }

            td[class~="mobile-hide-img"] {
                display: none !important;
                height: 0 !important;
                width: 0 !important;
                line-height: 0 !important;
            }

            td[class~="item"] {
                width: 140px !important;
                vertical-align: top !important;
            }

            td[class~="quantity"] {
                width: 50px !important;
            }

            td[class~="price"] {
                width: 90px !important;
            }

            td[class="item-table"] {
                padding: 30px 20px !important;
            }

            td[class="mini-container-left"],
            td[class="mini-container-right"] {
                padding: 0 15px 15px !important;
                display: block !important;
                width: 290px !important;
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
                <img src="http://s3.amazonaws.com/swu-filepicker/SBb2fQPrQ5ezxmqUTgCr_transparent.png"
                     class="force-width-gmail">
                <table cellspacing="0" cellpadding="0" width="100%" bgcolor="#ffffff"
                       background="http://s3.amazonaws.com/swu-filepicker/4E687TRe69Ld95IDWyEg_bg_top_02.jpg"
                       style="background-color:transparent">
                    <tr>
                        <td width="100%" height="80" valign="top" style="text-align: center; vertical-align:middle;">
                            <!--[if gte mso 9]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false"
                                    style="mso-width-percent:1000;height:80px; v-text-anchor:middle;">
                                <v:fill type="tile"
                                        src="http://s3.amazonaws.com/swu-filepicker/4E687TRe69Ld95IDWyEg_bg_top_02.jpg"
                                        color="#ffffff"/>
                                <v:textbox inset="0,0,0,0">
                            <![endif]-->
                            <center>
                                <table cellpadding="0" cellspacing="0" width="600" class="w320">
                                    <tr>
                                        <td class="pull-left mobile-header-padding-left"
                                            style="vertical-align: middle;">
                                            <a href="<?= /** @var string $home_url */
											$home_url ?>">
                                                <img width="137" height="47"
                                                     src="<?= /** @var string $mail_logo */
												     $mail_logo ?>"
                                                     alt="<?= /** @var string $site_name */
												     $site_name ?>" style="height: 50px; width: auto;"></a>
                                        </td>
                                        <td class="pull-right mobile-header-padding-right" style="color: #4d4d4d;">
											<?php /** @var array $social_links */
											foreach ( $social_links as $social_link ): ?>
                                                <a href="<?= $social_link['url'] ?>">
                                                    <img width="44" height="47"
                                                         src="<?= $social_link['img'] ?>"
                                                         alt="<?= $social_link['name'] ?>"/>
                                                </a>
											<?php endforeach; ?>
                                        </td>
                                    </tr>
                                </table>
                            </center>
                            <!--[if gte mso 9]>
                            </v:textbox>
                            </v:rect>
                            <![endif]-->
                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
    <tr>
        <td align="center" valign="top" width="100%" style="background-color: #f7f7f7;" class="content-padding">
            <center>
                <table cellspacing="0" cellpadding="0" width="600" class="w320">
                    <tr>
                        <td class="header-lg">
                            В вашем интернет-магазине "<?php echo esc_html( $site_name ) ?>" создан новый заказ!
                        </td>
                    </tr>
                    <tr>
                        <td class="free-text">
                            Заказ №<?= /** @var string $order_id */
							$order_id ?> успешно создан.
                            <br/>
							<?php if ( ! empty( $client_phone ) && ! empty( $clean_number ) ): ?>
                                <a
                                        href="tel:<?= $clean_number ?>"><?= $client_phone ?></a>
                                <br/>
							<?php endif ?>
							<?php if ( ! empty( $client_email ) ): ?>
                                <a href="mailto:<?= $client_email ?>"><?= $client_email ?></a>
							<?php endif ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="button">
                            <div><!--[if mso]>
                                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                                             xmlns:w="urn:schemas-microsoft-com:office:word" href="<?php
								/** @var string $order_edit_url */
								echo esc_url( $order_edit_url ) ?>"
                                             style="height:45px;v-text-anchor:middle;width:155px;" arcsize="15%"
                                             strokecolor="#ffffff" fillcolor="#ff6f6f">
                                    <w:anchorlock/>
                                    <center style="color:#ffffff;font-family:Helvetica, Arial, sans-serif;font-size:14px;font-weight:regular;">
                                        Смотреть заказ
                                    </center>
                                </v:roundrect>
                                <![endif]--><a href="<?= esc_url( $order_edit_url ) ?>"
                                               style="background-color:#ff6f6f;border-radius:5px;color:#ffffff;display:inline-block;font-family:'Cabin', Helvetica, Arial, sans-serif;font-size:14px;font-weight:regular;line-height:45px;text-align:center;text-decoration:none;width:155px;-webkit-text-size-adjust:none;mso-hide:all;">Смотреть
                                    заказ</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="w320">
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td class="mini-container-left">
                                        <table cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td class="mini-block-padding">
                                                    <table cellspacing="0" cellpadding="0" width="100%"
                                                           style="border-collapse:separate !important;">
                                                        <tr>
                                                            <td class="mini-block">
                                                                <span class="header-sm">Доставка</span><br/>
																<?= ! empty( $delivery_method ) ? sprintf( 'Способ доставки: %s<br>', $delivery_method ) : '' ?>
																<?= ! empty( $delivery_number ) ? sprintf( 'Номер отделения: %s<br>', $delivery_number ) : '' ?>
																<?= ! empty( $client_city ) ? sprintf( 'Город: %s<br>', $client_city ) : '' ?>
																<?= ! empty( $client_address ) ? sprintf( 'Адрес: %s<br>', $client_address ) : '' ?>
																<?= ! empty( $address_street ) ? sprintf( 'Улица: %s<br>', $address_street ) : '' ?>
																<?= ! empty( $address_house_number ) ? sprintf( 'Дом: %s<br>', $address_house_number ) : '' ?>
																<?= ! empty( $address_entrance_number ) ? sprintf( 'Подъезд: %s<br>', $address_entrance_number ) : '' ?>
																<?= ! empty( $address_apartment_number ) ? sprintf( 'Квартира: %s<br>', $address_apartment_number ) : '' ?>
																<?= ! empty( $client_last_name ) || ! empty( $client_first_name ) ? sprintf( 'Фамилия и имя: %s %s<br>', $client_last_name ?? '', $client_first_name ?? '' ) : '' ?>
																<?= ! empty( $client_email ) ? sprintf( 'E-mail: %s<br>', $client_email ) : '' ?>
																<?= ! empty( $client_phone ) ? sprintf( 'Телефон: %s<br>', $client_phone ) : '' ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td class="mini-container-right">
                                        <table cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td class="mini-block-padding">
                                                    <table cellspacing="0" cellpadding="0" width="100%"
                                                           style="border-collapse:separate !important;">
                                                        <tr>
                                                            <td class="mini-block">
                                                                <span class="header-sm">Оплата</span><br/>
																<?php /** @var string $payment_method */
																echo $payment_method ?> <br/>
                                                                <span class="header-sm">Время покупки</span><br/>
																<?= /** @var string $order_date */
																$order_date ?><br/>
                                                                <span class="header-sm">Комментарий</span><br/>
																<?= /** @var string $client_comment */
																$client_comment ?>

                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
    <tr>
        <td align="center" valign="top" width="100%"
            style="background-color: #ffffff;  border-top: 1px solid #e5e5e5; border-bottom: 1px solid #e5e5e5;">
            <center>
                <table cellpadding="0" cellspacing="0" width="600" class="w320">
                    <tr>
                        <td class="item-table">
                            <table cellspacing="0" cellpadding="0" width="100%">
                                <tr>
                                    <td class="title-dark" width="300">
                                        Позиция
                                    </td>
                                    <td class="title-dark" width="163">
                                        К-во
                                    </td>
                                    <td class="title-dark" width="97">
                                        Итого
                                    </td>
                                </tr>

								<?php /** @var array $cart_items */
								foreach ( $cart_items as $item ): ?>
                                    <tr>
                                        <td class="item-col item">
                                            <table cellspacing="0" cellpadding="0" width="100%">
                                                <tr>
                                                    <td class="mobile-hide-img">
                                                        <a href="<?= $item['link'] ?>" target="_blank">
                                                            <img width="110" src="<?= $item['thumbnail_url'] ?>"
                                                                 alt="<?= $item['name'] ?>">
                                                        </a>
                                                    </td>
                                                    <td class="product">
                                                        <a href="<?= $item['link'] ?>" target="_blank"
                                                           style="color: #4d4d4d; font-weight:bold;"><?= $item['name'] ?></a>
                                                        <br/>
														<?php foreach ( $item['attr'] as $attribute ) {
															echo $attribute['parent_name'] . ' : ' . $attribute['name'] . '<br/>';
														} ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td class="item-col quantity">
											<?= $item['qty'] ?>
                                        </td>
                                        <td class="item-col">
											<?= $item['all_price'] ?><?= $item['currency'] ?>
                                        </td>
                                    </tr>
								<?php endforeach; ?>

                                <tr>
                                    <td class="item-col item mobile-row-padding"></td>
                                    <td class="item-col quantity"></td>
                                    <td class="item-col price"></td>
                                </tr>

                                <tr>
                                    <td class="item-col item">
                                    </td>
                                    <td class="item-col quantity"
                                        style="text-align:right; padding-right: 10px; border-top: 1px solid #cccccc;">
                                        <span class="total-space">Стоимость товаров</span> <br/>
                                        <span class="total-space">Стоимость доставки</span> <br/>
                                        <span class="total-space">Скидка</span> <br/>
                                        <span class="total-space"
                                              style="font-weight: bold; color: #4d4d4d">Итого</span>
                                    </td>
                                    <td class="item-col price" style="text-align: left; border-top: 1px solid #cccccc;">
	                                    <span class="total-space"><?= /** @var string $products_cost */
		                                    $products_cost ?></span> <br/>
                                        <span class="total-space"><?= /** @var string $delivery_cost */
											$delivery_cost ?></span> <br/>
                                        <span class="total-space"><?= /** @var string $cart_discount */
											$cart_discount ?></span> <br/>
                                        <span class="total-space"
                                              style="font-weight:bold; color: #4d4d4d"><?= /** @var string $cart_amount */
											$cart_amount ?></span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
    <tr>
        <td align="center" valign="top" width="100%" style="background-color: #f7f7f7; height: 100px;">
            <center>
                <table cellspacing="0" cellpadding="0" width="600" class="w320">
                    <tr>
                        <td style="padding: 25px 0 25px">
                            Этот интернет-магазин работает на <strong><a
                                        href="https://f-shop.top/"
                                        target="_blank">F-SHOP</a></strong>
                        </td>
                    </tr>
                </table>
            </center>
        </td>
    </tr>
</table>
</body>
</html>