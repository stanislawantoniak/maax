<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$installer->run("ALTER TABLE review_detail ADD COLUMN recommend_product tinyint(1) DEFAULT '0'");
$installer->endSetup();
