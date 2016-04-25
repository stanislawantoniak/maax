<?php


$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('sales_setup');

//Adding Attribute converter_price_type
$attributeBrandshopCode = Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_BRANDSHOP_CODE;
$attributeBrandshop = $setup->addAttribute(
                          Mage_Catalog_Model_Product::ENTITY, $attributeBrandshopCode,
                          array(
                              'type'       => 'int',
                              'input'      => 'select',
                              'label'      => 'Brandshop',
                              'required'   => true,
                              'user_defined' => 1,
                              'visible' => 1,
                              'backend'    => 'eav/entity_attribute_backend_array',
                              'source'     => 'Zolago_Dropship_Model_Vendor_Brandshop_Source',
                              'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
                              'sort_order' => 21,
                              'group' => 'General',
                          )
                      );

// sql update
$partSize = 10000;

$model = Mage::getResourceModel('zolagocatalog/product_configurable');
$table = $model->getValueTable('catalog/product','int');
$brandshopId = Mage::getSingleton('eav/config')->getAttribute(Zolago_Catalog_Model_Product::ENTITY,$attributeBrandshopCode)->getAttributeId();
$vendorId = Mage::getSingleton('eav/config')->getAttribute(Zolago_Catalog_Model_Product::ENTITY,ZolagoOs_OmniChannel_Model_Vendor::ENTITY)->getAttributeId();
$conn = $installer->getConnection();
$counter = $conn
           ->select()
           ->from($table,array('count(*) as counter'))
           ->where('attribute_id = '.$vendorId);
$count = $conn->fetchAll($counter);
$count[0]['counter'];
$_tmp = 0;
while ($_tmp < $count[0]['counter']) {
    $select = $conn
              ->select()
              ->from($table,array('attribute_id','entity_type_id','store_id','entity_id','value'))
              ->where('attribute_id = '.$vendorId)
              ->order('value_id')
              ->limit($partSize,$_tmp);
    $data = $conn->fetchAll($select);
    foreach ($data as &$line) {
        $line['attribute_id'] = $brandshopId;
    }
    $conn->insertOnDuplicate($table,$data,array('value'));
    $_tmp += $partSize;
}

$installer->endSetup();
