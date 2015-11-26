<?php
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Pasek korzyści',
        'identifier'    => 'benefits-strip-modago',
        'content'       => <<<EOD
<div class="benefits">
    <div class="benefit-item">
        <a href="{{config path=" web/secure/base_url"}}informacje-o-modago">
        <div class="benefit-text"
             style="background-image: url('/skin/frontend/modago/default/images/svg/benefits_transport.svg')">
            <h3>Darmowa dostawa</h3>

            <p>na&nbsp;99% produktów</p>
        </div>
        </a>
    </div>
    <div class="benefit-item">
        <a href="{{config path=" web/secure/base_url"}}informacje-o-modago">
        <div class="benefit-text"
             style="background-image: url('/skin/frontend/modago/default/images/svg/benefits_return.svg')">
            <h3>Darmowy zwrot</h3>

            <p>w&nbsp;ciągu 30&nbsp;dni</p>
        </div>
        </a>
    </div>
    <div class="benefit-item">
        <a href="{{config path=" web/secure/base_url"}}informacje-o-modago">
        <div class="benefit-text"
             style="background-image: url('/skin/frontend/modago/default/images/svg/benefits_shops.svg')">
            <h3>Wiele sklepów</h3>

            <p>jeden koszyk</p>
        </div>
        </a>
    </div>
    <div class="benefit-item">
        <a href="{{config path=" web/secure/base_url"}}mypromotions">
        <div class="benefit-text"
             style="background-image: url('/skin/frontend/modago/default/images/svg/benefits_discounts.svg')">
            <h3>Specjalne rabaty</h3>

            <p>
                <a href="{{config path=" web/secure/base_url"}}mypromotions">dowiedz&nbsp;się więcej&nbsp;>></a>
            </p>
        </div>
        </a>
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