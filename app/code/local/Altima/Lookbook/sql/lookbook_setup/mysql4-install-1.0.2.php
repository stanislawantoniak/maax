<?php
/**
 * Altima Lookbook Free Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Altima
 * @package    Altima_LookbookFree
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('lookbook')};
CREATE TABLE {$this->getTable('lookbook')} (
  `lookbook_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `hotspots` text NOT NULL default '',
  `position` smallint(5) unsigned NOT NULL,
  `status` smallint(6) NOT NULL default '0',
  PRIMARY KEY (`lookbook_id`),
  KEY `IDX_LOOKBOOK_LOOKBOOK_ID` (`lookbook_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->setConfigData('lookbook/general/hotspot_icon/','default/hotspot-icon.png');

$installer->endSetup(); 