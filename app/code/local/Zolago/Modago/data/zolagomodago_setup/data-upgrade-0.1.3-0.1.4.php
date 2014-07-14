<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 08.07.2014
 */

// installation of footer cms blocks
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Modago strona glowna inspiracje',
        'identifier'    => 'inspirations-base',
        'content'       => <<<EOD
<section id="bottom" class="bg-w">
    <div class="container-fluid">
        <div class="col-sm-12">
            <!-- START BLOCK INSPIRACJE -->
            <div class="row">

                <section class="block-inspiration">
                    <header class="title-section">
                        <div class="inspiration-strips "></div>
                        <h2>inspiracje</h2>
                    </header>
                    <div id="rwd-inspiration" class="rwdCarousel">
                        <div class="rwd-carousel rwd-theme">
                            <div class="item">
                                <a href="">
                                    <img src="{{skin url='images/insp-1.jpg'}}" alt="">

                                    <div class="carousel-caption">
                                        <span class="title-caption">lady in the bushes</span>
                                        <span class="body-caption">Drugi wiersz opisu</span>
                                        <span class="seemore-caption">zobacz &gt;</span>
                                    </div>
                                </a>
                            </div>
                            <div class="item">
                                <a href="">
                                    <img src="{{skin url='images/insp-2.jpg'}}" alt="">

                                    <div class="carousel-caption">
                                        <span class="title-caption">lady in the bushes</span>
                                        <span class="body-caption">Drugi wiersz opisu</span>
                                        <span class="seemore-caption">zobacz &gt;</span>
                                    </div>
                                </a>
                            </div>
                            <div class="item">
                                <a href="">
                                    <img src="{{skin url='images/insp-3.jpg'}}" alt="">

                                    <div class="carousel-caption">
                                        <span class="title-caption">lady in the bushes</span>
                                        <span class="body-caption">Drugi wiersz opisu</span>
                                        <span class="seemore-caption">zobacz &gt;</span>
                                    </div>
                                </a>
                            </div>
                            <div class="item">
                                <a href="">
                                    <img src="{{skin url='images/insp-4.jpg'}}" alt="">

                                    <div class="carousel-caption">
                                        <span class="title-caption">lady in the bushes</span>
                                        <span class="body-caption">Drugi wiersz opisu</span>
                                        <span class="seemore-caption">zobacz &gt;</span>
                                    </div>
                                </a>
                            </div>
                            <div class="item">
                                <a href="">
                                    <img src="{{skin url='images/insp-1.jpg'}}" alt="">

                                    <div class="carousel-caption">
                                        <span class="title-caption">lady in the bushes</span>
                                        <span class="body-caption">Drugi wiersz opisu</span>
                                        <span class="seemore-caption">zobacz &gt;</span>
                                    </div>
                                </a>
                            </div>
                            <div class="item">
                                <a href="">
                                    <img src="{{skin url='images/insp-2.jpg'}}" alt="">

                                    <div class="carousel-caption">
                                        <span class="title-caption">lady in the bushes</span>
                                        <span class="body-caption">Drugi wiersz opisu</span>
                                        <span class="seemore-caption">zobacz &gt;</span>
                                    </div>
                                </a>
                            </div>
                            <div class="item">
                                <a href="">
                                    <img src="{{skin url='images/insp-3.jpg'}}" alt="">

                                    <div class="carousel-caption">
                                        <span class="title-caption">lady in the bushes</span>
                                        <span class="body-caption">Drugi wiersz opisu</span>
                                        <span class="seemore-caption">zobacz &gt;</span>
                                    </div>
                                </a>
                            </div>
                            <div class="item">
                                <a href="">
                                    <img src="{{skin url='images/insp-4.jpg'}}" alt="">

                                    <div class="carousel-caption">
                                        <span class="title-caption">lady in the bushes</span>
                                        <span class="body-caption">Drugi wiersz opisu</span>
                                        <span class="seemore-caption">zobacz &gt;</span>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="customNavigation">
                            <a class="prev"><i class="fa fa-chevron-left"></i></a>
                            <a class="next"><i class="fa fa-chevron-right"></i></a>
                        </div>
                    </div>
                </section>
                <!-- END:/ BLOCK INSPIRACJE -->
            </div>
        </div>
    </div>
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

