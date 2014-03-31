<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->setConfigData('web/url/redirect_to_base', 0);
$installer->setConfigData('web/default/front', 'umicrosite');

$installer->endSetup();

