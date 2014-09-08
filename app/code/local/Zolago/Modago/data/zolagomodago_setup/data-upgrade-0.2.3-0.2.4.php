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


