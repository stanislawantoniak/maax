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
            $queryUpdate = "UPDATE {$sizeTableTable} SET default_value = '{$defaultValue}' WHERE sizetable_id = " . (int)$toUpdateItem["sizetable_id"];
            $writeConnection->query($queryUpdate);
        }
    }
}
unset($toUpdate, $result, $toUpdateItem);


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
            $queryUpdate = "UPDATE {$sizeTableScopeTable} SET value = '{$value}' WHERE sizetable_id = " . (int)$toUpdateItem["sizetable_id"]. " AND store_id=". (int)$toUpdateItem["store_id"];
            $writeConnection->query($queryUpdate);
        }
    }
}



function is_serialized($str)
{
    $data = @unserialize($str);
    if ($str === 'b:0;' || $data !== false)
        return true;

    return false;
}

$installer->endSetup();