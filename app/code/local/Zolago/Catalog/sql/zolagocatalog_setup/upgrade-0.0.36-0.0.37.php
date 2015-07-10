<?php
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->removeAttribute('catalog_product', 'description_accepted');

$installer->addAttribute('catalog_product', 'description_status', array(
    'group'                     => 'General',
    'input'                     => 'select',
    'type'                      => 'int',
    'label'                     => 'Description status',
    'source'            		=> 'zolagocatalog/product_source_description',
    'backend'                   => '',
    'visible'                   => false,
    'required'                  => true,
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'user_defined'      		=> true,
    'default'           		=> Zolago_Catalog_Model_Product_Source_Description::DESCRIPTION_NOT_ACCEPTED,
));

$installer->updateAttribute('catalog_product', 'description_status', 'grid_permission', Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::DISPLAY);

$installer->endSetup();