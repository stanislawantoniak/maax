<?php
//recreate blocks with correct scopes
$allStores = 0;
$modagoStore = Mage::app()->getStore('default')->getId();

//remove old blocks (just to be sure that everything is set up right
$blocksToRemove = Mage::getModel('cms/block')->getCollection();
$blocksToRemove->addStoreFilter($allStores);
$blocksToRemove->addFieldToFilter("identifier", array('in' => array("notification-settings-general-subscription")));
foreach($blocksToRemove as $blockToRemove) {
	$blockToRemove->delete();
}

$blocksToCreate = array(
	array(
		'title' => 'Zgoda na newsletter konto klienta (Modago.pl)',
		'identifier' => 'notification-settings-general-subscription',
		'content' => "Wyrażam zgodę na przetwarzanie moich danych osobowych, w szczególności imienia, nazwiska, adresu zamieszkania, adresu poczty elektronicznej w celu przetwarzania ofert marketingowych i informacji handlowych przez Modago.pl",
		'is_active' => 1,
		'stores' => $modagoStore
	),
	array(
		'title' => 'Zgoda na newsletter konto klienta (default)',
		'identifier' => 'notification-settings-general-subscription',
		'content' => "Wyrażam zgodę na przetwarzanie moich danych osobowych, w szczególności imienia, nazwiska, adresu zamieszkania, adresu poczty elektronicznej w celu przetwarzania ofert marketingowych i informacji handlowych przez {{config path=\"general/store_information/name\"}}",
		'is_active' => 1,
		'stores' => $allStores
	)
);

foreach ($blocksToCreate as $blockData) {
	$collection = Mage::getModel('cms/block')->getCollection();
	$collection->addStoreFilter($blockData['stores']);
	$collection->addFieldToFilter('identifier',$blockData["identifier"]);
	$currentBlock = $collection->getFirstItem();

	if ($currentBlock->getBlockId()) {
		$oldBlock = $currentBlock->getData();
		$blockData = array_merge($oldBlock, $blockData);
	}
	$currentBlock->setData($blockData)->save();
}