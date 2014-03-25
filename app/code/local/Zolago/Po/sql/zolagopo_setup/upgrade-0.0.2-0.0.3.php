<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->setConfigData('udropship/vendor/portal_show_totals', true);

$installer->endSetup();
