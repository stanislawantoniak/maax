<?php

$cms = array(
    array(
        'title'         => 'WF: Footer Links',
        'identifier'    => 'wf_footer_links',
        'content'       =>
            <<<EOD
<div class="col-xs-6 col-sm-6 col-md-3">
    <div class="wpb_wrapper">
        <div class="wpb_text_column wpb_content_element ">
            <div class="wpb_wrapper">
                <h2 class="footer-list-title">O Nas</h2>
                <ul class="footer-list">
                    <li><a href="{{store direct_url='' _no_vendor='1'}}">O Nas</a></li>
                    <li><a href="{{store direct_url='' _no_vendor='1'}}">Współpraca B2B / Francyza</a></li>
                    <li><a href="{{store direct_url='storesmap' _no_vendor='1'}}">Znajdź sklep</a></li>
                    <li><a href="{{store direct_url='help/contact' _no_vendor='1'}}">Kontakt</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="col-xs-6 col-sm-6 col-md-3">
    <div class="wpb_wrapper">
        <div class="wpb_text_column wpb_content_element ">
            <div class="wpb_wrapper">
                <h2 class="footer-list-title">Zakupy</h2>
                <ul class="footer-list">
                    <li><a href="{{store direct_url='' _no_vendor='1'}}">Dostawa i płatność</a></li>
                    <li><a href="{{store direct_url='' _no_vendor='1'}}">Czas realizacji zamówienia</a></li>
                    <li><a href="{{store direct_url='' _no_vendor='1'}}">Zwroty i reklamacje</a></li>
                    <li><a href="{{store direct_url='' _no_vendor='1'}}">Regulamin</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="clearfix visible-xs">&nbsp;</div>
<div class="col-xs-6 col-sm-6 col-md-3">
    <div class="wpb_wrapper">
        <div class="wpb_text_column wpb_content_element ">
            <div class="wpb_wrapper">
                <h2 class="footer-list-title">Twoje Konto</h2>
                <ul class="footer-list">
                    <li><a href="{{store direct_url='customer/account' _no_vendor='1'}}">Zaloguj się</a></li>
                    <li><a href="/">Twoje zamówienia</a></li>
                    <li><a href="{{store direct_url='wishlist' _no_vendor='1'}}">Ulubione</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="col-xs-6 col-sm-6 col-md-3">
    <div class="wpb_wrapper">
        <div class="wpb_text_column wpb_content_element ">
            <div class="wpb_wrapper">
                <h2 class="footer-list-title">Kontakt</h2>
                <p>Pracujemy Pon-Pt, w godzinach 8-16</p>
                <p>Telefon + 48 (33) 821-94-10</p>
                <a href="/" class="contact-us-link">Napisz do nas</a></div>
        </div>
    </div>
</div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'WF: Footer Social Links',
        'identifier'    => 'wf_footer_social_links',
        'content'       =>
            <<<EOD
<ul class="menu-social-icons">
    <li>
        <a href="https://twitter.com/"
           class="title-toolip" title="Twitter" target="_blank">
            <i class="fa fa-twitter"></i>
        </a>
    </li>
    <li>
        <a href="http://www.facebook.com/" class="title-toolip" title="Facebook" target="_blank">
            <i class="fa fa-facebook"></i>
        </a>
    </li>
    <li>
        <a href="http://pinterest.com/" class="title-toolip" title="Pinterest" target="_blank">
            <i class="fa fa-pinterest"></i>
        </a>
    </li>
    <li>
        <a href="http://plus.google.com/" class="title-toolip" title="Google +" target="_blank">
            <i class="fa fa-google-plus"></i>
        </a>
    </li>
</ul>
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