<?php

$cms = array(
    array(
        'title'         => 'Checkout | Right column | Step 2',
        'identifier'    => 'checkout-right-column-step-2',
        'content'       =>
            <<<EOD
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
        </div>
        <div class="clearfix border-top">
            <ol>
                <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
                <li>Pellentesque oltrices lectus ut felis tempor, eget rutrum nulla sodales.</li>
                <li>Nullam vel nunc eu enim hendrerit hendrerit eget non leo.</li>
                <li>Quisque sit amet diam elementum, aliquet risus non, dignissim tortor.</li>

            </ol></div>
        <div class="clearfix border-top">
            <dl>
                <dt>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</dt>
                <dd class="separator">Pellentesque ultrices lectus ut fedds tempor, eget rutrum nulla sodales.</dd>
                <dd>Nullam vel nunc eu enim hendrerit hendrerit eget non leo.</dd>
                <dd class="separator">Quisque sit amet diam elementum, addquet risus non, dignissim tortor.</dd>
            </dl>
        </div>
    </section>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),

);

foreach ($cms as $data) {
    $block = Mage::getModel('cms/block')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData,$data);
    }

    $block->setData($data)->save();
}

