<?php
/**
 * Add Active Filter Label
 * Add Banner additional text field
 *
 * (Jako local vendor chcę definiować dodatkowy tekst i opis filtra w landing page kampanii)
 *
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;


$installer->startSetup();

$table = $installer->getTable('zolagocampaign/campaign');

$this->getConnection()
    ->addColumn($table, 'active_filter_label', array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "nullable" => false,
        "length" => 100,
        "comment" => "Active Filter Label"
    ));

$this->getConnection()
    ->addColumn($table, 'banner_text_info', array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "nullable" => false,
        "comment" => "Banner Text Description"
    ));


$installer->endSetup();
