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

$installer->run("ALTER TABLE `{$this->getTable('fidelitas/campaigns')}`ADD COLUMN `egoi_segments` varchar(255)");
$installer->run("ALTER TABLE `{$this->getTable('fidelitas/campaigns')}`ADD COLUMN `segments_origin` enum('store','egoi') DEFAULT 'store'");

$installer->endSetup();