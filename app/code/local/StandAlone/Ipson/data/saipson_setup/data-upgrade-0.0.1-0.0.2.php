<?php
//remove old blocks (just to be sure that everything is set up right
$blocksToRemove = Mage::getModel('cms/block')->getCollection();
$blocksToRemove->addFieldToFilter("identifier", array('in' => array("standalone_footer_links_mobile")));
foreach($blocksToRemove as $blockToRemove) {
	$blockToRemove->delete();
}
$blocksToRemove = Mage::getModel('cms/block')->getCollection();
$blocksToRemove->addFieldToFilter("identifier", array('in' => array("standalone_footer_links_desc")));
foreach($blocksToRemove as $blockToRemove) {
	$blockToRemove->delete();
}

//recreate blocks with correct scopes
$allStores = 0;

$blocksToCreate = array(
	array(
		'title' => 'Standalone: Footer Links Mobile',
		'identifier' => 'standalone_footer_links_mobile',
		'content' =>
			<<<EOD
			<ul id="nav_footer_mobile">
				<li>
					<a href="#">
						Osługa Klienta <i class="fa fa-chevron-down"></i>
					</a>
					<ul>
						<li><a href="{{store direct_url='' _no_vendor='1'}}">Dostawa</a></li>
						<li><a href="{{store direct_url='' _no_vendor='1'}}">Zwroty i reklamacje</a></li>
						<li><a href="{{store direct_url=' _no_vendor='1'}}">Raty</a></li>
						<li><a href="{{store direct_url=' _no_vendor='1'}}">Dofinansowanie</a></li>
						<li><a href="{{store direct_url=' _no_vendor='1'}}">Punkt odbioru</a></li>
					</ul>
				</li>
				<li>
					<a href="#">
						INFORMACJE <i class="fa fa-chevron-down"></i>
					</a>
					<ul>
						<li><a href="{{store direct_url='o-nas' _no_vendor='1'}}">O Nas</a></li>
						<li><a href="{{store direct_url='' _no_vendor='1'}}">Regulamin</a></li>
						<li><a href="{{store direct_url='' _no_vendor='1'}}">Polityka prywatności</a></li>
						<li><a href="{{store direct_url='' _no_vendor='1'}}">Kariera</a></li>
					</ul>
				</li>
				<li>
					<a href="#">
						TWOJE KONTO <i class="fa fa-chevron-down"></i>
					</a>
					<ul>
						<li><a href="{{store direct_url='customer/account' _no_vendor='1'}}">Zaloguj się</a></li>
						<li><a href="{{store url='sales/order/process'}}">Twoje zamówienia</a></li>
						<li><a href="{{store direct_url='wishlist' _no_vendor='1'}}">Przechowalnie</a></li>
					</ul>
				</li>
				<li>
					<a href="#">
						KONTAKT <i class="fa fa-chevron-down"></i>
					</a>
					<ul>
						<li><a href="mailto:contacr@ipson.com" class="contact-us-link">Napisz do nas</a></li>
						<li><a href="tel:+800180022" class="contact-us-link">Bezpłatna infolinia: 800 180 022</a></li>
						<li>Godziny pracy: pon. - piat. 9:00 - 17:00</li>
					</ul>
				</li>
			</ul>
EOD
	,
		'is_active' => 1,
		'stores' => $allStores
	),
	array(
		'title' => 'Standalone: Footer Links Desc',
		'identifier' => 'standalone_footer_links_desc',
		'content' =>
			<<<EOD
			<div class="col-xs-6 col-sm-3 col-md-3">
				<div class="wpb_wrapper">
					<div class="wpb_text_column wpb_content_element ">
						<div class="wpb_wrapper">
							<h2 class="footer-list-title">Osługa Klienta</h2>
							<ul class="footer-list">
							   <li><a href="{{store direct_url='' _no_vendor='1'}}">Dostawa</a></li>
							   <li><a href="{{store direct_url='' _no_vendor='1'}}">Zwroty i reklamacje</a></li>
							   <li><a href="{{store direct_url=' _no_vendor='1'}}">Raty</a></li>
							   <li><a href="{{store direct_url=' _no_vendor='1'}}">Dofinansowanie</a></li>
							   <li><a href="{{store direct_url=' _no_vendor='1'}}">Punkt odbioru</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-6 col-sm-3 col-md-3">
				<div class="wpb_wrapper">
					<div class="wpb_text_column wpb_content_element ">
						<div class="wpb_wrapper">
							<h2 class="footer-list-title">INFORMACJE</h2>
							<ul class="footer-list">
								  <li><a href="{{store direct_url='o-nas' _no_vendor='1'}}">O Nas</a></li>
								  <li><a href="{{store direct_url='' _no_vendor='1'}}">Regulamin</a></li>
								  <li><a href="{{store direct_url='' _no_vendor='1'}}">Polityka prywatności</a></li>
								  <li><a href="{{store direct_url='' _no_vendor='1'}}">Kariera</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="clearfix visible-xs">&nbsp;</div>
			<div class="col-xs-6 col-sm-3 col-md-3">
				<div class="wpb_wrapper">
					<div class="wpb_text_column wpb_content_element ">
						<div class="wpb_wrapper">
							<h2 class="footer-list-title">TWOJE KONTO</h2>
							<ul class="footer-list">
								<li><a href="{{store direct_url='customer/account' _no_vendor='1'}}">Zaloguj się</a></li>
								<li><a href="{{store url='sales/order/process'}}">Twoje zamówienia</a></li>
								<li><a href="{{store direct_url='wishlist' _no_vendor='1'}}">Przechowalnie</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-6 col-sm-3 col-md-3">
				<div class="wpb_wrapper">
					<div class="wpb_text_column wpb_content_element ">
						<div class="wpb_wrapper">
							<h2 class="footer-list-title">KONTAKT</h2>
						   <div><i class="fa fa-envelope-o" aria-hidden="true"></i><a href="mailto:contacr@ipson.com" class="contact-us-link">Napisz do nas</a></div>
						   <div><i class="fa fa-phone" aria-hidden="true"></i><a href="tel:+800180022" class="contact-us-link">Bezpłatna infolinia: 800 180 022</a></div>
						   <div><i class="fa fa-clock-o" aria-hidden="true"></i>Godziny pracy: pon. - piat. 9:00 - 17:00 </div>
					</div>
				</div>
			</div>
			</div>
EOD
	,
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