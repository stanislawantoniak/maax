<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 08.07.2014
 */

// installation of footer cms blocks
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Modago kategoria glowna - dla niej',
        'identifier'    => 'main-category-her',
        'content'       => <<<EOD
<div id="content" class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div id="sidebar" class="clearfix">
                            {{block id='sidebar-c7-wrapper'}}
                        </div>
                        <div id="content-main">

                            <section class="blockCarouselTop">
                                {{block type='zolagocms/component_slider' block_id='slider'}}
                            </section>
                            <section class="block-promoted-banners">
                                {{block type='zolagocms/component_boxes' block_id='boxes'}}
                            </section>
                        </div>
                    </div>
                </div>
            </div>
            <section id="bottom" class="bg-w">
                <div class="container-fluid">
                    <div class="col-sm-12">
                        <!-- START BLOCK INSPIRACJE -->
                                {{block type='zolagocms/component_inspirations' block_id='inpirations'}}
                        <!-- END:/ BLOCK INSPIRACJE -->
                    </div>
                </div>
            </section>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'Kategoria główna | Dla niej | Slider',
        'identifier'    => 'slider-base-c7',
        'content'       => <<<EOD
<div id="carousel-top-generic" class="carousel slide border-gray-1x dummy" data-ride="carousel">

    <!-- Indicators -->
    <ol class="carousel-indicators hidden-xs" id="carousel-top-indicators">
        <li data-target="#carousel-top-generic" data-slide-to="0" class="active"></li>
        <li data-target="#carousel-top-generic" data-slide-to="1"></li>
    </ol>

    <!-- Wrapper for slides -->
    <div class="carousel-inner">
        <div class="item active">
            <img src="skin/frontend/modago/default/images/banner1.jpg" alt="" class="hidden-xs">
            <img src="skin/frontend/modago/default/images/bannermobile1.jpg" alt="" class="visible-xs">
            <div class="carousel-caption">
                <a href="">przeglądaj stylizacje <i class="fa fa-caret-right"></i></a>
                <a class="margin-top-20px" href="">wejdź do sklepu <i class="fa fa-caret-right"></i></a>
            </div>
        </div>
        <div class="item">
            <img src="skin/frontend/modago/default/images/banner1.jpg" alt="" class="hidden-xs">
            <img src="skin/frontend/modago/default/images/bannermobile1.jpg" alt="" class="visible-xs">
            <div class="carousel-caption">
                <a href="">przeglądaj stylizacje <i class="fa fa-caret-right"></i></a>
                <a class="margin-top-20px" href="">wejdź do sklepu <i class="fa fa-caret-right"></i></a>
            </div>
        </div>
    </div>
    <!-- Controls -->
      <a class="left carousel-control visible-xs" href="#carousel-top-generic" data-slide="prev">
        <i class="fa fa-chevron-left"></i>
      </a>
      <a class="right carousel-control visible-xs" href="#carousel-top-generic" data-slide="next">
        <i class="fa fa-chevron-right"></i>
      </a>



</div>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'Kategoria główna | Dla niej | Boxy',
        'identifier'    => 'boxes-base-c7',
        'content'       => <<<EOD
<div id="rwd-banners" class="rwdCarousel">
    <div class="rwd-carousel rwd-theme">
        <div class="item border-gray-1x "><a href=""><img src="skin/frontend/modago/default/images/banner-small-1.jpg" alt=""></a></div>
        <div class="item border-gray-1x "><a href=""><img src="skin/frontend/modago/default/images/banner-small-2.jpg" alt=""></a></div>
        <div class="item border-gray-1x "><a href=""><img src="skin/frontend/modago/default/images/banner-small-3.jpg" alt=""></a></div>
        <div class="item border-gray-1x "><a href=""><img src="skin/frontend/modago/default/images/banner-small-4.jpg" alt=""></a></div>
    </div>
</div>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'Kategoria główna | Dla niej | Inspiracje',
        'identifier'    => 'inspirations-base-c7',
        'content'       => <<<EOD
<section class="block-inspiration">
    <header class="title-section">
        <div class="inspiration-strips "></div>
        <h2 >inspiracje</h2>
    </header>
    <div id="rwd-inspiration" class="rwdCarousel">
        <div class="rwd-carousel rwd-theme">
            <div class="item">
                <a href="">
                    <img src="skin/frontend/modago/default/images/insp-1.jpg" alt="">
                    <div class="carousel-caption">
                        <span class="title-caption">lady in the bushes</span>
                        <span class="body-caption">Drugi wiersz opisu</span>
                        <span class="seemore-caption">zobacz &gt;</span>
                    </div>
                </a>
            </div>
            <div class="item">
                <a href="">
                    <img src="skin/frontend/modago/default/images/insp-2.jpg" alt="">
                    <div class="carousel-caption">
                        <span class="title-caption">lady in the bushes</span>
                        <span class="body-caption">Drugi wiersz opisu</span>
                        <span class="seemore-caption">zobacz &gt;</span>
                    </div>
                </a>
            </div>
            <div class="item">
                <a href="">
                    <img src="skin/frontend/modago/default/images/insp-3.jpg" alt="">
                    <div class="carousel-caption">
                        <span class="title-caption">lady in the bushes</span>
                        <span class="body-caption">Drugi wiersz opisu</span>
                        <span class="seemore-caption">zobacz &gt;</span>
                    </div>
                </a>
            </div>
            <div class="item">
                <a href="">
                    <img src="skin/frontend/modago/default/images/insp-4.jpg" alt="">
                    <div class="carousel-caption">
                        <span class="title-caption">lady in the bushes</span>
                        <span class="body-caption">Drugi wiersz opisu</span>
                        <span class="seemore-caption">zobacz &gt;</span>
                    </div>
                </a>
            </div>
            <div class="item">
                <a href="">
                    <img src="skin/frontend/modago/default/images/insp-1.jpg" alt="">
                    <div class="carousel-caption">
                        <span class="title-caption">lady in the bushes</span>
                        <span class="body-caption">Drugi wiersz opisu</span>
                        <span class="seemore-caption">zobacz &gt;</span>
                    </div>
                </a>
            </div>
            <div class="item">
                <a href="">
                    <img src="skin/frontend/modago/default/images/insp-2.jpg" alt="">
                    <div class="carousel-caption">
                        <span class="title-caption">lady in the bushes</span>
                        <span class="body-caption">Drugi wiersz opisu</span>
                        <span class="seemore-caption">zobacz &gt;</span>
                    </div>
                </a>
            </div>
            <div class="item">
                <a href="">
                    <img src="skin/frontend/modago/default/images/insp-3.jpg" alt="">
                    <div class="carousel-caption">
                        <span class="title-caption">lady in the bushes</span>
                        <span class="body-caption">Drugi wiersz opisu</span>
                        <span class="seemore-caption">zobacz &gt;</span>
                    </div>
                </a>
            </div>
            <div class="item">
                <a href="">
                    <img src="skin/frontend/modago/default/images/insp-4.jpg" alt="">
                    <div class="carousel-caption">
                        <span class="title-caption">lady in the bushes</span>
                        <span class="body-caption">Drugi wiersz opisu</span>
                        <span class="seemore-caption">zobacz &gt;</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'Kategoria główna | Dla niej | Sidebar wrapper',
        'identifier'    => 'sidebar-c7-wrapper',
        'content'       => <<<EOD
<div class="sidebar">

  <div class="section clearfix hidden-xs">
    <h3 class="open">Kategorie </h3>

      <ul class="nav nav-pills nav-stacked">
        <li><a href="#" class="simple">Sukienki</a></li>
        <li><a href="#" class="simple">Spodnie</a></li>
        <li><a href="#" class="simple">Koszule</a></li>
        <li><a href="#" class="simple">Swetry</a></li>
      </ul>
      <ul class="nav nav-pills nav-stacked">
        <li><a href="#" class="simple">Buty</a></li>
        <li><a href="#" class="simple">Klapki</a></li>
      </ul>
      <ul class="nav nav-pills nav-stacked">
        <li><a href="#" class="simple">Skarpety i podkolanówki</a></li>
        <li><a href="#" class="simple">Klapki</a></li>
      </ul>

  </div>

  <div class="section clearfix hidden-xs">
    <h3 class="open">Teraz na topie </h3>

      <ul class="nav nav-pills nav-stacked">
        <li><a href="#" class="simple">Sukienki</a></li>
        <li><a href="#" class="simple">Spodnie</a></li>
        <li><a href="#" class="simple">Koszule</a></li>
        <li><a href="#" class="simple">Swetry</a></li>
      </ul>
      <ul class="nav nav-pills nav-stacked">
        <li><a href="#" class="simple">Buty</a></li>
        <li><a href="#" class="simple">Klapki</a></li>
      </ul>
      <ul class="nav nav-pills nav-stacked">
        <li><a href="#" class="simple">Skarpety i podkolanówki</a></li>
        <li><a href="#" class="simple">Klapki</a></li>
      </ul>

  </div>

</div>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    ),
);


// remove menu wrapper
$newNavigationWrapperContent = <<<EOD
<div class="header_bottom">
<div class="container-fluid">
<nav role="navigation">
<div id="navigation">

{{block id='navigation-main-desktop'}}
{{block id='navigation-main-mobile'}}

{{block type='zolagomodago/catalog_category' block_id='category.main.menu.mobile' template='catalog/category/category.main.menu.mobile.phtml'}}
</div>


</nav>
</div>

</div>
<div id="clone_submenu" class="hidden-xs clearfix">
    <div class="container-fluid">

    </div>
</div>
EOD;
Mage::getModel('cms/block')->load('navigation-main-wrapper')->setData('content', $newNavigationWrapperContent)->save();

foreach ($cmsNavigationBlocks as $data) {
    Mage::getModel('cms/block')->setData($data)->save();
}

