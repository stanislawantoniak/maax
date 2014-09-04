<?php


// installation content blocks of continue 

$cmsNavigationBlocks = array(
    array(
        'title'         => 'Customer Forgot Password Message',
        'identifier'    => 'customer_forgotpassword_message',
        'content'       => 
<<<EOD
<section class="section clearfix">
    <header class="title-section">
        <h2>NIE PAMIĘTASZ HASŁA?</h2>
        <p class="form-instructions ff_os fz_11">Tutaj message twój email: <strong>{{var customer_email}}</strong></p>
    </header>
</section>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    )
);

foreach ($cmsNavigationBlocks as $data) {
    Mage::getModel('cms/block')->setData($data)->save();
}

