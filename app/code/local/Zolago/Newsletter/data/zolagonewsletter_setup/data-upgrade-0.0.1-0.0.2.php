<?php

// installation of bottom header cms blocks
$cmsPage = array(
    array (
        'title' => 'Newsletter',
        'identifier' => 'newsletter/thankyou',
        'content' => <<<EOD
<div class="container-fluid bg-w">
  <h1 style="text-align: center">Dziękujemy za zapisanie się do newslettera!</h1>
  <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur pretium mattis justo at accumsan. Aliquam erat volutpat. Curabitur non porttitor odio. Quisque quam libero, facilisis eu libero eu, tempor commodo neque. Interdum et malesuada fames ac ante ipsum primis in faucibus. Praesent venenatis rutrum ullamcorper. Maecenas non metus commodo, malesuada purus sed, lacinia elit. Ut viverra, nisl non consequat gravida, justo tortor varius lorem, a accumsan neque dui quis odio. Morbi vehicula vitae sapien ut cursus. Suspendisse finibus mi dui, non placerat eros iaculis a. Mauris porta volutpat nunc sit amet iaculis. Aenean faucibus laoreet dictum. Nunc aliquam risus sed mauris vehicula ultrices. Sed semper fringilla magna, in euismod urna pretium vitae.</p>
  <p>Sed et tempus neque. Proin porttitor nisi est, non efficitur risus tempus ut. Nam pellentesque turpis vitae quam condimentum, sed semper magna tincidunt. Fusce vulputate mauris in lectus consequat auctor. Duis eget diam faucibus, pharetra ligula sed, vestibulum lorem. Vivamus id pellentesque ligula. Etiam vestibulum diam at diam mattis vulputate. Maecenas ornare, risus ac pharetra egestas, felis lacus dignissim elit, sed commodo quam tortor sit amet ante. Suspendisse finibus mauris sit amet nunc sollicitudin, nec maximus libero volutpat. Quisque tristique massa nunc, ac facilisis ante porttitor id. Sed viverra nisl sed congue ornare.</p>
</div>
EOD
    ,
        'is_active' => '1',
        'stores' => 0,
        'root_template' => 'one_column',
        'layout_update_xml' => '<remove name="breadcrumbs"/>'
    )
);

foreach ($cmsPage as $data) {
    $block = Mage::getModel('cms/page')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData,$data);
    }
    $block->setData($data)->save();
}

