<?php

// installation privacy-settings-remember-me-description cms blocks
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Invitation for login',
        'identifier'    => 'mypromotions_not_logged',
        'content'       => <<<EOD
<div class="brands-cms wrapp-section bg-w">
    <div class="container-fluid">
        <div class="row">
            <div class="brands-cms-content col-sm-12">
                <p style="font-size: 12px;">Zaloguj się i zapisz do newslettera a dostaniesz fajne promocje
</p>
            </div>
        </div>
    </div>
</div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'Coupons list',
        'identifier'    => 'mypromotions_logged_subscribed',
        'content'       => <<<EOD
<div class="brands-cms wrapp-section bg-w">
    <div class="container-fluid">
        <div class="row">
            <div class="brands-cms-content col-sm-12">
                <p style="font-size: 12px;">
Oto twoje kody promocyjne
                </p>
            </div>
        </div>
    </div>
</div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'Your promotions',
        'identifier'    => 'mypromotions_logged_not_subscribed',
        'content'       => <<<EOD
<div class="brands-cms wrapp-section bg-w">
    <div class="container-fluid">
        <div class="row">
            <div class="brands-cms-content col-sm-12">
                <p style="font-size: 12px;">
Zapisz się do newslettera a dostaniesz fajne promocje
                </p>
            </div>
        </div>
    </div>
</div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'Your promotions - please log in',
        'identifier'    => 'mypromotions_persistance',
        'content'       => <<<EOD
<div class="brands-cms wrapp-section bg-w">
    <div class="container-fluid">
        <div class="row">
            <div class="brands-cms-content col-sm-12">
                <p style="font-size: 12px;">
Zaloguj się i zobacz swoje promocje
                </p>
            </div>
        </div>
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

