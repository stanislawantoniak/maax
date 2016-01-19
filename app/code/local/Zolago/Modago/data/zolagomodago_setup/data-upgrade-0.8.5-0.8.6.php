<?php

//1. Create footer default block CMS
$allStoreViews = 0;

$footerBlocks = array(
    array(
        'title' => 'Stopka DEFAULT',
        'identifier' => 'footer-website',
        'content' => <<<EOD
<footer id="footer">
    <div class="footer-black">
        <div class="container-fluid">
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
    <div class="footer-black">
        <div class="container-fluid">
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
