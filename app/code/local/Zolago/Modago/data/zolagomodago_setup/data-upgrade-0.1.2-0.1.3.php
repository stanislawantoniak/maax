<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 08.07.2014
 */

// installation of footer cms blocks
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Modago Male bannery strona glowna',
        'identifier'    => 'boxes-base',
        'content'       => <<<EOD
<div class="row">


    <!--small banners-->
    <div class="container-fluid small-banners">
        <div class="row">

            <div class="col-xs-6 col-sm-3 tight-padding clear-left-nth">
                <a href=""><img class="border1px-rd2-img-gray margin-10-top img-responsive"
                                src="{{skin url='images/banner-small-1.jpg'}}"
                                alt=""></a>
            </div>
            <div class="col-xs-6 col-sm-3 tight-padding clear-left-nth">
                <a href=""><img class="border1px-rd2-img-gray margin-10-top img-responsive"
                                src="{{skin url='images/banner-small-2.jpg'}}"
                                alt=""></a>
            </div>
            <div class="col-xs-6 col-sm-3 tight-padding clear-left-nth">
                <a href=""><img class="border1px-rd2-img-gray margin-10-top img-responsive"
                                src="{{skin url='images/banner-small-3.jpg'}}"
                                alt=""></a>
            </div>
            <div class="col-xs-6 col-sm-3 tight-padding clear-left-nth">
                <a href=""><img class="border1px-rd2-img-gray margin-10-top img-responsive"
                                src="{{skin url='images/banner-small-4.jpg'}}"
                                alt=""></a>
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
        'title'         => 'Modago Duze bannery strona glowna',
        'identifier'    => 'slider-base',
        'content'       => <<<EOD
<div class="row">
    <section class="blockCarouselTop">

        <div id="carousel-top-generic" class="carousel slide border-gray-1x" data-ride="carousel">

            <!-- Indicators -->
            <ol class="carousel-indicators " id="carousel-top-indicators">
                <li data-target="#carousel-top-generic" data-slide-to="0" class="active"></li>
                <li data-target="#carousel-top-generic" data-slide-to="1" class=""></li>
            </ol>

            <!-- Wrapper for slides -->
            <div class="carousel-inner">
                <div class="item active">
                    <img src="{{skin url='images/banner1.jpg'}}" alt="" class="hidden-xs">
                    <img src="{{skin url='images/bannermobile1.jpg'}}" alt=""
                         class="visible-xs">

                    <div class="carousel-caption hidden-xs">
                        <a href="">przeglądaj stylizacje&nbsp;&nbsp;&nbsp;<i class="fa fa-caret-right"></i></a>
                        <a class="margin-top-20px" href="">wejdź do sklepu&nbsp;&nbsp;&nbsp;<i
                                class="fa fa-caret-right"></i></a>
                    </div>
                </div>
                <div class="item">
                    <img src="{{skin url='images/banner1.jpg'}}" alt="" class="hidden-xs">
                    <img src="{{skin url='images/bannermobile1.jpg'}}" alt=""
                         class="visible-xs">

                    <div class="carousel-caption hidden-xs">
                        <a href="">przeglądaj stylizacje&nbsp;&nbsp;&nbsp;<i class="fa fa-caret-right"></i></a>
                        <a class="margin-top-20px" href="">wejdź do sklepu&nbsp;&nbsp;&nbsp;<i
                                class="fa fa-caret-right"></i></a>
                    </div>
                </div>
            </div>


        </div>
    </section>
</div>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    ),
);

foreach ($cmsNavigationBlocks as $data) {
    Mage::getModel('cms/block')->setData($data)->save();
}

