<?php
/**
 * Update reservation flag
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$poTable = $this->getTable("udpo/po");

/**
 * Get the resource model
 */
$resource = Mage::getSingleton('core/resource');

/**
 * Retrieve the read connection
 */
$readConnection = $resource->getConnection('core_read');
$configTable = $resource->getTableName('core_config_data');

$query = 'SELECT value FROM ' . $configTable . " WHERE path='zolagocatalog/config/po_open_order'";
$poOpenOrder = $readConnection->fetchOne($query);

if (!$poOpenOrder) {
    //get default data
    $configFile = Mage::getConfig()->getModuleDir('etc', 'Zolago_Catalog') . DS . 'config.xml';
    $xmlObj = new Varien_Simplexml_Config($configFile);
    $xmlData = (string)$xmlObj->getNode('default/zolagocatalog/config/po_open_order');
}

if ($poOpenOrder) {
    $update = "UPDATE `{$poTable}`  SET reservation=0 WHERE `udropship_status` NOT IN ({$poOpenOrder})";
    $installer->run($update);
}

$installer->endSetup();