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
                                <li>
                                    <a href="/o-wojcik">O Nas</a>
                                </li>
                                <li>
                                    <a href="/">Współpraca B2B / Francyza</a>
                                </li>
                                <li>
                                    <a href="/">Znajdź sklep</a>
                                </li>
                                <li>
                                    <a href="/">Kontakt</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-3">
                <div class="wpb_wrapper">
                    <div class="wpb_text_column wpb_content_element ">
                        <div class="wpb_wrapper">
                            <h2 class="footer-list-title">Warunki zakupów</h2>
                            <ul class="footer-list">
                                <li>
                                    <a href="/">Dostawa i płatność</a>
                                </li>
                                <li>
                                    <a href="/">Czas realizacji zamówienia</a>
                                </li>
                                <li>
                                    <a href="/">Zwroty i  reklamacje</a>
                                </li>
                                <li>
                                    <a href="/">Regulamin</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix visible-xs"></div>
            <div class="col-xs-6 col-sm-6 col-md-3">
                <div class="wpb_wrapper">
                    <div class="wpb_text_column wpb_content_element ">
                        <div class="wpb_wrapper">
                            <h2 class="footer-list-title">Twoje Konto</h2>
                            <ul class="footer-list">
                                <li>
                                    <a href="/">Zaloguj się</a>
                                </li>
                                <li>
                                    <a href="/">Twoje zamówienia</a>
                                </li>
                                <li>
                                    <a href="/">Ulubione</a>
                                </li>
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
                            <p>Telefon  + 48 (33) 821-94-10</p>
                            <a href="">Napisz do nas</a>
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
        'title'         => 'WF: Footer Social Links',
        'identifier'    => 'wf_footer_social_links',
        'content'       =>
            <<<EOD
<ul class="menu-social-icons">
                                    <li>
                                        <a href="https://twitter.com/"
                                           class="title-toolip" title="Twitter" target="_blank">
                                            <i class="ico-twitter"></i>

                                        </a></li>
                                    <li>
                                        <a href="http://www.facebook.com/" class="title-toolip" title="Facebook" target="_blank">
                                            <i class="ico-facebook"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="http://pinterest.com/" class="title-toolip" title="Pinterest" target="_blank">
                                            <i class="ico-pinterest"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="http://plus.google.com/" class="title-toolip" title="Google +" target="_blank">
                                            <i class="ico-google-plus"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="mailto:" class="title-toolip" title="Mail to friend">
                                            <i class="ico-envelope"></i>
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