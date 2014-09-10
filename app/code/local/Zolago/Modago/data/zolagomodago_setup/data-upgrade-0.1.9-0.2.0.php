<?php
// fix for question in vendor page
$newContent = <<<EOD
                <div class="overlay-color" style="background:#FF8000; color:#fff">	<!-- USTAWIENIA Z SYSTEMU -->
        <aside class="container-fluid">
            <div class="col-sm-12 main p">
                <header>
                    <h2 style="color:#fff">Sklep: <strong>Esotiq | Wersja Desktop</strong></h2>
                </header>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat illo laudantium tempora nihil accusamus in, magnam minima. Sed distinctio ratione alias consequuntur consectetur praesentium adipisci eveniet commodi ipsum minus! Velit.</p>

<!-- guziki Vendor Details-->

{{block name="vendor.info" type="udqa/product_question" template="unirgy/microsite/vendor.info.phtml"}}

<!-- end guziki Vendor Details -->


            </div>
        </aside>
    </div>
EOD;

Mage::getModel('cms/block')->load('top-bottom-header-desktop-v-4')->setData('content', $newContent)->save();


