<?php

$cmsBlocks = array(
    array(
        'title'         => 'SalesManago unsubscribe redirect',
        'identifier'    => 'salesmanago_unsubscribe_redirect',
        'content'       => <<<EOD
<section class="section clearfix">
    <header class="title-section">
        <h2>Wypisałeś się z listy subskrybentów</h2>
    </header>
    <div>
        <div class="container-fluid">
            <div class="col-sm-12">
                <div class="row">
                    <p>
                        1. informacja pierwsza jak bardzo ważne jest abyś był zapisany do newslettera
                    </p>
                </div>
                <div class="row">
                    <p>
                        2. informacja pierwsza jak bardzo ważne jest abyś był zapisany do newslettera
                    </p>
                </div>
                <div class="row">
                    <p>
                        3. informacja pierwsza jak bardzo ważne jest abyś był zapisany do newslettera
                    </p>
                </div>
            </div>
        </div>
    </div>
    <button type="submit" name="send" class="hide-if-invalid button button-primary large link pull-right" style="width:auto">Zapisz mnie do listy subskrybentów</button>
</section>

EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    )
);

foreach ($cmsBlocks as $data) {
    $block = Mage::getModel('cms/block')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData,$data);
    }
    $block->setData($data)->save();
}

