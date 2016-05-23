<?php

$cms = array(
    array(
        'title'         => 'Account empty order history',
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