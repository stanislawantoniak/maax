<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$model = Mage::getModel("cms/page")->
		load("default-microsite-vendor-landing-page", "identifier");
/* @var $model Mage_Cms_Model_Page */
		
if($model->getId()){
	$model->setData('content', '<h1 class="vendor-name">Vendor: {{var currentVendorLandingPageTitle|escape:html}}</h1>
<p>{{var currentVendorReviewsSummaryHtml}}</p>
<div class="generic-box vendor-description"><img class="vendor-img" src="{{media url=$currentVendor.getLogo()}}" alt="" /> {{var currentVendor.getDescription()|escape:html}}</div>
<div id="our-products">{{layout handle="umicrosite_current_vendor_products_list_solr"}}</div>');
	
	$model->setData('layout_update_xml', '<update handle="umicrosite_landing"/>');
	$model->save();
}