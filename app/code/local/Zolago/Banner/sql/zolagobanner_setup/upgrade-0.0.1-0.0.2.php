<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


/**
 * Change is_active to status
 */
 $table = $installer->getConnection()
    ->dropColumn($installer->getTable('zolagobanner/banner'), "is_active");
$installer->endSetup();
