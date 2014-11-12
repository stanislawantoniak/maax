<?php
/**
 * adding new flag notify_email for forcing notifying customer
 */
error_reporting(E_ALL);
ini_set("display_errors", 1);



$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$coll = Mage::getResourceModel("core/config_data_collection");
$coll->addFieldToFilter("path", 'urma/general/statuses');
$coll->addFieldToFilter("scope_id", 0);
$coll->addFieldToFilter("scope", "default");

$conf = $coll->getIterator()->current()->getData('value');
$configValue = Mage::helper('udropship')->unserialize($conf);

for($i = 0; $i < count($configValue); $i++) {
    $configValue[$i]['notify_email'] = '0';
}

$installer->getConnection()->insertOnDuplicate($installer->getTable('core/config_data'), array(
    "scope"		=>	"default",
    "scope_id"	=>	0,
    "path"		=>	"urma/general/statuses",
    "value"		=>	Mage::helper('udropship')->serialize($configValue)
));

try {
    $cacheTypes = Mage::app()->useCache();
    foreach ($cacheTypes as $type => $option) {
        Mage::app()->getCacheInstance()->cleanType($type);
    }
} catch (Exception $e) {
    Mage::log($e->getMessage());
}

