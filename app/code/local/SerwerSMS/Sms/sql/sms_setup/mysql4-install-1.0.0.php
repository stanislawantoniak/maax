<?php
/**
 *
 *	@copyright  Copyright (c) 2012-2013 SerwerSMS.pl
 *	http://www.serwersms.pl
 */

$installer = $this;
 
$installer->startSetup();
 
$installer->run("
 
-- DROP TABLE IF EXISTS {$this->getTable('serwersms_sms')};
CREATE TABLE {$this->getTable('serwersms_sms')} (
    `id` int(11) unsigned NOT NULL auto_increment,
    `data` DATETIME NOT NULL,
    `smsid` VARCHAR(20) NOT NULL,
    `numer` VARCHAR(40) NOT NULL,
    `nadawca` VARCHAR(40) NOT NULL,
    `typ` VARCHAR(5) NOT NULL,
    `status` VARCHAR(20) NOT NULL,
    `raport` VARCHAR(50) NOT NULL,
    `tresc` TEXT NOT NULL,
    `powod` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
-- DROP TABLE IF EXISTS {$this->getTable('serwersms_odpowiedzi')};
CREATE TABLE {$this->getTable('serwersms_odpowiedzi')} (
    `id_odp` int(11) unsigned NOT NULL auto_increment,
    `data` DATETIME NOT NULL,
    `numer` VARCHAR(20) NOT NULL,
    `wiadomosc` TEXT NOT NULL,
  PRIMARY KEY (`id_odp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
 
$installer->endSetup();
