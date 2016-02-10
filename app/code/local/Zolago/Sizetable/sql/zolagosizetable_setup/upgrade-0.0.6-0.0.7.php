<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$defaultCollection = Mage::getModel("zolagosizetable/sizetable")->getCollection();
foreach ($defaultCollection as $defaultCollectionItem) {

    $resave = false;
    if (!is_serialized($defaultCollectionItem->getDefaultValue())) {
        $defaultCollectionItem->setData("default_value", serialize(array("C" => $defaultCollectionItem->getDefaultValue())));
    }
    $scopes = Mage::getModel("zolagosizetable/sizetable")
        ->load($defaultCollectionItem->getId())
        ->getScopes();

    $sizetableOfItem = $scopes->getData("sizetable");
    $postData = array();
    if (!empty($sizetableOfItem)) {
        foreach ($sizetableOfItem as $storeId => $sizetableItem) {
            if (!is_serialized($sizetableItem)) {
                $postData[$storeId]["C"] = $sizetableItem;
            }
        }
    }
    $defaultCollectionItem->setPostData($postData);

    //Stores
    try {
        $defaultCollectionItem->save();
    } catch (Exception $e) {
        Mage::logException($e);
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