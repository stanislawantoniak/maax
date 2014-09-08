<?php
// fix for question in vendor page
$newContent = <<<EOD
<div class="overlay-color" style="background:#FF8000; color:#fff">
    <aside class="container-fluid">
        <div class="col-sm-12 main p">
            <header>
                <h2 style="color:#fff">Sklep: <strong>Esotiq | Wersja Desktop</strong></h2>
            </header>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat illo laudantium tempora nihil accusamus in, magnam minima. Sed distinctio ratione alias consequuntur consectetur praesentium adipisci eveniet commodi ipsum minus! Velit.</p>

            <!-- guziki Vendor Details-->
<div class="container-fluid">
    <div class="action-box-bundle clearfix" style="margin-bottom: 20px;">
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-sm-6 col-md-4 col-lg-3" style="text-align: center;">
                <button class="btn button button-primary" style="margin-top: 15px;" data-toggle="modal"
                        data-target="#seller_description">Informacje o sprzedawcy</button>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3" style="text-align: center;">
                <button class="btn button button-primary" style="margin-top: 15px;" data-toggle="modal"
                        data-target="#terms_delivery">Warunki dostawy i zwrotu</button>
            </div>

            <div class="col-sm-6 col-md-4 col-lg-3" style="text-align: center;">
                <button class="btn button button-primary" style="margin-top: 15px;" data-toggle="modal"
                        data-target="#ask_question">Zadaj pytanie sprzedawcy</button>
            </div>
            <div class="col-sm-6 col-md-4 col-md-offset-4 col-lg-3 col-lg-offset-0" style="text-align: center;">
                <a href="http://modago.pl/" class="btn button button-primary"
                   style="margin-top: 15px;">Wróć do galerii</a>
            </div>
        </div>
    </div>
</div>
            {{block name="vendor.info" type="zolagoudmspro/vendor_info" template="unirgy/microsite/vendor.info.phtml"}}

            <!-- end guziki Vendor Details -->


        </div>
    </aside>
</div>
EOD;

Mage::getModel('cms/block')->load('top-bottom-header-desktop-v-4')->setData('content', $newContent)->save();


$checkoutSidebar = array(
        'title'         => "Checkout | Right column | Step 1",
        'identifier'    => "checkout-right-column-step-1",
        'content'       =>
            <<<EOD

<div class="sidebar-secound col-lg-3 col-md-4 col-sm-12 col-xs-12 col-lg-push-9 col-md-push-8">
    <section class="p main bg-w hidden-sm hidden-xs">
        <header>
            <h2 class="open">Blok CMS</h2>
        </header>
        <div class="clearfix border-top">
            <ul>
                <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
                <li>Pellentesque ultrices lectus ut felis tempor, eget rutrum nulla sodales.</li>
                <li>Nullam vel nunc eu enim hendrerit hendrerit eget non leo.</li>
                <li>Quisque sit amet diam elementum, aliquet risus non, dignissim tortor.</li>
            </ul>
            <figure>
                <img src="/skin/frontend/modago/default/images/home_main_callout.jpg" alt="">
            </figure>
        </div>
        <div class="clearfix border-top">
            <ol>
                <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
                <li>Pellentesque oltrices lectus ut felis tempor, eget rutrum nulla sodales.</li>
                <li>Nullam vel nunc eu enim hendrerit hendrerit eget non leo.</li>
                <li>Quisque sit amet diam elementum, aliquet risus non, dignissim tortor.</li>
                </ol>
        </div>
        <div class="clearfix border-top">
            <dl>
                <dt>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</dt>
                <dd class="separator">Pellentesque ultrices lectus ut fedds tempor, eget rutrum nulla sodales.</dd>
                <dd>Nullam vel nunc eu enim hendrerit hendrerit eget non leo.</dd>
                <dd class="separator">Quisque sit amet diam elementum, addquet risus non, dignissim tortor.</dd>
            </dl>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.Pellentesque ultrices lectus ut fedds tempor,
                eget rutrum nulla sodales.</p>
            <figure>
                <img src="/skin/frontend/modago/default/images/home_main_callout.jpg" alt="">
            </figure>

        </div>
    </section>
</div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
);

Mage::getModel('cms/block')->setData($checkoutSidebar)->save();


