<?php
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;

$installer->startSetup();
/*** Update customer address attributes*/
$installer->updateAttribute('catalog_product', 'description_accepted', 'is_required', false); 
$installer->updateAttribute('catalog_product', 'description_accepted', 'default', 0); 
$installer->updateAttribute('catalog_product', 'description_accepted', 'is_global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE); 
$installer->updateAttribute('catalog_product', 'description_accepted', 'grid_permission', Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::DISPLAY); 



/*** Update order address attributes*/
$installer->endSetup();