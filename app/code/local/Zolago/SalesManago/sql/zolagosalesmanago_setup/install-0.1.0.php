<?php
$installer = $this;
 
$installer->startSetup();

$installer->addAttribute('customer', 'salesmanago_cart_event_id', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'SALESmanago Cart Event ID',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'default' => '0',
    'visible_on_front' => 0
));
Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'salesmanago_cart_event_id')
    ->setData('used_in_forms', array('adminhtml_customer'))
    ->save();
 
$installer->endSetup();
