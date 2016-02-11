<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$writeConnection = $resource->getConnection('core_write');

$sizeTableTable = $resource->getTableName("zolagosizetable/sizetable");

//Default values
$query = "SELECT * FROM {$sizeTableTable};";
$results = $readConnection->fetchAll($query);

if (!empty($results)) {
    $toUpdate = array();
    foreach ($results as $result) {
        if (!is_serialized($result["default_value"])) {
            $toUpdate[] = $result;
        }
    }

    if (!empty($toUpdate)) {
        foreach ($toUpdate as $toUpdateItem) {
            $defaultValue = serialize(array("C" => $toUpdateItem["default_value"]));
            $queryUpdate = sprintf("UPDATE %s SET default_value='%s' WHERE sizetable_id=%s", $sizeTableTable, $defaultValue, (int)$toUpdateItem["sizetable_id"]);

            $writeConnection->query($queryUpdate);
        }
    }
}
unset($toUpdate, $result, $toUpdateItem, $queryUpdate);


//Scope values
$sizeTableScopeTable = $resource->getTableName("zolagosizetable/sizetable_scope");
$query = "SELECT * FROM {$sizeTableScopeTable};";
$results = $readConnection->fetchAll($query);

if (!empty($results)) {
    $toUpdate = array();
    foreach ($results as $result) {
        if (!is_serialized($result["value"])) {
            $toUpdate[] = $result;
        }
    }

    if (!empty($toUpdate)) {
        foreach ($toUpdate as $toUpdateItem) {
            $value = serialize(array("C" => $toUpdateItem["value"]));
            $queryUpdate = sprintf("UPDATE %s SET value='%s' WHERE sizetable_id=%s AND store_id=%s",$sizeTableScopeTable, $value,(int)$toUpdateItem["sizetable_id"],(int)$toUpdateItem["store_id"]);

            $writeConnection->query($queryUpdate);
        }
    }
}

$installer->endSetup();