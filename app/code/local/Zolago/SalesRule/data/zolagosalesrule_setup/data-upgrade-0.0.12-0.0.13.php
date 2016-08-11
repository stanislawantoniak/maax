<?php

// installation privacy-settings-remember-me-description cms blocks
$cmsNavigationBlocks = array(
	array(
		'title'         => 'Account promotions newsletter confirmation popup',
		'identifier'    => 'mypromotions_newsletter_popup',
		'content'       => <<<EOD
<section class="mypromotions-cms">
	<div class="mypromotions-cms-title">
		Jeszcze tylko jeden kroczek...
	</div>
	<div class="mypromotions-cms-text">
		Aby zapisać się do newslettera, potrzebujemy jeszcze potwierdzenia adresu email.<br />
		Potwierdź adres, by zobaczyć kupony.
	</div>
	<ol class="mypromotions-cms-list">
		<li>Odbierz maila</li>
		<li>Kliknij w link w mailu</li>
		<li>Odśwież tę stronę klikając w poniższy przycisk</li>
	</ol>
	<a href="#" class="button button-primary large link" onclick="Mall.refresh();">
		Odśwież stronę
	</a>
</section>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	)
);

foreach ($cmsNavigationBlocks as $data) {
	$block = Mage::getModel('cms/block')->load($data['identifier']);
	if ($block->getBlockId()) {
		$oldData = $block->getData();
		$data = array_merge($oldData,$data);
	}
	$block->setData($data)->save();
}