<?php
$cms = array(
    array(
        'title'         => 'Search page empty',
        'identifier'    => 'search-page-empty',
        'content'       => '<p>Przykro nam, ale nie znaleźliśmy w serwisie produktów pasujących do Twojego zapytania. Szukaj ponownie używając innego hasła lub skorzystaj z nawigacji po kategoriach by przeglądać produkty.</p><p class="lead">A może zainteresują Cię inne ciekawe produkty? Poniżej przegląd naszych bestsellerów.</p>',
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