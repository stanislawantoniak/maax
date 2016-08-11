<?php

$tpl = array(
        'template_code'       => 'New account - Polish for modago.pl',
        'template_subject'    => 'Potwierdzenie założenia konta',
        'template_text'       =>
            <<<EOD
<!DOCTYPE html>
<html>
<head>
	<title>Modago - Potwierdzenie założenia konta</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<style type="text/css">
		/* CLIENT-SPECIFIC STYLES */
		body, table, td, a {
			-webkit-text-size-adjust: 100%;
			-ms-text-size-adjust: 100%;
		}

		/* Prevent WebKit and Windows mobile changing default text sizes */
		table, td {
			mso-table-lspace: 0pt;
			mso-table-rspace: 0pt;
		}

		/* Remove spacing between tables in Outlook 2007 and up */
		img {
			-ms-interpolation-mode: bicubic;
		}

		/* Allow smoother rendering of resized image in Internet Explorer */

		/* RESET STYLES */
		img {
			border: 0;
			height: auto;
			line-height: 100%;
			outline: none;
			text-decoration: none;
		}

		table {
			border-collapse: collapse !important;
		}

		body {
			height: 100% !important;
			margin: 0 !important;
			padding: 0 !important;
			width: 100% !important;
		}

		/* iOS BLUE LINKS */
		a[x-apple-data-detectors] {
			color: inherit !important;
			text-decoration: none !important;
			font-size: inherit !important;
			font-family: inherit !important;
			font-weight: inherit !important;
			line-height: inherit !important;
		}

		.img-max {
			max-width: 100% !important;
			width: 100% !important;
			height: auto !important;
		}

		.threeBoxesContainer {
			padding: 15px 0 0 0;
		}

		.welcomeText {
			padding: 25px 20px;
		}

		.welcomeText .welcomeTextTitle {
			font-weight: 700;
			font-size: 20px;
			padding-bottom: 25px;
			color: #333333;
			line-height: 1.4em;
		}

		.welcomeText .welcomeTextContent {
			font-size: 14px;
			color: #555555;
			line-height: 1.4em;
		}

		.welcomeSpacer {
			width: 45px;
			height: 3px;
			background-color: #C6A662;
			margin: 15px auto;
		}

		.twoBoxesContainer {
			padding: 30px 20px;
			text-align: center;
		}

		.twoBoxesContainer .twoBoxes {
			width: 48%;
			display: inline-block;
			vertical-align: top;
			text-align: left;
		}

		.twoBoxesContainer .twoBoxes .twoBoxesTitle {
			font-size: 20px;
			font-weight: 700;
			margin-bottom: 15px;
			margin-left: 10px;
			color: #333333;
		}

		.twoBoxesContainer .twoBoxes .twoBoxesText {
			margin-left: 10px;
			font-size: 14px;
			line-height: 1.4em;
			color: #555555;
		}

		.twoBoxesContainer .twoBoxes .twoBoxesText .twoBoxesButton {
			background-color: #F40A59;
			padding: 10px 15px;
			font-size: 12px;
			color: #FFFFFF;
			text-align: center;
			margin-top: 54px;
			font-weight: 700;
			display: inline-block;
			cursor: pointer;
			text-decoration: none;
		}

		.twoBoxesContainer .twoBoxes img {
			max-width: 100%;
			width: 100%;
		}
		
		/* MOBILE STYLES */
		@media screen and (max-width: 580px) {

			/* ALLOWS FOR FLUID TABLES */
			.wrapper {
				width: 100% !important;
				max-width: 100% !important;
			}

			/* ADJUSTS LAYOUT OF LOGO IMAGE */
			.logo img {
				margin-left: -15px !important;
			}

			/* USE THESE CLASSES TO HIDE CONTENT ON MOBILE */
			.mobile-hide {
				display: none !important;
			}
			
			.mobile-content {
                display:block !important;
                max-height: none !important;
                overflow:visible !important;
            }

			/* FULL-WIDTH TABLES */
			.responsive-table {
				width: 100% !important;
			}

			/* UTILITY CLASSES FOR ADJUSTING PADDING ON MOBILE */
			.padding {
				padding: 20px 15px 20px 15px !important;
			}

			.no-padding {
				padding: 0 !important;
			}

			.threeBoxesContainer {
				padding: 15px;
			}

		    .threeBoxes {
			    width: 100%;
			    height: auto;
			    display: block;
				padding-bottom: 15px;
		    }

		    .twoBoxesContainer .twoBoxes {
			    width: 100%;
			    display: block;
			    text-align: center !important;
		    }

		    .twoBoxesContainer .twoBoxes img {
			    padding-bottom: 20px;
		    }

		    .twoBoxesContainer .twoBoxes .twoBoxesText .twoBoxesButton {
			    margin-top: 20px;
		    }
		}

		/* ANDROID CENTER FIX */
		div[style*="margin: 16px 0;"] {
			margin: 0 !important;
		}
	</style>
</head>
<body style="margin: 0 !important;padding: 0 !important;background-color: #e5e5e5;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;height: 100% !important;width: 100% !important;" bgcolor="#e5e5e5">

<!-- Ten tekst będzie widać przed wejściem w maila (np. na liście wiadomości w gmailu) -->
<div style="display: none; font-size: 1px; color: #fefefe; line-height: 1px; font-family: Helvetica, Arial, Helvetica, sans-serif; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
	Dziękujemy za rejestrację w Modago.pl
</div>

<!-- HEADER -->
<div style="background-color:#e5e5e5;">
	<table bgcolor="#e5e5e5" border="0" cellpadding="0" cellspacing="0" width="100%" style="padding-top: 10px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse !important;">
		<tr class="mobile-hide">
			<td style="padding-top: 15px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;" class="no-padding"></td>
		</tr>
		<tr>
			<td align="center" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
				<!--[if (gte mso 9)|(IE)]>
				<table align="center" border="0" cellspacing="0" cellpadding="0" width="580">
					<tr>
						<td align="center" valign="top" width="580">
				<![endif]-->
				<table bgcolor="#000000" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 580px;border-bottom: 3px solid #c6a662;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse !important;" class="wrapper">
					<tr>
						<td align="left" valign="top" style="padding: 0;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;" class="logo">
							<a href="{{store url=''}}" target="_blank" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;">
								<img alt="{{var logo_alt}}" src="cid:logo.png" width="175" height="52" style="display: block;font-family: Helvetica, Arial, Helvetica, sans-serif;color: #ffffff;font-size: 16px;-ms-interpolation-mode: bicubic;border: 0;height: auto;line-height: 100%;outline: none;text-decoration: none;" border="0">
							</a>
						</td>
					</tr>
				</table>
				<!--[if (gte mso 9)|(IE)]>
				</td>
				</tr>
				</table>
				<![endif]-->
			</td>
		</tr>
		<tr>
			<td align="center" class="no-padding" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
				<!--[if (gte mso 9)|(IE)]>
				<table align="center" border="0" cellspacing="0" cellpadding="0" width="580">
					<tr>
						<td align="center" valign="top" width="580">
				<![endif]-->
				<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 580px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse !important;" class="responsive-table">
					<tr>
						<td style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
							<!-- TEXT CONTENT -->
							<table width="100%" border="0" cellspacing="0" cellpadding="0" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse !important;">
								<tr>
									<td style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
										<!-- COPY -->
										<table width="100%" border="0" cellspacing="0" cellpadding="0" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse !important;">
											<tr>
												<td align="left" style="font-family: Helvetica, Arial, Helvetica, sans-serif;color: #333333;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;" class="no-padding">
													<img src="/skin/frontend/modago/gallery/images/newsletter/head.png" alt="Witamy w Modago.pl Twoje ulubione sklepy w jednym miejscu" class="img-max mobile-hide" style="-ms-interpolation-mode: bicubic;border: 0;height: auto !important;line-height: 100%;outline: none;text-decoration: none;max-width: 100% !important;width: 100% !important;">
													<div class="mobile-content" style="display:none;max-height:0px;overflow:hidden;">
													    <img src="/skin/frontend/modago/gallery/images/newsletter/head_mobile.jpg" alt="Witamy w Modago.pl Twoje ulubione sklepy w jednym miejscu" class="img-max" style="-ms-interpolation-mode: bicubic;border: 0;height: auto !important;line-height: 100%;outline: none;text-decoration: none;max-width: 100% !important;width: 100% !important;">
													</div>
												</td>
											</tr>
										</table>
										<table width="100%" border="0" cellspacing="0" cellpadding="0" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse !important;">
											<tr>
												<td align="left" style="font-family: Helvetica, Arial, Helvetica, sans-serif;color: #333333;text-align: center;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;padding: 15px 0 0 0;" class="threeBoxesContainer">
													<img src="/skin/frontend/modago/gallery/images/newsletter/3boxes_delivery.png" alt="Darmowa dostawa" class="threeBoxes" style="-ms-interpolation-mode: bicubic;border: 0;height: auto;line-height: 100%;outline: none;text-decoration: none;">
													<img src="/skin/frontend/modago/gallery/images/newsletter/3boxes_return.png" alt="Zwrot do 30 dni" class="threeBoxes" style="-ms-interpolation-mode: bicubic;border: 0;height: auto;line-height: 100%;outline: none;text-decoration: none;margin: 0 10px;">
													<img src="/skin/frontend/modago/gallery/images/newsletter/3boxes_shops.png" alt="Ulubione sklepy" class="threeBoxes" style="-ms-interpolation-mode: bicubic;border: 0;height: auto;line-height: 100%;outline: none;text-decoration: none;">
												</td>
											</tr>
										</table>
										<table width="100%" border="0" cellspacing="0" cellpadding="0" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse !important;">
											<tr>
												<td align="left" style="font-family: Helvetica, Arial, Helvetica, sans-serif;color: #333333;text-align: center;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;" class="no-padding">
													<div style="padding-top:25px"></div>
													<div class="welcomeText" style="padding: 25px 20px;">
														<div class="welcomeTextTitle" style="font-weight: 700;font-size: 20px;padding-bottom: 25px;color: #333333;line-height: 1.4em;">
															TWOJE ULUBIONE SKLEPY MODOWE DOSTĘPNE W JEDNYM MIEJSCU.
														</div>
														<div class="welcomeTextContent" style="font-size: 14px;color: #555555;line-height: 1.4em;">
															W naszej internetowej galerii handlowej zrobisz zakupy w najlepszych markowych sklepach.
															Zaprosiliśmy do współpracy wszystkie najbardziej znane marki odzieżowe, których oferty
															można przeglądać i w kilku klikach kupić w naszym serwisie.
														</div>
													</div>
													<div class="welcomeSpacer" style="width: 45px;height: 3px;background-color: #C6A662;margin: 15px auto;"></div>
													<div class="welcomeText" style="padding: 25px 20px;">
														<div class="welcomeTextTitle" style="font-weight: 700;font-size: 20px;padding-bottom: 25px;color: #333333;line-height: 1.4em;">
															SZEROKA OFERTA, JEDEN KOSZYK ZAKUPOWY.
														</div>
														<div class="welcomeTextContent" style="font-size: 14px;color: #555555;line-height: 1.4em;">
															Produkty różnych sklepów możesz przeglądać w jednym miejscu i zamówić z jednego
															koszyka. Zaoszczędzisz dzięki temu cenny czas, który możesz wykorzystać na
															dodatkowe przyjemności.
														</div>
													</div>
													<div class="welcomeSpacer" style="width: 45px;height: 3px;background-color: #C6A662;margin: 15px auto;"></div>
													<div class="welcomeText" style="padding: 25px 20px;">
														<div class="welcomeTextTitle" style="font-weight: 700;font-size: 20px;padding-bottom: 25px;color: #333333;line-height: 1.4em;">
															SPECJALNE PROMOCJE DLA NASZYCH KLIENTÓW.
														</div>
														<div class="welcomeTextContent" style="font-size: 14px;color: #555555;line-height: 1.4em;">
															Dla zarejestrowanych Klientów przygotowujemy z producentami specjalne promocje,
															niedostępne w ich sklepach stacjonarnych. Dołącz do nas i korzystaj z dodatkowych
															rabatów.
														</div>
													</div>
													<div class="welcomeSpacer" style="width: 45px;height: 3px;background-color: #C6A662;margin: 15px auto;"></div>
													<div class="welcomeText" style="padding: 25px 20px;">
														<div class="welcomeTextTitle" style="font-weight: 700;font-size: 20px;padding-bottom: 25px;color: #333333;line-height: 1.4em;">
															BEZPIECZNE ZAKUPY, 100% SATYSFAKCJI.
														</div>
														<div class="welcomeTextContent" style="font-size: 14px;color: #555555;line-height: 1.4em;">
															W naszym serwisie kupujesz bezpośrednio od najbardziej znanych firm modowych, mając
															pewność, że zakupy będą udane. My czuwamy nad tym by były wygodne i bezpieczne.
														</div>
													</div>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<!--[if (gte mso 9)|(IE)]>
				</td>
				</tr>
				</table>
				<![endif]-->
			</td>
		</tr>
		<tr>
			<td align="center" style="padding-top: 15px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
				<!--[if (gte mso 9)|(IE)]>
				<table align="center" border="0" cellspacing="0" cellpadding="0" width="580">
					<tr>
						<td align="center" valign="top" width="580">
				<![endif]-->
				<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 580px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse !important;" class="responsive-table">
					<tr>
						<td align="center" height="100%" valign="top" width="100%" style="background: #FFFFFF;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
							<!--[if (gte mso 9)|(IE)]>
							<table align="center" border="0" cellspacing="0" cellpadding="0" width="580">
								<tr>
									<td align="center" valign="top" width="580">
							<![endif]-->
							<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 560px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse !important;">
								<tr>
									<td align="center" valign="top" style="font-family: Helvetica, Arial, Helvetica, sans-serif;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
										<div class="twoBoxesContainer" style="padding: 30px 20px;text-align: center;">
											<div class="twoBoxes" style="width: 48%;display: inline-block;vertical-align: top;text-align: left;">
												<img src="/skin/frontend/modago/gallery/images/newsletter/bottom.png" alt="Kobieta" style="-ms-interpolation-mode: bicubic;border: 0;height: auto;line-height: 100%;outline: none;text-decoration: none;max-width: 100%;width: 100%;">
											</div>
											<div class="twoBoxes" style="width: 48%;display: inline-block;vertical-align: top;text-align: left;">
												<div class="twoBoxesTitle" style="font-size: 20px;font-weight: 700;margin-bottom: 15px;margin-left: 10px;color: #333333;">
													Kupuj swoje ulubione marki do 50% taniej!
												</div>
												<div class="twoBoxesText" style="margin-left: 10px;font-size: 14px;line-height: 1.4em;color: #555555;">
													Im więcej przeglądasz, im więcej kupujesz
													tym więcej otrzymasz kodów rabatowych
													i tym lepiej dobierzemy je do Ciebie.
													<br>
													<a href="{{store url='mypromotions'}}" class="twoBoxesButton" style="-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;background-color: #F40A59;padding: 10px 15px;font-size: 12px;color: #FFFFFF;text-align: center;margin-top: 54px;font-weight: 700;display: inline-block;cursor: pointer;text-decoration: none;">
														ZOBACZ KUPONY
													</a>
												</div>
											</div>
										</div>
									</td>
								</tr>
							</table>
							<!--[if (gte mso 9)|(IE)]>
							</td>
							</tr>
							</table>
							<![endif]-->
						</td>
					</tr>
				</table>
				<!--[if (gte mso 9)|(IE)]>
				</td>
				</tr>
				</table>
				<![endif]-->
			</td>
		</tr>
		<tr>
			<td align="center" style="padding: 20px 0px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
				<!--[if (gte mso 9)|(IE)]>
				<table align="center" border="0" cellspacing="0" cellpadding="0" width="580">
					<tr>
						<td align="center" valign="top" width="580">
				<![endif]-->
				<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="max-width: 580px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse !important;" class="responsive-table">
					<tr>
						<td align="center" style="font-size: 12px;line-height: 18px;font-family: Helvetica, Arial, Helvetica, sans-serif;color: #666666;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
							&copy; {{var year}} <a href="{{store url=''}}" target="_blank" style="color: #666666;text-decoration: none;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;">Modago.pl</a>
						</td>
					</tr>
				</table>
				<!--[if (gte mso 9)|(IE)]>
				</td>
				</tr>
				</table>
				<![endif]-->
			</td>
		</tr>
	</table>
</div>
</body>
</html>
EOD
);


/** @var Mage_Core_Model_Email_Template $templateModel */
$templateModel =  Mage::getModel('core/email_template');
$templateModel->loadByCode($tpl['template_code']);

if($tplId = $templateModel->getId()) {
    $templateModel
        ->setData($tpl)
        ->setId($tplId)
        ->save();
} else {
   return false;
}