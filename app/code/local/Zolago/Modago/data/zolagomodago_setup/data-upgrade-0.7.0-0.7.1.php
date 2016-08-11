<?php

//1. Create footer default block CMS
$allStoreViews = 0;

$footerBlocks = array(
    array(
        'title' => 'Stopka DEFAULT',
        'identifier' => 'footer-website',
        'content' => <<<EOD
<footer id="footer">
    <div class="container-fluid footer-default">
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