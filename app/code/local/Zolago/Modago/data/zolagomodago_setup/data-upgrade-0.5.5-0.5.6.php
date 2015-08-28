<?php
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Pasek korzyści',
        'identifier'    => 'benefits-strip-modago',
        'content'       => <<<EOD
<div class="benefits">
	<div class="benefit-item">
	    <div class="benefit-text" style="background-image: url('/skin/frontend/modago/default/images/benefits/benefits_transport.png')">
	        <h3>Darmowa dostawa</h3>
	        <p>na&nbsp;99% produktów</p>
	    </div>
	</div>
	<div class="benefit-item">
	    <div class="benefit-text" style="background-image: url('/skin/frontend/modago/default/images/benefits/benefits_return.png')">
	        <h3>Darmowy zwrot</h3>
	        <p>w&nbsp;ciągu 30&nbsp;dni</p>
	    </div>
	</div>
	<div class="benefit-item">
	    <div class="benefit-text" style="background-image: url('/skin/frontend/modago/default/images/benefits/benefits_shops.png')">
	        <h3>Wiele sklepów</h3>
	        <p>jeden koszyk</p>
	    </div>
	</div>
	<div class="benefit-item">
	    <div class="benefit-text" style="background-image: url('/skin/frontend/modago/default/images/benefits/benefits_rabat.png')">
	        <h3>Specjalne rabaty</h3>
	        <p>
		        <a href="#">dowiedz&nbsp;się więcej&nbsp;>></a>
	        </p>
	    </div>
	</div>
</div>
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