<?php
/**
 * Set user to view
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$pricessizesView = $this->getTable("zolagocatalog/pricessizes");
$config  = Mage::getConfig()->getResourceConnectionConfig("default_setup");
$username = (string)$config->username;
$host = (string)$config->host;

$update = "GRANT SELECT ON {$pricessizesView} TO '{$username}'@'{$host}';";
Mage::log($update);
$installer->run($update);

$installer->endSetup();




