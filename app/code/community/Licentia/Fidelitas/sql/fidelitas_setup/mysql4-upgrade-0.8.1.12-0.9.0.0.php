<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title Advanced Email and SMS Marketing Automation
 * @category Marketing
 * @package Licentia
 * @author Bento Vilas Boas <bento@licentia.pt>
 * @Copyright (c) 2012 Licentia - http://licentia.pt
 * @license Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 */
$installer = $this;
$installer->startSetup();

$installer->run("ALTER IGNORE TABLE `{$this->getTable('fidelitas/subscribers')}` ADD UNIQUE `unq_uid_list` (`uid`, `list`) ");
$installer->run("ALTER IGNORE TABLE `{$this->getTable('fidelitas/lists')}` ADD UNIQUE `unq_listnum` (`listnum`) ");
$installer->run("ALTER IGNORE TABLE `{$this->getTable('fidelitas/autoresponders')}` CHANGE COLUMN `lists_ids` `listnum` int(11) DEFAULT NULL ");
$installer->run("ALTER IGNORE TABLE `{$this->getTable('fidelitas/campaigns')}` CHANGE COLUMN `recurring_first_run` `recurring_first_run` datetime DEFAULT NULL");
$installer->run("ALTER IGNORE TABLE `{$this->getTable('fidelitas/campaigns')}` DROP COLUMN `end_method`");

$installer->endSetup();