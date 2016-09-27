<?php
/* no for standalone version
$vissaviStore =  Mage::app()->getStore('vissavi')->getId();
$modagoStore = Mage::app()->getStore('default')->getId();
$allStores = 0;

$blocksToRemove = Mage::getModel('cms/block')->getCollection();
$blocksToRemove->addStoreFilter($allStores);
$blocksToRemove->addFieldToFilter("identifier", array('in' => array("benefits-strip-modago")));
foreach($blocksToRemove as $blockToRemove) {
    $blockToRemove->delete();
}

$blocks = array(
    array(
        'title' => 'Pasek korzyści (modago.pl)',
        'identifier' => 'benefits-strip',
        'content' => <<<EOD
<div class="benefits">
    <div class="benefit-item">
        <a href="{{config path=" web/secure/base_url"}}informacje-o-modago">
        <div class="benefit-text"
             style="background-image: url('/skin/frontend/modago/default/images/svg/benefits_transport.svg')">
            <h3>Darmowa dostawa</h3>

            <p>na&nbsp;99% produktów</p>
        </div>
        </a>
    </div>
    <div class="benefit-item">
        <a href="{{config path=" web/secure/base_url"}}informacje-o-modago">
        <div class="benefit-text"
             style="background-image: url('/skin/frontend/modago/default/images/svg/benefits_return.svg')">
            <h3>Darmowy zwrot</h3>

            <p>w&nbsp;ciągu 30&nbsp;dni</p>
        </div>
        </a>
    </div>
    <div class="benefit-item">
        <a href="{{config path=" web/secure/base_url"}}informacje-o-modago">
        <div class="benefit-text"
             style="background-image: url('/skin/frontend/modago/default/images/svg/benefits_shops.svg')">
            <h3>Wiele sklepów</h3>

            <p>jeden koszyk</p>
        </div>
        </a>
    </div>
    <div class="benefit-item">
        <a href="{{config path=" web/secure/base_url"}}mypromotions">
        <div class="benefit-text"
             style="background-image: url('/skin/frontend/modago/default/images/svg/benefits_discounts.svg')">
            <h3>Specjalne rabaty</h3>

            <p>
                <a href="{{config path=" web/secure/base_url"}}mypromotions">dowiedz&nbsp;się więcej&nbsp;>></a>
            </p>
        </div>
        </a>
    </div>
</div>
EOD
    ,
        'is_active' => 1,
        'stores' => $modagoStore
    ),
    array(
        'title' => 'Pasek korzyści (vissavi)',
        'identifier' => 'benefits-strip',
        'content' => <<<EOD
<div class="benefits">
	<div class="benefit-item">
	    <div class="benefit-text" style="background-image: url('/skin/frontend/modago/vissavi/images/svg/benefits_transport.svg')">
	        <h3>Darmowa dostawa</h3>
	        <p>na&nbsp;wszysktie&nbsp;produkty</p>
	    </div>
	</div>
	<div class="benefit-item">
	    <div class="benefit-text" style="background-image: url('/skin/frontend/modago/vissavi/images/svg/about_delivery_quick.svg')">
	        <h3>Szybka wysyłka</h3>
	        <p>w ciągu 24h</p>
	    </div>
	</div>
	<div class="benefit-item">
	    <div class="benefit-text" style="background-image: url('/skin/frontend/modago/vissavi/images/svg/benefits_return.svg')">
	        <h3>Darmowy zwrot</h3>
	        <p>w&nbsp;ciągu 14&nbsp;dni</p>
	    </div>
	</div>
	<div class="benefit-item">
	    <div class="benefit-text" style="background-image: url('/skin/frontend/modago/vissavi/images/svg/about_promotions.svg')">
	        <h3>Specjalne rabaty</h3>
	        <p>
		        <a href="#">dla&nbsp;klientów&nbsp;e-sklepu</a>
	        </p>
	    </div>
	</div>
</div>
EOD
    ,
        'is_active' => 1,
        'stores' => $vissaviStore
    ),
    array(
        'title' => 'Pasek korzyści (default)',
        'identifier' => 'benefits-strip',
        'content' => <<<EOD
<div class="benefits">
	<div class="benefit-item">
	    <div class="benefit-text" style="background-image: url('/skin/frontend/modago/vissavi/images/svg/benefits_transport.svg')">
	        <h3>Darmowa dostawa</h3>
	        <p>na&nbsp;wszysktie&nbsp;produkty</p>
	    </div>
	</div>
	<div class="benefit-item">
	    <div class="benefit-text" style="background-image: url('/skin/frontend/modago/vissavi/images/svg/about_delivery_quick.svg')">
	        <h3>Szybka wysyłka</h3>
	        <p>w ciągu 24h</p>
	    </div>
	</div>
	<div class="benefit-item">
	    <div class="benefit-text" style="background-image: url('/skin/frontend/modago/vissavi/images/svg/benefits_return.svg')">
	        <h3>Darmowy zwrot</h3>
	        <p>w&nbsp;ciągu 14&nbsp;dni</p>
	    </div>
	</div>
	<div class="benefit-item">
	    <div class="benefit-text" style="background-image: url('/skin/frontend/modago/vissavi/images/svg/about_promotions.svg')">
	        <h3>Specjalne rabaty</h3>
	        <p>
		        <a href="#">dla&nbsp;klientów&nbsp;e-sklepu</a>
	        </p>
	    </div>
	</div>
</div>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStores
    )
);


foreach ($blocks as $data) {
    $collection = Mage::getModel('cms/block')->getCollection();
    $collection->addStoreFilter($data['stores']);
    $collection->addFieldToFilter("identifier", $data["identifier"]);
    $block = $collection->getFirstItem();

    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData, $data);
    }
    $block->setData($data)->save();
}
*/