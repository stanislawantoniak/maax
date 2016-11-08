<?php
/**
 *
 */
$blocks = array(

    array(
        'title'         => 'Facebook Footer Link (Ortohab)',
        'identifier'    => 'footer_facebook_link',
        'content'       => <<<EOD
                            <div class="footer_facebook_link">
                                <a href="https://www.facebook.com/ortohab/">
                                    <i class="fa fa-facebook" aria-hidden="true"></i>
                                    <span>Dołącz do nas na facebooku</span>
                                </a>
                            </div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 2
    ),

    array(
        'title'         => 'Facebook Footer Link (Naplo)',
        'identifier'    => 'footer_facebook_link',
        'content'       => <<<EOD
                            <div class="footer_facebook_link">
                                <a href="https://www.facebook.com/">
                                    <i class="fa fa-facebook" aria-hidden="true"></i>
                                    <span>Dołącz do nas na facebooku</span>
                                </a>
                            </div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 3
    ),

    array(
        'title'         => 'Facebook Footer Link (Lekal)',
        'identifier'    => 'footer_facebook_link',
        'content'       => <<<EOD
                            <div class="footer_facebook_link">
                                <a href="https://www.facebook.com/">
                                    <i class="fa fa-facebook" aria-hidden="true"></i>
                                    <span>Dołącz do nas na facebooku</span>
                                </a>
                            </div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 4
    )

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