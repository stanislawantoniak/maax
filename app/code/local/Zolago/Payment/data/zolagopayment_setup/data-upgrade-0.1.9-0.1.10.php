<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagopayment/provider');

$installer->getConnection()->truncateTable($table);

$installer->getConnection()->query("
INSERT INTO `". $table ."` (`provider_id`, `code`, `is_active`, `name`, `created_at`, `updated_at`, `type`) VALUES
(51, 'pbs', 1, 'Płacę z PBS', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway'),
(52, 'orange', 1, 'Płacę z Orange', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway'),
(53, 'blik', 1, 'BLIK', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway'),
(54, 'bps', 1, 'Banki Spółdzielcze', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway'),
(55, 'bplus', 1, 'Płacę z Plus Bank', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway'),
(56, 'bgetin', 1, 'Getin Bank PBL', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway'),
(57, 'noblepay', 1, 'Getin Bank PBL', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway'),
(58, 'ideacloud', 1, 'Idea Cloud', '2016-11-23 23:00:00', '2016-11-23 23:00:00', 'gateway');

");

$installer->endSetup();
