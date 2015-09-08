<?php
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Aby skorzystać z rabatu',
        'identifier'    => 'mypromotions-code-how-to-use',
		'content' => <<<EOD
<div class="code-how-to-use">Aby skorzystać z rabatu:</div>
<ol>
	<li>skopiuj poniższy kod rabatowy</li>
	<li>dodaj objęte rabatem produkty do koszyka</li>
	<li>przejdź do koszyka</li>
	<li>kliknij w link "Czy posiadasz bon rabatowy?"</li>
	<li>wprowadź swój kod rabatowy w pole bonu</li>
	<li>zatwierdź przyciskiem "Dodaj"</li>
</ol>
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