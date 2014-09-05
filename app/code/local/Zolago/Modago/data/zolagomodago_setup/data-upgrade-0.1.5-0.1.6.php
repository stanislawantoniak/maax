<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 13.08.2014
 */

// installation of footer cms blocks
$cmsNavigationBlocks = array(
    array(
        'title'         => 'PL | CART | Available Payment Methods - Icons',
        'identifier'    => 'pl-cart-available-payment-methods-icons',
        'content'       => <<<EOD
<a href="#"><img src="/skin/frontend/modago/default/images/payment_methods/payment_methods.png" alt=""></a>
<a href="#"><img src="/skin/frontend/modago/default/images/payment_methods/payment_methods-02.png" alt=""></a>
<a href="#"><img src="/skin/frontend/modago/default/images/payment_methods/payment_methods-03.png" alt=""></a><br>
<a href="#"><img src="/skin/frontend/modago/default/images/payment_methods/payment_methods-04.png" alt=""></a>
<a href="#"><img src="/skin/frontend/modago/default/images/payment_methods/payment_methods-05.png" alt=""></a>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'PL | CART | Available payment methods | Learn more | Popup',
        'identifier'    => 'pl-cart-available-payment-methods-learn-more-popup',
        'content'       => <<<EOD
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis accusantium voluptate, autem hic sunt vero placeat rerum, tenetur illum vel esse, accusamus atque similique. Magnam voluptas alias laboriosam ea soluta?</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facilis accusantium voluptate, autem hic sunt vero placeat rerum, tenetur illum vel esse, accusamus atque similique. Magnam voluptas alias laboriosam ea soluta?</p>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    )
);

foreach ($cmsNavigationBlocks as $data) {
    Mage::getModel('cms/block')->load($data['identifier'])->setData($data)->save();
}

