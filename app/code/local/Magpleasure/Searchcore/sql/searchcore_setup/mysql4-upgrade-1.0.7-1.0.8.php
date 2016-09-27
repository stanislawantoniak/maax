<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

$installer = $this;
$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('mp_search_result')}
   CHANGE `relevance` `relevance` decimal(6,6) default '0.0000' NOT NULL;

");


$installer->endSetup();