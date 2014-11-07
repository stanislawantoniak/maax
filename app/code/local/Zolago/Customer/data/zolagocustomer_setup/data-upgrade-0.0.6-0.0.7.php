<?php

$aAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'phone');
$aAttribute->setData('used_in_forms', array('customer_account_edit','adminhtml_customer'));
$aAttribute->save();

$bAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'sms_agreement');
$bAttribute->setData('used_in_forms', array('customer_account_edit','adminhtml_customer'));
$bAttribute->save();
