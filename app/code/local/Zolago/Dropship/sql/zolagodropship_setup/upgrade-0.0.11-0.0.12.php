<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

//1. konfiguracja: na poziomie sprzedawcy dodajemy flagę: indeksowanie przez google włączone/wyłączone,
// użyte metatagi googleoff,
// domyślnie indeksowanie jest włączone
$vendorTable = $this->getTable("udropship/vendor");

$installer->getConnection()->addColumn($vendorTable,
    "index_by_google", array(
        "type" => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        "comment" => "Index products by google",
        "nullable" => FALSE,
        "default" => TRUE
    ));

//2. na poziomie konfiguracji brandshopów w sprzedawcy dodajemy konfigurację:
// "indeksowanie przez google"- użyj konfiguracji sprzedawcy (domyślnie), wyłączone, włączone
$vendorBrandshopTable = $this->getTable("zolagodropship/vendor_brandshop");

$installer->getConnection()->addColumn($vendorBrandshopTable,
    "index_by_google", array(
        "type" => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        "comment" => "Index products by google",
        "nullable" => FALSE,
        "default" => FALSE
    ));

$installer->getConnection()
    ->addIndex(
        $vendorBrandshopTable,
        $installer->getIdxName('zolagodropship/vendor_brandshop', array('index_by_google')),
        array('index_by_google')
    );

$installer->endSetup();