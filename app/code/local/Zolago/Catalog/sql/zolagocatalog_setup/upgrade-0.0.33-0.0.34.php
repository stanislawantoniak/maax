<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

/*
 * Add Category Attributes basic category
 */
$installer->updateAttribute('catalog_category', 'dynamic_meta_title', 'note', 'Use {$attribute first_letter=capital} to make first character uppercase. <br/>To add date use {$current_date format="Y-m-d"}');

$installer->endSetup();