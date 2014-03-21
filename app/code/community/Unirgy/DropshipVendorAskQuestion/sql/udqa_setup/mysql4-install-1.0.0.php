<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    DropshipVendorAskQuestion
 * @copyright  Copyright (c) 2011-2012 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

$this->startSetup();

$this->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('udqa/question')}` (
`question_id` int(10) unsigned NOT NULL auto_increment,
`question_status` tinyint(1) NOT NULL DEFAULT '1',
`answer_status` tinyint(1) NOT NULL DEFAULT '1',
`product_id` int(10) unsigned DEFAULT NULL,
`shipment_id` int(10) unsigned DEFAULT NULL,
`customer_id` int(10) unsigned DEFAULT NULL,
`vendor_id` int(10) unsigned DEFAULT NULL,
`question_date` datetime NOT NULL default '0000-00-00 00:00:00',
`answer_date` datetime NOT NULL default '0000-00-00 00:00:00',
`customer_name` varchar(255) NOT NULL default '',
`customer_email` varchar(255) NOT NULL default '',
`question_text` TEXT DEFAULT NULL,
`answer_text` TEXT DEFAULT NULL,
`visibility`  tinyint(1) NOT NULL DEFAULT '0',
`is_customer_notified` tinyint(1) NOT NULL DEFAULT '0',
`is_vendor_notified` tinyint(1) NOT NULL DEFAULT '0',
`is_admin_question_notified` tinyint(1) NOT NULL DEFAULT '0',
`is_admin_answer_notified` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY  (`question_id`),
KEY `IDX_UDQA_QUESTION_STATUS` (`question_status`),
KEY `IDX_UDQA_ANSWER_STATUS` (`answer_status`),
KEY `IDX_UDQA_CUSTOMER_ID` (`customer_id`),
KEY `IDX_UDQA_VENDOR_ID` (`vendor_id`),
KEY `IDX_UDQA_QUESTION_DATE` (`answer_date`,`question_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$this->endSetup();