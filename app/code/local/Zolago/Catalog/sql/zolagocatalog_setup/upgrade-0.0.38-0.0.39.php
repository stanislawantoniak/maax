<?php
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

/* Note that addAttribute() has a field translation using _prepareValues().
 * But updateAttribute() does not use this.
 * So it is set correctly here to 'is_required',
 * but in addAttribute, you'd use 'required' for the same thing.
 */
$installer->updateAttribute('catalog_product', 'description_status', 'is_required', 0);

$installer->endSetup();