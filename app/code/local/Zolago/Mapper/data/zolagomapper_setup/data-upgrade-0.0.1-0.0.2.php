<?php
/**
 * Update type of condition combine from
 * 'rule/condition_combine' into 'zolagomapper/mapper_condition_combine'
 * because previously condition don't show correctly
 * 'mapper_condition_product'
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagomapper/mapper');

$installer->getConnection()->query("
UPDATE `{$table}`
SET `conditions_serialized` = REPLACE(`conditions_serialized`, 's:4:\"type\";s:22:\"rule/condition_combine\";', 's:4:\"type\";s:37:\"zolagomapper/mapper_condition_combine\";')
WHERE `conditions_serialized` LIKE '%rule/condition_combine%'
");

$installer->endSetup();
