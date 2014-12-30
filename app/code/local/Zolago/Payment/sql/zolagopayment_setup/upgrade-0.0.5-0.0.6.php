<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagopayment/provider');

$installer->getConnection()->query("UPDATE `". $table ."` SET `code` = 'ipko' WHERE `provider_id` = 4;");
$installer->getConnection()->query("UPDATE `". $table ."` SET `name` = 'Płacę z iPKO' WHERE `provider_id` = 4;");

$installer->endSetup();
