<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('catalog/eav_attribute'), "column_width", "VARCHAR( 255 ) NULL");

$installer->endSetup();