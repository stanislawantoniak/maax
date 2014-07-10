<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 08.07.2014
 */

// installation of footer cms blocks
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Nawigacja głowna - wrapper',
        'identifier'    => 'navigation-main-wrapper',
        'content'       => <<<EOD
<div class="header_bottom">
<div class="container-fluid">
<nav role="navigation">
<div id="navigation">

{{block id='navigation-main-desktop'}}
{{block id='navigation-main-mobile'}}

<nav id="navCategory" class="visible-xs">
    <h3>Dla kobiet</h3>
    <a href="#" class="backToCategory">przejdź wyżej <i class="fa fa-angle-up"></i></a>
    <ul id="nav_category_mobile" class="navigation" role="navigation">
        <li><a href="#">Sukienki </a></li>
        <li><a href="#">Spodnie</a></li>
        <li><a href="#">Koszulki</a></li>
        <li><a href="#">Bluzki</a></li>
        <li><a href="#">Koszule</a></li>
        <li><a href="#">Bielizna</a></li>
        <li><a href="#">Kurtki</a></li>
        <li><a href="#">Marynarki</a></li>
    </ul>
</nav>
</div>


</nav>
</div>

</div>
<div id="clone_submenu" class="hidden-xs clearfix">
    <div class="container-fluid">

    </div>
</div>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title' => 'Nawigacja głowna - desktop',
        'identifier' => 'navigation-main-desktop',
        'content' => <<<EOD
<ul id="nav_desc" class="navigation hidden-xs" role="navigation">

{{block type='zolagomodago/catalog_category' name='page.header.bottom.category.items' template='page/html/header/bottom.category.items.phtml'}}

<li><a href="#">Wybierz sklep</a>
</li>
<li><a href="#">Outlet</a>
</li>
<li><a href="#">W twoim stylu</a>
</li>
</ul>
EOD
,
        'is_active' => 1,
        'stores' => 0

    ),
    array(
        'title' => 'Nawigacja głowna - mobile',
        'identifier' => 'navigation-main-mobile',
        'content' => <<<EOD
<ul id="nav_mobile" class="navigation visible-xs" role="navigation">

{{block type='zolagomodago/catalog_category' name='page.header.bottom.category.items.mobile' template='page/html/header/bottom.category.items.mobile.phtml'}}

    <li><a href="#">Wybierz sklep <i class="fa fa-chevron-right"></i></a>
    </li>
    <li><a href="#">Outlet <i class="fa fa-chevron-right"></i></a>
    </li>
    <li><a href="">W twoim stylu <i class="fa fa-chevron-right"></i></a>
    </li>
</ul>
EOD
,
        'is_active' => 1,
        'stores' => 0

    ),
    array(
        'title' => 'Nawigacja rozwijana, kategoria Ona',
        'identifier' => 'navigation-dropdown-c-1',
        'content' => <<<EOD
<ul>
<li>
    <div class="row clearfix">
        <div class="col-left">
            <div class="jsMasonry">

                <div class="box col-sm-2 col-xs-6">

                    <dl>
                        <dt><a rel="category" data-description="Kategory" href="#ona">Ona</a>
                        </dt>
                        <dd><a data-description="Bluzy" href="#">Bluzy</a>
                        </dd>
                        <dd><a data-description="Jeans" href="#">Jeans</a>
                        </dd>
                        <dd><a data-description="Koszule" href="#">Koszule</a>
                        </dd>
                        <dd><a data-description="Spodnie" href="#">Spodnie</a>
                        </dd>
                        <dd><a data-description="Spodnie" href="#">Spodnie</a>
                        </dd>
                        <dd><a data-description="Rajstopy" href="#">Rajstopy</a>
                        </dd>
                        <dd><a data-description="ReadMore" href="#">Czytaj więcej <i class="fa fa-angle-right"></i></a>
                        </dd>
                    </dl>
                </div>
                <div class="box col-sm-2 col-xs-6">
                    <dl>
                        <dt><a rel="category" data-description="Kategory" href="#">Odzież</a>
                        </dt>
                        <dd><a data-description="Bluzy" href="#">Bluzy</a>
                        </dd>
                        <dd><a data-description="Jeans" href="#">Jeans</a>
                        </dd>
                        <dd><a data-description="Koszule" href="#">Koszule</a>
                        </dd>

                        <dd><a data-description="ReadMore" href="#">Czytaj więcej <i class="fa fa-angle-right"></i></a>
                        </dd>
                    </dl>
                </div>
                <div class="box col-sm-2 col-xs-6">
                    <dl>
                        <dt><a rel="category" data-description="Kategory" href="#">Odzież</a>
                        </dt>
                        <dd><a data-description="Bluzy" href="#">Bluzy</a>
                        </dd>
                        <dd><a data-description="Jeans" href="#">Jeans</a>
                        </dd>
                        <dd><a data-description="Koszule" href="#">Koszule</a>
                        </dd>
                        <dd><a data-description="Spodnie" href="#">Spodnie</a>
                        </dd>
                        <dd><a data-description="Spodnie" href="#">Spodnie</a>
                        </dd>
                        <dd><a data-description="Rajstopy" href="#">Rajstopy</a>
                        </dd>
                        <dd><a data-description="ReadMore" href="#">Czytaj więcej <i class="fa fa-angle-right"></i></a>
                        </dd>
                    </dl>
                </div>
                <div class="box col-sm-2 col-xs-6">
                    <dl>
                        <dt><a rel="category" data-description="Kategory" href="#">Odzież</a>
                        </dt>
                        <dd><a data-description="Bluzy" href="#">Bluzy</a>
                        </dd>
                        <dd><a data-description="Jeans" href="#">Jeans</a>
                        </dd>
                        <dd><a data-description="Koszule" href="#">Koszule</a>
                        </dd>
                        <dd><a data-description="Spodnie" href="#">Spodnie</a>
                        </dd>
                        <dd><a data-description="Spodnie" href="#">Spodnie</a>
                        </dd>
                        <dd><a data-description="Rajstopy" href="#">Rajstopy</a>
                        </dd>
                        <dd><a data-description="ReadMore" href="#">Czytaj więcej <i class="fa fa-angle-right"></i></a>
                        </dd>
                    </dl>
                </div>
                <div class="box col-sm-2 col-xs-6">
                    <dl>
                        <dt><a rel="category" data-description="Kategory" href="#">Odzież</a>
                        </dt>
                        <dd><a data-description="Bluzy" href="#">Bluzy</a>
                        </dd>
                        <dd><a data-description="Jeans" href="#">Jeans</a>
                        </dd>
                        <dd><a data-description="Rajstopy" href="#">Rajstopy</a>
                        </dd>
                        <dd><a data-description="ReadMore" href="#">Czytaj więcej <i class="fa fa-angle-right"></i></a>
                        </dd>
                    </dl>
                </div>
                <div class="box col-sm-2 col-xs-6">
                    <dl>
                        <dt><a rel="category" data-description="Kategory" href="#">Odzież</a>
                        </dt>
                        <dd><a data-description="Bluzy" href="#">Bluzy</a>
                        </dd>
                        <dd><a data-description="Jeans" href="#">Jeans</a>
                        </dd>
                        <dd><a data-description="Koszule" href="#">Koszule</a>
                        </dd>
                        <dd><a data-description="Spodnie" href="#">Spodnie</a>
                        </dd>
                        <dd><a data-description="Spodnie" href="#">Spodnie</a>
                        </dd>
                        <dd><a data-description="Rajstopy" href="#">Rajstopy</a>
                        </dd>
                        <dd><a data-description="ReadMore" href="#">Czytaj więcej <i class="fa fa-angle-right"></i></a>
                        </dd>
                    </dl>
                </div>
                <div class="box col-sm-2 col-xs-6">
                    <dl>
                        <dt><a rel="category" data-description="Kategory" href="#">Odzież</a>
                        </dt>
                        <dd><a data-description="Bluzy" href="#">Bluzy</a>
                        </dd>
                        <dd><a data-description="Jeans" href="#">Jeans</a>
                        </dd>
                        <dd><a data-description="Koszule" href="#">Koszule</a>
                        </dd>

                        <dd><a data-description="ReadMore" href="#">Czytaj więcej <i class="fa fa-angle-right"></i></a>
                        </dd>
                    </dl>
                </div>
                <div class="box col-sm-2 col-xs-6">
                    <dl>
                        <dt><a rel="category" data-description="Kategory" href="#">Odzież</a>
                        </dt>
                        <dd><a data-description="Bluzy" href="#">Bluzy</a>
                        </dd>
                        <dd><a data-description="Jeans" href="#">Jeans</a>
                        </dd>
                        <dd><a data-description="Koszule" href="#">Koszule</a>
                        </dd>
                        <dd><a data-description="Spodnie" href="#">Spodnie</a>
                        </dd>
                        <dd><a data-description="Spodnie" href="#">Spodnie</a>
                        </dd>
                        <dd><a data-description="Rajstopy" href="#">Rajstopy</a>
                        </dd>
                        <dd><a data-description="ReadMore" href="#">Czytaj więcej <i class="fa fa-angle-right"></i></a>
                        </dd>
                    </dl>
                </div>
                <div class="box col-sm-2 col-xs-6">
                    <dl>
                        <dt><a rel="category" data-description="Kategory" href="#">Odzież</a>
                        </dt>
                        <dd><a data-description="Bluzy" href="#">Bluzy</a>
                        </dd>
                        <dd><a data-description="Jeans" href="#">Jeans</a>
                        </dd>
                        <dd><a data-description="Koszule" href="#">Koszule</a>
                        </dd>
                        <dd><a data-description="Spodnie" href="#">Spodnie</a>
                        </dd>
                        <dd><a data-description="Spodnie" href="#">Spodnie</a>
                        </dd>
                        <dd><a data-description="Rajstopy" href="#">Rajstopy</a>
                        </dd>
                        <dd><a data-description="ReadMore" href="#">Czytaj więcej <i class="fa fa-angle-right"></i></a>
                        </dd>
                    </dl>


                </div>
                <div class="box col-sm-2 col-xs-6">
                    <dl>
                        <dt><a rel="category" data-description="Kategory" href="#">Odzież</a>
                        </dt>
                        <dd><a data-description="Bluzy" href="#">Bluzy</a>
                        </dd>
                        <dd><a data-description="Jeans" href="#">Jeans</a>
                        </dd>
                        <dd><a data-description="Rajstopy" href="#">Rajstopy</a>
                        </dd>
                        <dd><a data-description="ReadMore" href="#">Czytaj więcej <i class="fa fa-angle-right"></i></a>
                        </dd>
                    </dl>
                </div>
            </div>
            <a href="#" class="view-all-category">zobacz wszystkie kategorie <i class="fa fa-angle-right"></i></a>
        </div>

        <div class="col-right hidden-xs">
            <figure>
                <img src="skin/frontend/modago/default/images/hilfinger.jpg" alt="" class="img-responsive">
            </figure>
        </div>
    </div>
    <div class="row">
        <a href="#" class="closeSubMenu">ZWIŃ</i></a>
    </div>
    <!-- end row -->
</li>
<!-- end grid demo -->
</ul>
EOD
,
        'is_active' => 1,
        'stores' => 0

    )
);

foreach ($cmsNavigationBlocks as $data) {
    Mage::getModel('cms/block')->setData($data)->save();
}

