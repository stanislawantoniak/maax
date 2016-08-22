<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_GROUPEDPRODUCTPROMOTIONS
 * @copyright  Copyright (c) 2013 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */


$this->startSetup();

$this->run("

ALTER TABLE  {$this->getTable('itoris_groupedproductpromotions_rules')}
ADD `price_method` int unsigned null;
ALTER TABLE  {$this->getTable('itoris_groupedproductpromotions_rules')}
ADD `discount_promoset` float unsigned null;
ALTER TABLE  {$this->getTable('itoris_groupedproductpromotions_rules')}
ADD `code` smallint(5) unsigned null;
ALTER TABLE  {$this->getTable('itoris_groupedproductpromotions_rules')}
ADD `fixed_price` float unsigned null;
");

$this->endSetup();
?>