<?php
$this->startSetup();

$this->run('UPDATE '.$this->getTable('catalog/eav_attribute').' SET column_attribute_order = 100 WHERE column_attribute_order = 0');
$this->endSetup();