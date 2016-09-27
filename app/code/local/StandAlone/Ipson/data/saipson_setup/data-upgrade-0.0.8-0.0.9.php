<?php

$blocks = array(
    array(
        'title' => 'Home page logotypes strip',
        'identifier' => 'home-page-logotypes-strip',
        'content' =>
<<<EOD
<div id="home-logo-items" class="container-fluid">
	<div class="col-sm-12 home-logo-slick-slider">
		<div class="home-logo-item">
			<div class="cnt">
				<a href="#">
					<img src="/skin/frontend/rich/details/images/home-page-logos/logo1.jpg"/>
				</a>
			</div>
		</div>
		<div class="home-logo-item">
			<div class="cnt">
				<a href="#">
					<img src="/skin/frontend/rich/details/images/home-page-logos/logo2.jpg"/>
				</a>
			</div>
		</div>
		<div class="home-logo-item">
			<div class="cnt">
				<a href="#">
					<img src="/skin/frontend/rich/details/images/home-page-logos/logo3.jpg"/>
				</a>
			</div>
		</div>
		<div class="home-logo-item">
			<div class="cnt">
				<a href="#">
					<img src="/skin/frontend/rich/details/images/home-page-logos/logo4.jpg"/>
				</a>
			</div>
		</div>
		<div class="home-logo-item">
			<div class="cnt">
				<a href="#">
					<img src="/skin/frontend/rich/details/images/home-page-logos/logo5.jpg"/>
				</a>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function(){
		jQuery('.home-logo-slick-slider').slick({
			dots: true,
			infinite: false,
			arrows: false,
			slidesToShow: 5,
			slidesToScroll: 1,
			responsive: [
				{
					breakpoint: 930,
					settings: {
						slidesToShow: 4,
						slidesToScroll: 1,
					}
				},
				{
					breakpoint: 750,
					settings: {
						slidesToShow: 3,
						slidesToScroll: 1,
					}
				},
				{
					breakpoint: 575,
					settings: {
						slidesToShow: 2,
						slidesToScroll: 1,
					}
				}
			]
		});
	});
</script>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    ),
);

foreach ($blocks as $blockData) {
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

