<?php

//1. Create footer default block CMS
$allStoreViews = 0;

$footerBlocks = array(
    array(
        'title' => 'Stopka DEFAULT',
        'identifier' => 'footer-website',
        'content' => <<<EOD
<footer id="footer">
    <div class="container-fluid footer-black">
        <div class="col-xs-12">
            <div class="row">
                <div class="footer-logo ">
                    <a href="{{block type="core/template" template="page/html/footer/link-logo.phtml"}}">
                    <img src="{{block type="core/template" template="page/html/footer/image-logo.phtml"}}"  alt="{{config path='design/header/logo_alt'}}" />
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStoreViews
    )
);


foreach ($footerBlocks as $data) {
    $collection = Mage::getModel('cms/block')->getCollection();
    $collection->addStoreFilter($allStoreViews);
    $collection->addFieldToFilter("identifier", $data["identifier"]);
    $block = $collection->getFirstItem();

    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData, $data);
    }
    $block->setData($data)->save();
}
unset($data);



//2. Create footer MODAGO block CMS
$modagoStoreId =  Mage::app()->getStore('default')->getId();
$footerBlocks = array(
    array(
        'title' => 'Stopka MODAGO',
        'identifier' => 'footer-website',
        'content' => <<<EOD
<footer id="footer">
    <div class="container-fluid footer-black">
        <div class="col-xs-12">
            <div class="row">
                <div class="footer-logo ">
                    <a href="{{block type="core/template" template="page/html/footer/link-logo.phtml"}}"><img src="{{skin url='images/svg/logo.svg'}}" alt="{{config path='design/header/logo_alt'}}" /></a>
                </div>
                {{block id='footer-links-website'}}
                {{block id='footer-social-icons-website'}}
            </div>
        </div>
    </div>
    <div class=" footer-gray-wr">
        <div class="container-fluid">
            <div class="col-xs-12">
                <div class="row">
                    <div class="footer-payment  hidden-xs">
                        <span class="footer-pay-visa"></span>
                        <span class="footer-pay-master"></span>
                        <span class="footer-pay-paypal"></span>
                    </div>
                    <div class="footer-utils visible-xs">
                        <div>
                            <span id="persistent-forget-mobile"></span>
                        </div>
                    </div>
                    <div class="hidden-xs pull-right" id="persistent-forget-desktop"></div>
                </div>
            </div>
        </div>
    </div>
</footer>
EOD
    ,
        'is_active' => 1,
        'stores' => $modagoStoreId
    ),

    //FOOTER LINKS
    array(
        'title' => 'Linki w stopce MODAGO',
        'identifier' => 'footer-links-website',
        'content' => <<<EOD
<div class="footer-about ">
	<ul class="hidden-sm hidden-xs">
		<li><a href="{{store direct_url='help' _no_vendor='1'}}"><i class="fa fa-angle-right"></i> Pomoc</a></li>
		<li><a href= "{{store direct_url='customer/account' _no_vendor='1'}}"  ><i class="fa fa-angle-right"></i> Twoje konto</a></li>
		<li><a href="{{store direct_url='regulamin-modago' _no_vendor='1'}}"><i class="fa fa-angle-right"></i> Regulamin</a></li>
		<li><a href="{{store direct_url='informacje-o-modago' _no_vendor='1'}}"><i class="fa fa-angle-right"></i> O nas</a></li>
	</ul>
	<ul class="visible-sm visible-xs">
		<li><a href="{{store direct_url='help' _no_vendor='1'}}"><i class="fa fa-angle-right"></i> Pomoc</a></li>
		<li><a href=  "{{store direct_url='customer/account' _no_vendor='1'}}" ><i class="fa fa-angle-right"></i> Twoje konto</a></li>
		<li><a href="{{store direct_url='regulamin-modago' _no_vendor='1'}}"><i class="fa fa-angle-right"></i> Regulamin</a></li>
		<li><a href="{{store direct_url='informacje-o-modago' _no_vendor='1'}}"><i class="fa fa-angle-right"></i> O nas</a></li>
	</ul>
</div>
EOD
    ,
        'is_active' => 1,
        'stores' => $modagoStoreId
    ),

    //SOCIAL ICONS
    array(
        'title' => 'Linki w stopce MODAGO',
        'identifier' => 'footer-social-icons-website',
        'content' => <<<EOD
<div class="footer-connect ">
    <span>dołącz <br class="visible-xs">do nas</span>
    <a href="https://twitter.com/modagopl" target="_blank" class="ico_social_twitter"></a> <a href="{{store direct_url='help' _no_vendor='1'}}">
    <a href="https://www.facebook.com/MODAGOpl" target="_blank" class="ico_social_fb"></a>
    <a href="https://instagram.com/modago.pl/" target="_blank" class="ico_social_instagram"></a>
    <a href="http://google.com/+ModagoPl" target="_blank" class="ico_social_gplus"></a>
    <a href="https://www.pinterest.com/MODAGOpl/" target="_blank" class="ico_social_pinterest"></a>
</div>
EOD
    ,
        'is_active' => 1,
        'stores' => $modagoStoreId
    )
);


foreach ($footerBlocks as $data) {
    $collection = Mage::getModel('cms/block')->getCollection();
    $collection->addStoreFilter($modagoStoreId, FALSE);
    $collection->addFieldToFilter("identifier", $data["identifier"]);
    $block = $collection->getFirstItem();

    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData, $data);
    }
    $block->setData($data)->save();
}
unset($data);

//3. Remove old cms blocks
$oldBlocks = array(
    "footer-modago",
    "footer-social-icons",
    "footer-links-modago"
);
foreach ($oldBlocks as $identifier) {
    $block = Mage::getModel('cms/block')
        ->load($identifier);
    $block->delete();
}
