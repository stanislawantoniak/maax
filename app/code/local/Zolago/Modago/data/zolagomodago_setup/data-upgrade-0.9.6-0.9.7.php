<?php
//remove old blocks (just to be sure that everything is set up right
$blocksToRemove = Mage::getModel('cms/block')->getCollection();
$blocksToRemove->addFieldToFilter("identifier", array('in' => array("checkout-right-column-step-1-guest")));
foreach($blocksToRemove as $blockToRemove) {
    $blockToRemove->delete();
}
$blocksToRemoveLogin = Mage::getModel('cms/block')->getCollection();
$blocksToRemoveLogin->addFieldToFilter("identifier", array('in' => array("checkout-right-column-step-1")));
foreach($blocksToRemoveLogin as $blockToRemove) {
    $blockToRemove->delete();
}
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Checkout | Right column | Step 1 | Guest',
        'identifier'    => 'checkout-right-column-step-1-guest',
        'content'       => <<<EOD
<div class="sidebar-second col-lg-3 col-md-4 col-sm-4 col-xs-12 col-lg-push-9 col-md-push-8 col-sm-push-8 hidden-xs">
	<div class="main bg-w">
		<div class="checkout-sidebar-second-list-title">
			Kupując w&nbsp;{{config path="general/store_information/name"}} otrzymujesz:
		</div>
		<ul class="checkout-sidebar-second-ul">
			<li class="icon-box  left-icon design-2 animation-2">
				<div class="icon"><i class="fa fa-repeat" style=""></i></div>
				<div class="icon-content">
					<h3>30 dniowy</h3>
					<div class="icon-text">darmowy zwrot</div>
				</div>
			</li>
			<li class="icon-box  left-icon design-2 animation-2">
				<div class="icon"><i class="fa fa-truck fa-mirrored" style=""></i></div>
				<div class="icon-content">
					<h3>Błyskawiczną</h3>
					<div class="icon-text">wysyłkę</div>
				</div>
			</li>
			<li class="icon-box  left-icon design-2 animation-2">
				<div class="icon"><i class="fa fa-credit-card" style=""></i></div>
				<div class="icon-content">
					<h3>Wygodne</h3>
					<div class="icon-text">formy płatności</div>
				</div>
			</li>
			<li class="icon-box  left-icon design-2 animation-2">
				<div class="icon"><i class="fa fa-lock" style=""></i></div>
				<div class="icon-content">
					<h3>Bezpieczne</h3>
					<div class="icon-text">zakupy</div>
				</div>
			</li>
		</ul>
		<div class="checkout-sidebar-second-list-title">
			Załóż konto aby&nbsp;mieć:
		</div>
		<ul class="checkout-sidebar-second-ul">
			<li class="icon-box  left-icon design-2 animation-2">
				<div class="icon"><i class="fa fa-list-alt" style=""></i></div>
				<div class="icon-content">
					<h3>Wgląd w status</h3>
					<div class="icon-text">zamówień i zwrotów</div>
				</div>
			</li>
			<li class="icon-box  left-icon design-2 animation-2">
				<div class="icon"><i class="fa fa-shopping-bag" style=""></i></div>
				<div class="icon-content">
					<h3>Ułatwiony proces</h3>
					<div class="icon-text">składania zamówień</div>
				</div>
			</li>
			<li class="icon-box  left-icon design-2 animation-2">
				<div class="icon"><i class="fa fa-percent" style=""></i></div>
				<div class="icon-content">
					<h3>Dodatkowe</h3>
					<div class="icon-text">rabaty i promocje</div>
				</div>
			</li>
		</ul>
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

$cmsNavigationBlocksLogin = array(
    array(
        'title'         => 'Checkout | Right column | Step 1 | Logged in',
        'identifier'    => 'checkout-right-column-step-1',
        'content'       => <<<EOD
<div class="sidebar-second col-lg-3 col-md-4 col-sm-4 col-xs-12 col-lg-push-9 col-md-push-8 col-sm-push-8 hidden-xs">
    <div class="main bg-w">
        <div class="checkout-sidebar-second-list-title">
            Kupując w&nbsp;{{config path="general/store_information/name"}} otrzymujesz:
        </div>
        <ul class="checkout-sidebar-second-ul">
            <li class="icon-box  left-icon design-2 animation-2">
				<div class="icon"><i class="fa fa-repeat" style=""></i></div>
				<div class="icon-content">
					<h3>30 dniowy</h3>
					<div class="icon-text">darmowy zwrot</div>
				</div>
			</li>
			<li class="icon-box  left-icon design-2 animation-2">
				<div class="icon"><i class="fa fa-truck fa-mirrored" style=""></i></div>
				<div class="icon-content">
					<h3>Błyskawiczną</h3>
					<div class="icon-text">wysyłkę</div>
				</div>
			</li>
			<li class="icon-box  left-icon design-2 animation-2">
				<div class="icon"><i class="fa fa-credit-card" style=""></i></div>
				<div class="icon-content">
					<h3>Wygodne</h3>
					<div class="icon-text">formy płatności</div>
				</div>
			</li>
			<li class="icon-box  left-icon design-2 animation-2">
				<div class="icon"><i class="fa fa-lock" style=""></i></div>
				<div class="icon-content">
					<h3>Bezpieczne</h3>
					<div class="icon-text">zakupy</div>
				</div>
			</li>
        </ul>
    </div>
</div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    )
);

foreach ($cmsNavigationBlocksLogin as $data) {
    $block = Mage::getModel('cms/block')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData,$data);
    }
    $block->setData($data)->save();
}