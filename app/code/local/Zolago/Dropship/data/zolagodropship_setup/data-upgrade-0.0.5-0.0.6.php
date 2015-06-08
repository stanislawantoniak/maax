<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/* @var $model Mage_Cms_Model_Block */
$model = Mage::getModel("cms/block");

// Creating Structure

// udropship-help-pl
// udropship-help-pl-campaign
// udropship-help-pl-campaign-placement
// udropship-help-pl-campaign-placement-category
// udropship-help-pl-campaign-vendor
// udropship-help-pl-campaign-vendor-edit
// udropship-help-pl-udpo
// udropship-help-pl-udpo-vendor
// udropship-help-pl-udpo-vendor-aggregated
// udropship-help-pl-udpo-vendor-edit
// udropship-help-pl-udprod
// udropship-help-pl-udprod-vendor-attributes
// udropship-help-pl-udprod-vendor-image
// udropship-help-pl-udprod-vendor-price
// udropship-help-pl-udprod-vendor-product
// udropship-help-pl-udqa
// udropship-help-pl-udqa-vendor
// udropship-help-pl-udqa-vendor-questionEdit
// udropship-help-pl-udqa-vendor-questions
// udropship-help-pl-udropship
// udropship-help-pl-udropship-ghapi
// udropship-help-pl-udropship-operator
// udropship-help-pl-udropship-operator-edit
// udropship-help-pl-udropship-pos
// udropship-help-pl-udropship-pos-edit
// udropship-help-pl-udropship-sizetable
// udropship-help-pl-udropship-sizetable-edit
// udropship-help-pl-udropship-vendor-settings
// udropship-help-pl-udropship-vendor-settings-info
// udropship-help-pl-udropship-vendor-settings-rma
// udropship-help-pl-udropship-vendor-settings-shipping
// udropship-help-pl-urma
// udropship-help-pl-urma-vendor
// udropship-help-pl-urma-vendor-edit

$helps = array();

$arrPL = array('udropship-help-pl', 'udropship-help-pl-campaign', 'udropship-help-pl-campaign-placement', 'udropship-help-pl-campaign-placement-category', 'udropship-help-pl-campaign-vendor', 'udropship-help-pl-campaign-vendor-edit', 'udropship-help-pl-udpo', 'udropship-help-pl-udpo-vendor', 'udropship-help-pl-udpo-vendor-aggregated', 'udropship-help-pl-udpo-vendor-edit', 'udropship-help-pl-udprod', 'udropship-help-pl-udprod-vendor-attributes', 'udropship-help-pl-udprod-vendor-image', 'udropship-help-pl-udprod-vendor-price', 'udropship-help-pl-udprod-vendor-product', 'udropship-help-pl-udqa', 'udropship-help-pl-udqa-vendor', 'udropship-help-pl-udqa-vendor-questionEdit', 'udropship-help-pl-udqa-vendor-questions', 'udropship-help-pl-udropship', 'udropship-help-pl-udropship-ghapi', 'udropship-help-pl-udropship-operator', 'udropship-help-pl-udropship-operator-edit', 'udropship-help-pl-udropship-pos', 'udropship-help-pl-udropship-pos-edit', 'udropship-help-pl-udropship-sizetable', 'udropship-help-pl-udropship-sizetable-edit', 'udropship-help-pl-udropship-vendor-settings', 'udropship-help-pl-udropship-vendor-settings-info', 'udropship-help-pl-udropship-vendor-settings-rma', 'udropship-help-pl-udropship-vendor-settings-shipping', 'udropship-help-pl-urma', 'udropship-help-pl-urma-vendor', 'udropship-help-pl-urma-vendor-edit');
foreach ($arrPL as $identifier) {
    $helps[] = array(
        'title'         => $identifier,
        'identifier'    => $identifier,
        'content'       => $identifier,
        'is_active'     => 1,
        'stores'        => 0
    );
}
$arrEN = array('udropship-help-en', 'udropship-help-en-campaign', 'udropship-help-en-campaign-placement', 'udropship-help-en-campaign-placement-category', 'udropship-help-en-campaign-vendor', 'udropship-help-en-campaign-vendor-edit', 'udropship-help-en-udpo', 'udropship-help-en-udpo-vendor', 'udropship-help-en-udpo-vendor-aggregated', 'udropship-help-en-udpo-vendor-edit', 'udropship-help-en-udprod', 'udropship-help-en-udprod-vendor-attributes', 'udropship-help-en-udprod-vendor-image', 'udropship-help-en-udprod-vendor-price', 'udropship-help-en-udprod-vendor-product', 'udropship-help-en-udqa', 'udropship-help-en-udqa-vendor', 'udropship-help-en-udqa-vendor-questionEdit', 'udropship-help-en-udqa-vendor-questions', 'udropship-help-en-udropship', 'udropship-help-en-udropship-ghapi', 'udropship-help-en-udropship-operator', 'udropship-help-en-udropship-operator-edit', 'udropship-help-en-udropship-pos', 'udropship-help-en-udropship-pos-edit', 'udropship-help-en-udropship-sizetable', 'udropship-help-en-udropship-sizetable-edit', 'udropship-help-en-udropship-vendor-settings', 'udropship-help-en-udropship-vendor-settings-info', 'udropship-help-en-udropship-vendor-settings-rma', 'udropship-help-en-udropship-vendor-settings-shipping', 'udropship-help-en-urma', 'udropship-help-en-urma-vendor', 'udropship-help-en-urma-vendor-edit');
foreach ($arrEN as $identifier) {
    $helps[] = array(
        'title'         => $identifier,
        'identifier'    => $identifier,
        'content'       => $identifier,
        'is_active'     => 1,
        'stores'        => 0
    );
}
// Save
foreach ($helps as $data) {
    $block = Mage::getModel('cms/block')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData, $data);
    }
    $block->setData($data)->save();
}


// Populating data

// Images Help
$model->load("vendor-portal-mass-images-help", "identifier");

if ($model->getId()) {
    $content = $model->getData('content');
    $model->load("udropship-help-pl-udprod-vendor-image", "identifier")->setData('content', $content)->save();
}

// GH Api Help
$model->load("ghapi-help", "identifier");
if ($model->getId()) {
    $content = $model->getData('content');
    $model->load("udropship-help-pl-udropship-ghapi", "identifier")->setData('content', $content)->save();
}