<?php

$cms = array(
    array(
        'title'         => 'Shopping Cart | Accept TOS | Under checkout button',
        'identifier'    => 'shopping-cart-accept-tos',
        'content'       =>
            <<<EOD
<p>
    Klikając w przycisk akceptuję TOS.
</p>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    )
);

foreach ($cms as $data) {
    $block = Mage::getModel('cms/block')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData,$data);
    }

    $block->setData($data)->save();
}