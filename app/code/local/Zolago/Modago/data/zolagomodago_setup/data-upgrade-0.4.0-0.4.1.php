<?php

$cms = array(
    array(
        'title'         => 'Contact with Modago',
        'identifier'    => 'help-contact-gallery',
        'content'       =>
            <<<EOD
            <div id="contact-container" class="container-fluid bg-w">
	<h1>KONTAKT Z GALERIĄ MODAGO</h1>
	<p>
		Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent viverra, erat quis vulputate pulvinar, nulla ligula ullamcorper velit, vel blandit sem nunc nec lectus.
	</p>
	<p>
		Suspendisse ultricies tincidunt ultricies. Proin tempor massa eu massa gravida facilisis. Aliquam et lobortis nisl. Donec tincidunt nibh at neque aliquet, non tristique leo eleifend. Mauris et mi tempor, lobortis justo a, volutpat leo. Vivamus iaculis commodo tortor, ornare pulvinar nisi. In ac porta purus. Ut metus libero, fringilla vel diam vitae, fermentum lacinia neque.
	</p>
	<p>
		Nullam facilisis mauris sit amet libero hendrerit, faucibus sagittis felis fringilla. Vestibulum posuere mi ut velit fringilla pulvinar. Aenean et scelerisque nulla. Duis justo elit, fermentum sit amet pretium pretium, accumsan ac sem. Nulla pulvinar ultricies metus eu convallis. Proin interdum erat ut nunc lobortis, id pretium elit ornare. Etiam condimentum dolor eu lectus volutpat, ac imperdiet felis tincidunt.
	</p>
	{{block type="udqa/product_question" template="catalog/product/question.phtml"}}
</div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'Contact with Vendor',
        'identifier'    => 'help-contact-vendor',
        'content'       =>
            <<<EOD
            <div id="contact-container" class="container-fluid bg-w">
	<h1>KONTAKT ZE SKLEPEM</h1>
	<p>
		Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent viverra, erat quis vulputate pulvinar, nulla ligula ullamcorper velit, vel blandit sem nunc nec lectus.
	</p>
	<p>
		Quisque tincidunt lacus at enim egestas posuere at non nisl. Nunc placerat leo nec purus vehicula, ut cursus eros aliquet. Integer at consectetur quam.
	</p>
	<p>
		Maecenas sed hendrerit ligula, in eleifend tortor. Sed ut maximus sapien, in interdum est. Aenean vehicula odio at turpis egestas finibus.
		<ul>
			<li>Duis pulvinar, diam quis tempor dignissim, tellus turpis eleifend elit, vitae tristique risus nulla non tortor. </li>
			<li> Aenean pharetra posuere nibh, porta blandit ante blandit accumsan.</li>
			<li>Curabitur maximus est a velit dapibus consectetur.</li>
			<li>Nunc semper id lacus at blandit. Vestibulum at ante quis justo mollis lobortis. </li>
		</ul>
	</p>
	<p>
		Donec a mollis magna, sed interdum mi. Curabitur volutpat scelerisque est sit amet fringilla. In hac habitasse platea dictumst. Ut id egestas quam, at blandit dolor. Nam elementum odio vitae justo suscipit tincidunt. Aenean est metus, fringilla a gravida sed, ultricies vitae justo. Vivamus leo velit, sagittis eu justo quis, pulvinar ornare velit. Sed semper quis nibh et iaculis.
	</p>
{{block type="udqa/product_question" template="catalog/product/question.phtml"}}
</div>
EOD
    ,
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

