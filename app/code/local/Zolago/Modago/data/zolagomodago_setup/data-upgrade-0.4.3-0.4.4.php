<?php

$cms = array(
    array(
        'title'         => 'Account empty order history',
        'identifier'    => 'account-order-history-empty',
        'content'       =>
            <<<EOD
<section>
	<div id="account-order-history-empty" class="bg-w main">
		    {{block type="zolagomodago/sales_order_history_text" name="sales.order.history.text"}}		       
			<p>Sprawdź nasze <a href="#promocje" class="underline">promocje</a> już teraz.</p>
			<a id="back" class="button button-third large pull-left">Wróć</a>
	</div>
</section>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'Account empty order history text',
        'identifier'    => 'account-order-history-empty-text',
        'content'       =>
            <<<EOD
			<p>Nie masz jeszcze zamówień? Niemożliwe!</p>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    )
);

foreach ($cms as $data) {
    $block = Mage::getModel('cms/block')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData,$data);
    }

    $block->setData($data)->save();
}
$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);


// Customer Phone Attribute
$setup->addAttribute('customer', 'phone', array(
	'input'         => 'text',
	'type'          => 'varchar',
	'label'         => 'Phone',
	'visible'       => 1,
	'required'      => 0,
	'user_defined'  => 1
));

$setup->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'phone',
	'999'  //sort_order
);

$oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'phone');
$oAttribute->setData('used_in_forms', array('adminhtml_customer'));
$oAttribute->save();

// Customer SMS agreement Attribute
$setup->addAttribute('customer', 'sms_agreement', array(
	'input'         => 'select',
	'type'          => 'int',
	'source'        => 'eav/entity_attribute_source_boolean',
	'label'         => 'SMS agreement',
	'visible'       => 1,
	'required'      => 0,
	'user_defined'  => 1
));

$setup->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'sms_agreement',
	'999'  //sort_order
);

$oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'sms_agreement');
$oAttribute->setData('used_in_forms', array('adminhtml_customer'));
$oAttribute->save();

$setup->endSetup();
