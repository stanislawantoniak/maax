<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 13.08.2014
 */

// installation of footer cms blocks
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Top bottom header mobile vendor Esotiq',
        'identifier'    => 'top-bottom-header-mobile-v-4',
        'content'       => <<<EOD
<div class="overlay-color" style="background:#FF8000; color:#fff">  <!-- USTAWIENIA Z SYSTEMU -->
        <aside class="container-fluid">
            <div class="col-sm-12 main p">
                <header>
                    <h2 style="color:#fff">Sklep: <strong>Esotiq| Wersja mobile</strong></h2>
                </header>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat illo laudantium tempora nihil accusamus in, magnam minima. Sed distinctio ratione alias consequuntur consectetur praesentium adipisci eveniet commodi ipsum minus! Velit.</p>
            </div>
        </aside>
    </div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'Top bottom header desktop vendor Esotiq',
        'identifier'    => 'top-bottom-header-desktop-v-4',
        'content'       => <<<EOD
        <div class="overlay-color" style="background:#FF8000; color:#fff">	<!-- USTAWIENIA Z SYSTEMU -->
        <aside class="container-fluid">
            <div class="col-sm-12 main p">
                <header>
                    <h2 style="color:#fff">Sklep: <strong>Esotiq | Wersja Desktop</strong></h2>
                </header>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Fugiat illo laudantium tempora nihil accusamus in, magnam minima. Sed distinctio ratione alias consequuntur consectetur praesentium adipisci eveniet commodi ipsum minus! Velit.</p>

<!-- guziki Vendor Details-->

{{block name="vendor.info" type="zolagoudmspro/vendor_info" template="unirgy/microsite/vendor.info.phtml"}}

<!-- end guziki Vendor Details -->


            </div>
        </aside>
    </div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    )
);

foreach ($cmsNavigationBlocks as $data) {
    $block = Mage::getModel('cms/block')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData,$data);
    }
    $block->setData($data)->save();
}

