<?php

$cms = array(
    array(
        'title'         => 'Contact from new order email',
        'identifier'    => 'help-contact-vendor-po',
        'content'       =>
            <<<EOD

<div id="content" class="container-fluid bg-w contact-vendor-po">
    <div class="page-title">
        <h1>KONTAKT ZE SPRZEDAWCĄ <span style="text-transform:uppercase">{{var vendor_name}}</span></h1>
        <h2>NUMER ZAMÓWIENIA: {{var order_number}}</h2>
    </div>
    <div>
        {{block type="udqa/product_question" template="catalog/product/question.phtml"}}
    </div>
</div>

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

