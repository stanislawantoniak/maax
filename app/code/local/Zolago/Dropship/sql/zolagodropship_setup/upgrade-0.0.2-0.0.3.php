<?php
/**
 * ZolagoOs LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   ZolagoOs
 * @package    ZolagoOs_OmniChannel
 * @copyright  Copyright (c) 2008-2009 ZolagoOs LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('zolago_vendor_preferences')}` (
`vendor_preferences_id` int(11) unsigned NOT NULL auto_increment,
`vendor_id` int(11) unsigned NOT NULL,
`company_name` varchar(255) NOT NULL,
`tax_no` varchar(50) default NULL,
`www` varchar(255) NOT NULL,
`contact_email` varchar(127) NOT NULL,
`contact_telephone` varchar(127) NOT NULL,
`executive_firstname` varchar(255) NOT NULL,
`executive_lastname` varchar(255) NOT NULL,
`executive_telephone` varchar(127) NOT NULL,
`executive_telephone_mobile` varchar(127) NOT NULL,
`administrator_firstname` varchar(255) NOT NULL,
`administrator_lastname` varchar(255) NOT NULL,
`administrator_telephone` varchar(127) NOT NULL,
`administrator_telephone_mobile` varchar(127) NOT NULL,

`rma_email` varchar(127) NOT NULL,
`rma_telephone` varchar(127) NOT NULL,

`rma_executive_firstname` varchar(255) NOT NULL,
`rma_executive_lastname` varchar(255) NOT NULL,
`rma_executive_telephone` varchar(127) NOT NULL,
`rma_executive_telephone_mobile` varchar(127) NOT NULL,
`rma_executive_email` varchar(127) NOT NULL,

PRIMARY KEY  (`vendor_preferences_id`),
KEY `FK_zolagodropship_vendor_preferences_vendor` (`vendor_id`),

CONSTRAINT `FK_zolagodropship_vendor_preferences_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `{$this->getTable('udropship_vendor')}` (`vendor_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");


$this->endSetup();