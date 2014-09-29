<?php

$cms = 
    array(
        'title'         => 'Shopping Cart | Accept TOS | Under checkout button',
        'identifier'    => 'shopping-cart-accept-tos',
        'content'       =>
            <<<EOD
<p>
    Oświadczam że zapoznałem się z:<br/>
	<a href="#" class="underline">Regulaminem Modago.pl</a><br/>
	i akceptuję postanowienia.
</p>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    );


$block = Mage::getModel('cms/block')->load($cms['identifier']);
$block->addData($cms);
$block->save();
