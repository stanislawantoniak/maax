<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 08.07.2014
 */

// installation of footer cms blocks
$cmsFooterBlocks = array(
    array(
        'title'         => 'Stopka ikony sieci spolecznosciowych',
        'identifier'    => 'footer-social-icons',
        'content'       => <<<EOD
<div class="footer-connect ">
    <span>dołącz <br class="visible-xs">do nas</span>
    <a href="#" class="ico_social_twitter"></a>
    <a href="#" class="ico_social_fb"></a>
    <a href="#" class="ico_social_instagram"></a>
    <a href="#" class="ico_social_gplus"></a>
    <a href="#" class="ico_social_pinterest"></a>
</div>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title' => 'Linki w stopce',
        'identifier' => 'footer-links-modago',
        'content' => <<<EOD
<div class="footer-about ">
    <ul class="hidden-sm hidden-xs">
        <li><a href="#"><i class="fa fa-angle-right"></i> Pomoc</a></li>
        <li><a href="#"><i class="fa fa-angle-right"></i> Twoje konto</a></li>
        <li><a href="#"><i class="fa fa-angle-right"></i> Regulamin</a></li>
        <li><a href="#"><i class="fa fa-angle-right"></i> O nas</a></li>
    </ul>
    <ul class="visible-sm visible-xs">
    <li><a href="#"><i class="fa fa-angle-right"></i> Pomoc</a></li>
    <li><a href="#"><i class="fa fa-angle-right"></i> O nas</a></li>
    <li><a href="#"><i class="fa fa-angle-right"></i> Twoje konto</a></li>
    <li><a href="#"><i class="fa fa-angle-right"></i> Kontakt</a></li>
    <li><a href="#"><i class="fa fa-angle-right"></i> Pełna wersja wpisu</a></li>
</ul>
</div>
EOD
,
        'is_active' => 1,
        'stores' => 0

    ),
    array(
        'title' => 'Stopka Modago',
        'identifier' => 'footer-modago',
        'content' => <<<EOD
<footer id="footer">
    <div class="container-fluid footer-black">
        <div class="col-xs-12">
            <div class="row">
                <div class="footer-logo ">

                    <a href="{{store url=''}}"><img src="{{skin url='images/logo.gif'}}" alt="{{config path='design/header/logo_alt'}}" /></a>

                </div>

                {{block id='footer-links-modago'}}
                {{block id='footer-social-icons'}}
            </div>
        </div>
    </div>
    <div class=" footer-gray-wr">
        <div class="container-fluid">
            <div class="col-xs-12">
                <div class="row">
                    <div class="footer-payment  hidden-xs">
                        <span class="footer-pay-visa "></span>
                        <span class="footer-pay-master "></span>
                        <span class="footer-pay-paypal "></span>
                    </div>
                    <div class="footer-utils visible-xs">
                        <div>
                            <a href=" ">Polityka prywatności <i class="fa fa-angle-right"></i></a>
                        </div>
                    </div>
                    <div class="copy">
                        {{config path='design/footer/copyright'}}
                    </div>

                </div>
            </div>
        </div>
    </div>
</footer>
EOD
,
        'is_active' => 1,
        'stores' => 0

    )
);

foreach ($cmsFooterBlocks as $data) {
    Mage::getModel('cms/block')->setData($data)->save();
}

