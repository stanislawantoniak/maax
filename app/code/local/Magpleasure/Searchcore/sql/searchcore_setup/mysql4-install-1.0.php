<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

$installer = $this;

$installer->startSetup();


$installer->run("

DROP TABLE IF EXISTS {$this->getTable('mp_search_query')};
DROP TABLE IF EXISTS {$this->getTable('mp_search_type')};

CREATE TABLE IF NOT EXISTS {$this->getTable('mp_search_type')} (
  `type_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type_code` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`type_id`),
  INDEX `MPSEARCH_TYPE_CODE` (`type_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('mp_search_query')} (
  `query_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Query ID',
  `query_text` varchar(255) DEFAULT NULL COMMENT 'Query text',
  `num_results` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Num results',
  `popularity` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Popularity',
  `redirect` varchar(255) DEFAULT NULL COMMENT 'Redirect',
  `synonym_for` varchar(255) DEFAULT NULL COMMENT 'Synonym for',
  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store ID',
  `display_in_terms` smallint(6) NOT NULL DEFAULT '1' COMMENT 'Display in terms',
  `is_active` smallint(6) DEFAULT '1' COMMENT 'Active status',
  `is_processed` smallint(6) DEFAULT '0' COMMENT 'Processed status',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated at',
  PRIMARY KEY (`query_id`),
  KEY `IDX_MP_SEARCH_QUERY_QUERY_TEXT_STORE_ID_POPULARITY` (`query_text`,`store_id`,`popularity`),
  KEY `IDX_MP_SEARCH_QUERY_STORE_ID` (`store_id`),
  KEY `IDX_MP_SEARCH_QUERY_SYN_STORE_ID` (`query_text`,`synonym_for`),
  CONSTRAINT `FK_MP_SEARCH_QUERY_STORE` FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core_store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    ");


$installer->endSetup(); 