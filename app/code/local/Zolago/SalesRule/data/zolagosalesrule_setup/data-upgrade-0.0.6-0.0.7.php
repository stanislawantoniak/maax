<?php

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
										     src="cid:logo_email.png">
									</a>
								</div>
							</div>
							<div class="lowerMarginWhite"
							     style="border-top:25px solid white;border-bottom:10px solid white;border-left:35px solid white;border-right:35px solid white;font-family:'Arial','Helvetica',sans-serif;font-size:13px;">



<h1 style="font-weight:normal;font-size:21px;border-bottom:25px solid white;font-family:'Arial','Helvetica',sans-serif;line-height:1.4em">
</h1>Promocje dla Ciebie:</h1>
{{var promotionList}}
<div style="border-top:15px solid white;border-bottom:35px solid white;line-height:1.4em;margin:0;padding:0">
                                                                        Email ten został on wysłany przez automat.
                                                                        </br>
						                        Dziękujemy za współpracę!<br/><br/>
									Zespół <a href='{{store url=""}}' style="text-decoration:none;color:#444">Modago.pl</a>
								</div><br/>

							</div>
						</td>
					</tr>
					</tbody>
				</table>
				<div style="background-color:#e5e5e5;direction:ltr;color:#777;font-size:11px;border:10px solid #E5E5E5;padding:0;margin:0;font-family:'Arial','Helvetica',sans-serif;text-align:center">&copy;
					{{dateAndTime format="Y"}} <a href='{{store url=""}}' style="text-decoration:none;color:#777">Modago.pl</a>
				</div>
			</div>
		</div>
	</center>
</div>
</body>
EOD;

$subject  = "Promocje dla ciebie";
$code     = "Promotions for subscribers";

/** @var Zolago_Common_Model_Core_Email_Template $model */
$model = Mage::getModel('core/email_template');
$model->loadByCode($code);
$model->setTemplateCode($code);
$model->setTemplateText($template);
$model->setTemplateSubject($subject);
$model->setAddedAt(date('Y-m-d H:i:s'));
$model->setTemplateType(Mage_Core_Model_Template::TYPE_HTML);
$model->save();
