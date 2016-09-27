<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

/** @var Magpleasure_Common_Helper_Data $helper */
$helper = Mage::helper('magpleasure');
$ids = $helper->getStore()->getAllStores();

$drops = array();
foreach ($ids as $id) {
    $drops[] = "DROP TABLE IF EXISTS {$this->getTable("mp_search_index_$id")}";
    $drops[] = "DROP TABLE IF EXISTS {$this->getTable("mp_search_result_$id")}";
}
$installer->run(implode(';', $drops));

$installer->run("
    ALTER TABLE {$this->getTable('mp_search_index')}
    ADD updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

    UPDATE {$this->getTable('mp_search_index')}
    SET updated_at = CURRENT_TIMESTAMP;
");

$model = Mage::getModel('searchcore/resource_index');
$model->setReindexRequiredFlag();

$installer->endSetup();