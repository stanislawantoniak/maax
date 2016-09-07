<?php

$blocks = array(
	array(
		'title'         => 'Benefits strip',
		'identifier'    => 'benefits-strip',
		'content'       =>
			<<<EOD
<div class="benefits">
    <div class="benefit-item">
        <a href="{{config path="web/secure/base_url"}}dostawa">
        <div class="benefit-text" style="background-image: url('/skin/frontend/rich/details/images/benefits_transport.jpg')">
            <h3>Darmowa dostawa</h3>
            <p>od&nbsp;99&nbsp;zł</p>
        </div>
        </a>
    </div>
    <div class="benefit-item">
        <a href="{{config path="web/secure/base_url"}}blog">
        <div class="benefit-text"
             style="background-image: url('/skin/frontend/rich/details/images/benefits_tips.jpg')">
            <h3>Porady fizjoterapeuty</h3>
            <p>dowiedz&nbsp;się więcej</p>
        </div>
        </a>
    </div>
    <div class="benefit-item">
        <a href="{{config path="web/secure/base_url"}}dofinansowanie">
        <div class="benefit-text"
             style="background-image: url('/skin/frontend/rich/details/images/benefits_financing.jpg')">
            <h3>Dofinansowanie</h3>
            <p>
                <a href="{{config path="web/secure/base_url"}}dofinansowanie">dowiedz&nbsp;się więcej</a>
            </p>
        </div>
        </a>
    </div>
    <div class="benefit-item">
        <a href="{{config path="web/secure/base_url"}}zwroty-i-reklamacje">
        <div class="benefit-text"
             style="background-image: url('/skin/frontend/rich/details/images/benefits_return.jpg')">
            <h3>Darmowy zwrot</h3>
             <p>w&nbsp;30&nbsp;dni</p>
        </div>
        </a>
    </div>
</div>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	)
);

foreach ($blocks as $blockData) {
	$collection = Mage::getModel('cms/block')->getCollection();
	$collection->addStoreFilter($blockData['stores']);
	$collection->addFieldToFilter('identifier', $blockData["identifier"]);
	$currentBlock = $collection->getFirstItem();

	if ($currentBlock->getBlockId()) {
		$oldBlock = $currentBlock->getData();
		$blockData = array_merge($oldBlock, $blockData);
	}
	$currentBlock->setData($blockData)->save();
}

