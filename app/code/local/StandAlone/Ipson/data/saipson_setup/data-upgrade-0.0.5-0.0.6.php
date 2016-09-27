<?php

$blocks = array(
	array(
		'title'         => 'Normal Login Page | Continue',
		'identifier'    => 'login-continue-normal',
		'content'       =>
			<<<EOD
                    <section class="section">
			<header class="title-section">
				<h2>Nie masz konta?</h2>
			</header>
			<div class="register-padding-left">
				<div>Możesz je łatwo założyć, aby mieć:</div>
				<ul class="benefits">
					<li>dodatkowe rabaty i promocje</li>
					<li>uproszczony proces składania zamówień</li>
					<li>stały wzgląd w status zamówienia czy zwrotu</li>
				</ul>
			</div>
			<a title="Załóż konto" class="button button-primary large link pull-right" href="{{store url='customer/account/create'}}"><span><span>Załóż konto</span></span></a>		
		</section>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	),
	array(
		'title'         => 'Checkout Login Page | Continue',
		'identifier'    => 'login-continue-checkout',
		'content'       =>
			<<<EOD
                    <section class="section">
			<header class="title-section">
				<h2>Nie masz konta?</h2>
			</header>
			<ul class="benefits list bullet_01">
				<li>możesz je łatwo założyć przy składaniu zamówienia</li>
				<li>możesz zrobić zakupy bez rejestracji</li>
			</ul>
			<footer class="footer-section">
				<a title="Kontynuuj" class="button button-primary large link pull-right" href="{{store url='checkout/guest/continue'}}"><span><span>Kontynuuj</span></span></a>
			</footer>
		</section>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	),
	array(
		'title' => 'Rejestracja prawy blok',
		'identifier' => 'customer-register-right',
		'content' => <<<EOD
<div class="sidebar-second col-lg-3 col-md-4 col-sm-4 col-xs-12 col-lg-push-9 col-md-push-8 col-sm-push-8 hidden-xs">
    <div class="main bg-w">
        <div class="checkout-sidebar-second-list-title">
            Załóż konto aby&nbsp;mieć:
        </div>
        <ul class="checkout-sidebar-second-ul">
            <li class="icon-box  left-icon design-2 animation-2">
				<div class="icon"><i class="fa fa-percent" style=""></i></div>
				<div class="icon-content">
					<h3>Dodatkowe</h3>
					<div class="icon-text">rabaty i promocje</div>
				</div>
			</li>
            <li class="icon-box  left-icon design-2 animation-2">
				<div class="icon"><i class="fa fa-shopping-bag" style=""></i></div>
				<div class="icon-content">
					<h3>Uproszczony proces</h3>
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
		'is_active' => 1,
		'stores' => 0
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

