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

$lists = Mage::getModel('fidelitas/lists')->getCollection();
$previous = array();
foreach ($lists as $list) {

    if ($list->getStoreId() == 0)
        continue;

    if (in_array($list->getStoreId(), $previous))
        continue;

    $previous[] = $list->getStoreId();

    if ($list->getData('purpose') == 'auto') {
        $list->setData('auto', 1)->save();
    }

    if ($list->getData('purpose') == 'auto') {
        $list->setData('purpose', 'regular')->save();
    }
    Mage::getModel('fidelitas/lstores')
            ->setData(array('store_id' => $list->getStoreId(), 'list_id' => $list->getId()))
            ->save();
}

