<?php

$cms = array(
	array(
		'title'         => 'Contact (default)',
		'identifier'    => 'help-contact',
		'content'       =>
			<<<EOD
<div id="contact-container" class="container-fluid bg-w">
	<h1>KONTAKT</h1>
	<p>
		Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent viverra, erat quis vulputate pulvinar, nulla ligula ullamcorper velit, vel blandit sem nunc nec lectus.
	</p>
	<p>
		Quisque tincidunt lacus at enim egestas posuere at non nisl. Nunc placerat leo nec purus vehicula, ut cursus eros aliquet. Integer at consectetur quam.
	</p>
	<p>
		Maecenas sed hendrerit ligula, in eleifend tortor. Sed ut maximus sapien, in interdum est. Aenean vehicula odio at turpis egestas finibus.
		<ul>
			<li>Duis pulvinar, diam quis tempor dignissim, tellus turpis eleifend elit, vitae tristique risus nulla non tortor. </li>
			<li> Aenean pharetra posuere nibh, porta blandit ante blandit accumsan.</li>
			<li>Curabitur maximus est a velit dapibus consectetur.</li>
			<li>Nunc semper id lacus at blandit. Vestibulum at ante quis justo mollis lobortis. </li>
		</ul>
	</p>
	<p>
		Donec a mollis magna, sed interdum mi. Curabitur volutpat scelerisque est sit amet fringilla. In hac habitasse platea dictumst. Ut id egestas quam, at blandit dolor. Nam elementum odio vitae justo suscipit tincidunt. Aenean est metus, fringilla a gravida sed, ultricies vitae justo. Vivamus leo velit, sagittis eu justo quis, pulvinar ornare velit. Sed semper quis nibh et iaculis.
	</p>
{{block type="udqa/product_question" template="catalog/product/question.phtml"}}
</div>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	)
);

foreach ($cms as $blockData) {
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