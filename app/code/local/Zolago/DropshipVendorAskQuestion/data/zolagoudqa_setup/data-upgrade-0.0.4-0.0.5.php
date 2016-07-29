<?php
/* no for standalone version
$template = <<<EOD
<body style="margin:0;padding:0;background-color:#e5e5e5;color:#444444">
<!--[if gte mso 9]>
<style type="text/css">
	.container {
		width: 580px;
	}
	.lowerMarginWhite {
		margin: 25px 35px 10px 35px;
	}
	.productsTable {
		width: 510px;
	}
	.spacer {
		margin: 0 0 25px 0;
	}
	.productColumn {
		width: 420px;
	}
	.valueColumn {
		width: 80px;
	}
	.imgColumn {
		width: 60px;
	}
	.productNameColumn {
		padding-top:10px;
	}
</style>
<![endif]-->
<style type="text/css">
	@media only screen and (max-width: 480px) {
		.borderContainer {
			border: none !important;
		}

		.lowerMarginBlack {
			margin-left: -20px !important;
		}

		.lowerMarginWhite {
			border-left: 15px solid white !important;
			border-right: 15px solid white !important;
		}
	}
</style>
<div style="margin:0;padding:0;background-color:#e5e5e5;color:#444444">
	<center>
		<div class="borderContainer" style="border:20px solid #e5e5e5">
			<div>
				<table style="direction:ltr;text-align:left;font-family:'Arial','Helvetica',sans-serif;color:#444;background-color:white;max-width:580px"
				       cellpadding="0" cellspacing="0" border="0" class="container">
					<tbody>
					<tr>
						<td style="margin:0;padding:0">
							<div style="width:100%;background-color:black;border-bottom:3px solid #C6A662;">
								<div class="lowerMarginBlack">
									<a href='{{store url=""}}' style="text-decoration:none;border:none">
										<img width="175" height="52" alt="{{var logo_alt}}"
										     src="cid:logo.png">
									</a>
								</div>
							</div>
							<div class="lowerMarginWhite"
							     style="border-top:25px solid white;border-bottom:10px solid white;border-left:35px solid white;border-right:35px solid white;font-family:'Arial','Helvetica',sans-serif;font-size:13px;">
								<h1 style="font-weight:normal;font-size:21px;border-bottom:15px solid white;font-family:'Arial','Helvetica',sans-serif">
                                                                                  {{if local_vendor}}Galeria {{var store.getFrontendName()}} otrzymała Twoje pytanie.{{else}}Sprzedawca {{var vendor.getVendorName()}} otrzymał Twoje pytanie.{{/if}}
								</h1>
								<div style="border-bottom:25px solid white;line-height: 1.4em;margin:0;padding:0">
									Pracownicy firmy postarają się na nie odpowiedzieć w ciągu 1 dnia roboczego.
								</div>
								<h2 style="font-size: 15px;border-bottom:10px solid white;border-top:10px solid white;font-family:'Arial','Helvetica',sans-serif;margin:0;padding:0">
								Treść Twojej wiadomości:
								</h2>
								<div style="border-bottom:25px solid white;line-height: 1.4em;margin:0;padding:0">
									{{var question.question_text}}
								</div>

								{{if increment_id}}
								<h2 style="font-size: 15px;border-bottom:10px solid white;font-family:'Arial','Helvetica',sans-serif;margin:0;padding:0">
								 Dotyczy:
								</h2>
								<div style="border-bottom:25px solid white;line-height: 1.4em;margin:0;padding:0">
								 Zamówienie nr {{var increment_id}}
								</div>
								{{/if}}

								{{if question.product_id}}
								<h2 style="font-size: 15px;border-bottom:10px solid white;font-family:'Arial','Helvetica',sans-serif;margin:0;padding:0">
								 Dotyczy:
								</h2>
								<div style="border-bottom:25px solid white;line-height: 1.4em;margin:0;padding:0">
								Produkt: {{var question.product_name}}
								</div>
								{{/if}}

                                                		<div style="border-top:15px solid white;border-bottom:25px solid white;line-height:1.4em;margin:0;padding:0">
                                                                        <b>Nie odpowiadaj na ten e-mail</b>, gdyż został on wysłany przez automat. Jeśli masz dodatkowe pytania, <a target="_blank" style="text-decoration:none;color:#15c" href='{{if local_vendor}}{{store url="help/contact/gallery"}}{{else}}{{store url="help/contact/vendor"}}{{/if}}' style="text-decoration:none;color:#15c">
                                                                                                                                  {{if local_vendor}}skontaktuj się ze galerią{{else}}skontaktuj się ze sprzedawcą{{/if}}.
                                                                                                                                 </a></br>
						                        Dziękujemy i zapraszamy ponownie!<br/><br/>
									Zespół <a href='{{store url=""}}' style="text-decoration:none;color:#444">Modago.pl</a>
								</div><br/>
							</div>
						</td>
					</tr>
					</tbody>
				</table>
				<div style="background-color:#e5e5e5;direction:ltr;color:#777;font-size:11px;border:10px solid #E5E5E5;padding:0;margin:0;font-family:'Arial','Helvetica',sans-serif;text-align:center">&copy;
					2014 <a href='{{store url=""}}' style="text-decoration:none;color:#777">Modago.pl</a>
				</div>
			</div>
		</div>
	</center>
</div>
</body>
EOD;

$model = Mage::getModel('core/email_template')->loadByCode('Customer new question confirmation - Polish for Modago');
$model->setTemplateText($template);
$model->save();

$template = <<<EOD
<body style="margin:0;padding:0;background-color:#e5e5e5;color:#444444">
<!--[if gte mso 9]>
<style type="text/css">
	.container {
		width: 580px;
	}
	.lowerMarginWhite {
		margin: 25px 35px 10px 35px;
	}
	.productsTable {
		width: 510px;
	}
	.spacer {
		margin: 0 0 25px 0;
	}
	.productColumn {
		width: 420px;
	}
	.valueColumn {
		width: 80px;
	}
	.imgColumn {
		width: 60px;
	}
	.productNameColumn {
		padding-top:10px;
	}
</style>
<![endif]-->
<style type="text/css">
	@media only screen and (max-width: 480px) {
		.borderContainer {
			border: none !important;
		}

		.lowerMarginBlack {
			margin-left: -20px !important;
		}

		.lowerMarginWhite {
			border-left: 15px solid white !important;
			border-right: 15px solid white !important;
		}
	}
</style>
<div style="margin:0;padding:0;background-color:#e5e5e5;color:#444444">
	<center>
		<div class="borderContainer" style="border:20px solid #e5e5e5">
			<div>
				<table style="direction:ltr;text-align:left;font-family:'Arial','Helvetica',sans-serif;color:#444;background-color:white;max-width:580px"
				       cellpadding="0" cellspacing="0" border="0" class="container">
					<tbody>
					<tr>
						<td style="margin:0;padding:0">
							<div style="width:100%;background-color:black;border-bottom:3px solid #C6A662;">
								<div class="lowerMarginBlack">
									<a href='{{store url=""}}' style="text-decoration:none;border:none">
										<img width="175" height="52" alt="{{var logo_alt}}"
										     src="cid:logo.png">
									</a>
								</div>
							</div>
							<div class="lowerMarginWhite"
							     style="border-top:25px solid white;border-bottom:10px solid white;border-left:35px solid white;border-right:35px solid white;font-family:'Arial','Helvetica',sans-serif;font-size:13px;">
								<h1 style="font-weight:normal;font-size:21px;border-bottom:25px solid white;font-family:'Arial','Helvetica',sans-serif">
                                 {{if local_vendor}} Już odpowiadamy na Twoją wiadomość! {{var store.getFrontendName()}}{{else}}Sprzedawca {{var vendor.getVendorName()}} odpowiedział na Twoje pytanie. {{/if}}                       
								</h1>

								<h2 style="font-size: 15px;border-bottom:0px solid white;font-family:'Arial','Helvetica',sans-serif;margin:0;padding:0">
								Odpowiedź {{if local_vendor}}galerii {{var store.getFrontendName()}}{{else}}sprzedawcy {{var vendor.getVendorName()}}{{/if}}:
								</h2>
								<div style="border-bottom:10px solid white;line-height: 1.2em;margin:0;padding:0">
									({{var question.answer_date}})
								</div>
								<div style="border-bottom:45px solid white;line-height: 1.4em;margin:0;padding:0">
									{{var question.answer_text}}
								</div>
								<h2 style="font-size: 15px;border-bottom:0px solid white;font-family:'Arial','Helvetica',sans-serif;margin:0;padding:0">
                                                                           Treść Twojej wiadomości:
								</h2>
								<div style="border-bottom:10px solid white;line-height: 1.2em;margin:0;padding:0">
									({{var question.question_date}})
								</div>
								<div style="border-bottom:45px solid white;line-height: 1.4em;margin:0;padding:0">
									{{var question.question_text}}
								</div>

								{{if increment_id}}
								<h2 style="font-size: 15px;border-bottom:10px solid white;font-family:'Arial','Helvetica',sans-serif;margin:0;padding:0">
								 Dotyczy:
								</h2>
								<div style="border-bottom:25px solid white;line-height: 1.4em;margin:0;padding:0">
								 Zamówienie nr {{var increment_id}}
								</div>
								{{/if}}

								{{if question.product_id}}
								<h2 style="font-size: 15px;border-bottom:10px solid white;font-family:'Arial','Helvetica',sans-serif;margin:0;padding:0">
								 Dotyczy:
								</h2>
								<div style="border-bottom:25px solid white;line-height: 1.4em;margin:0;padding:0">
								Produkt: {{var question.product_name}}
								</div>
								{{/if}}

                                                		<div style="border-top:10px solid white;border-bottom:35px solid white;line-height:1.4em;margin:0;padding:0">
                                                                    <b>Nie odpowiadaj na ten e-mail</b>, gdyż został on wysłany przez automat. Jeśli masz dodatkowe pytania, {{if local_vendor}}<a target="_blank" href="/help/contact/gallery/" style="text-decoration:none;color:#15c">
                                                                                                                                  skorzystaj z formularz kontaktowego.
                                                                                                                                 </a>{{else}}<a target="_blank" href="/help/contact/vendor/" style="text-decoration:none;color:#15c">
                                                                                                                                  skorzystaj z formularz kontaktowego.
                                                                                                                                 </a>  {{/if}}
						                        Dziękujemy i zapraszamy ponownie!<br/><br/>
									Zespół <a href='{{store url=""}}' style="text-decoration:none;color:#444">Modago.pl</a>
								</div><br/>
							</div>
						</td>
					</tr>
					</tbody>
				</table>
				<div style="background-color:#e5e5e5;direction:ltr;color:#777;font-size:11px;border:10px solid #E5E5E5;padding:0;margin:0;font-family:'Arial','Helvetica',sans-serif;text-align:center">&copy;
					2014 <a href='{{store url=""}}' style="text-decoration:none;color:#777">Modago.pl</a>
				</div>
			</div>
		</div>
	</center>
</div>
</body>
EOD;

$model = Mage::getModel('core/email_template')->loadByCode('Customer notification after vendor answer to question - Polish for Modago');
$model->setTemplateText($template);
$model->save();

$subject = <<<EOD
{{var store_name}} – pytanie od klienta{{if increment_id}} dotyczące zamówienia {{var increment_id}}{{/if}}{{if question.product_id}} dotyczące produktu {{var question.product_name}}{{/if}}
EOD;
$template = <<<EOD
<body style="margin:0;padding:0;background-color:#e5e5e5;color:#444444">
<!--[if gte mso 9]>
<style type="text/css">
	.container {
		width: 580px;
	}
	.lowerMarginWhite {
		margin: 25px 35px 10px 35px;
	}
	.productsTable {
		width: 510px;
	}
	.spacer {
		margin: 0 0 25px 0;
	}
	.productColumn {
		width: 420px;
	}
	.valueColumn {
		width: 80px;
	}
	.imgColumn {
		width: 60px;
	}
	.productNameColumn {
		padding-top:10px;
	}
</style>
<![endif]-->
<style type="text/css">
	@media only screen and (max-width: 480px) {
		.borderContainer {
			border: none !important;
		}

		.lowerMarginBlack {
			margin-left: -20px !important;
		}

		.lowerMarginWhite {
			border-left: 15px solid white !important;
			border-right: 15px solid white !important;
		}
	}
</style>
<div style="margin:0;padding:0;background-color:#e5e5e5;color:#444444">
	<center>
		<div class="borderContainer" style="border:20px solid #e5e5e5">
			<div>
				<table style="direction:ltr;text-align:left;font-family:'Arial','Helvetica',sans-serif;color:#444;background-color:white;max-width:580px"
				       cellpadding="0" cellspacing="0" border="0" class="container">
					<tbody>
					<tr>
						<td style="margin:0;padding:0">
							<div style="width:100%;background-color:black;border-bottom:3px solid #C6A662;">
								<div class="lowerMarginBlack">
									<a href='{{store url=""}}' style="text-decoration:none;border:none">
										<img width="175" height="52" alt="{{var logo_alt}}"
										     src="cid:logo.png">
									</a>
								</div>
							</div>
							<div class="lowerMarginWhite"
							     style="border-top:25px solid white;border-bottom:10px solid white;border-left:35px solid white;border-right:35px solid white;font-family:'Arial','Helvetica',sans-serif;font-size:13px;">
					


<h1 style="font-weight:normal;font-size:21px;border-bottom:25px solid white;font-family:'Arial','Helvetica',sans-serif;line-height:1.4em">
   Prosimy o odpowiedź na nowe pytanie od klienta{{if increment_id}} dotyczące przesyłki  #{{var increment_id}}{{/if}}{{if question.product_id}} dotyczące produktu {{var question.product_name}}{{/if}}. 
</h1>

<h2 style="font-size: 15px;border-bottom:10px solid white;font-family:'Arial','Helvetica',sans-serif;margin:0;padding:0;line-height:1.4em">
	Klient:
</h2>
<div style="border-bottom:25px solid white;line-height: 1.4em;margin:0;padding:0;line-height:1.4em">
     {{var question.customer_name}}
</div>

{{if increment_id}}
<h2 style="font-size: 15px;border-bottom:10px solid white;border-top:10px solid white;font-family:'Arial','Helvetica',sans-serif;margin:0;padding:0;line-height:1.4em">
	Zamówienie:
</h2>
<div style="border-bottom:25px solid white;line-height: 1.4em;margin:0;padding:0;line-height:1.4em">
{{var increment_id}}
</div>
{{/if}}

{{if question.product_id}}
<h2 style="font-size: 15px;border-bottom:10px solid white;border-top:10px solid white;font-family:'Arial','Helvetica',sans-serif;margin:0;padding:0;line-height:1.4em">
	Produkt:
</h2>
<div style="border-bottom:25px solid white;line-height: 1.4em;margin:0;padding:0;line-height:1.4em">
{{var question.product_name}}
</div>
{{/if}}

<h2 style="font-size: 15px;border-bottom:10px solid white;border-top:10px solid white;font-family:'Arial','Helvetica',sans-serif;margin:0;padding:0;line-height:1.4em">
	Treść pytania:
</h2>
<div style="border-bottom:25px solid white;line-height: 1.4em;margin:0;padding:0;line-height:1.4em">
{{var question.question_text}}
</div>
							

<div style="border-top:15px solid white;border-bottom:35px solid white;line-height:1.4em;margin:0;padding:0">
                                                                        <b>Nie odpowiadaj na ten e-mail</b>, gdyż został on wysłany przez automat. <br/><a target="_blank" style="text-decoration:none;color:#15c" href="{{store url="udqa/vendor/"}}">Aby odpowiedzieć Klientowi, kliknij tutaj i zaloguj się do panelu obsługi sklepu.</a> 
                                                                                                                               
                                                                                                                                 </a></br>
						                        Dziękujemy za współpracę!<br/><br/>
									Zespół <a href='{{store url=""}}' style="text-decoration:none;color:#444">Modago.pl</a>
								</div><br/>

							</div>
						</td>
					</tr>
					</tbody>
				</table>
				<div style="background-color:#e5e5e5;direction:ltr;color:#777;font-size:11px;border:10px solid #E5E5E5;padding:0;margin:0;font-family:'Arial','Helvetica',sans-serif;text-align:center">&copy;
					2014 <a href='{{store url=""}}' style="text-decoration:none;color:#777">Modago.pl</a>
				</div>
			</div>
		</div>
	</center>
</div>
</body>

EOD;
$model = Mage::getModel('core/email_template')->loadByCode('Vendor notification after customer question - Polish for Modago');
$model->setTemplateText($template);
$model->setTemplateSubject($subject);
$model->save();

*/