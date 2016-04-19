<?php
/** @var Zolago_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();
// Wrong idea
$installer->removeAttribute('catalog_product', 'charge_lower_commission');

$installer->endSetup();




