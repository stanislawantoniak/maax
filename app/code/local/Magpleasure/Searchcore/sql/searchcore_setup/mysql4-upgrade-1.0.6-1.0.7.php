<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

$installer = $this;
$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('mp_search_index_word')};
DROP TABLE IF EXISTS {$this->getTable('mp_search_result')};
DROP TABLE IF EXISTS {$this->getTable('mp_search_index')};
DROP TABLE IF EXISTS {$this->getTable('mp_search_word')};

CREATE TABLE IF NOT EXISTS {$this->getTable('mp_search_index')} (
   `index_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `entity_id` int(10) unsigned NOT NULL,
   `type_id` smallint(5) unsigned NOT NULL,
   `store_id` smallint(5) unsigned NOT NULL,
   PRIMARY KEY (`index_id`),
   UNIQUE KEY (`entity_id`,`type_id`,`store_id`),
   KEY `type_id` (`type_id`, `store_id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('mp_search_result')} (
   `query_id` int(10) unsigned NOT NULL,
   `index_id` int(10) unsigned NOT NULL,
   `relevance` decimal(10,4) NOT NULL DEFAULT '0.0000',
   PRIMARY KEY (`query_id`,`index_id`),
   KEY (`query_id`),
   KEY (`index_id`),
   CONSTRAINT FOREIGN KEY (`query_id`) REFERENCES {$this->getTable('mp_search_query')} (`query_id`) ON DELETE CASCADE ON UPDATE CASCADE,
   CONSTRAINT FOREIGN KEY (`index_id`) REFERENCES {$this->getTable('mp_search_index')} (`index_id`) ON DELETE CASCADE ON UPDATE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('mp_search_word')} (
   `word_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
   `word` varchar(255) NOT NULL,
   PRIMARY KEY (`word_id`),
   UNIQUE KEY (`word`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 CREATE TABLE IF NOT EXISTS {$this->getTable('mp_search_index_word')} (
   `index_id` int(10) unsigned NOT NULL,
   `word_id` bigint(255) unsigned NOT NULL,
   `location` int(10) unsigned NOT NULL,
   CONSTRAINT FOREIGN KEY (`index_id`) REFERENCES {$this->getTable('mp_search_index')} (`index_id`) ON DELETE CASCADE ON UPDATE CASCADE,
   CONSTRAINT FOREIGN KEY (`word_id`) REFERENCES {$this->getTable('mp_search_word')} (`word_id`) ON DELETE CASCADE ON UPDATE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");


$installer->endSetup();