<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$model = Mage::getModel("cms/page")->
		load("default-microsite-vendor-landing-page", "identifier");
/* @var $model Mage_Cms_Model_Page */
		
if($model->getId()){
	$model->setData('layout_update_xml', '<update handle="umicrosite_landing"/>');
	$model->save();
}