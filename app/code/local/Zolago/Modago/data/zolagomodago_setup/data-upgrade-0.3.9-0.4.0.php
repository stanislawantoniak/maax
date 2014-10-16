<?php

$cms = array(

    array(
        'title'         => 'Help page',
        'identifier'    => 'help-page',
        'content'       =>
<<<EOD
{{block type="cms/block" block_id="help-page-mobile-menu"}}

<div class="container-fluid">
	<div class="help-content">
		<div class="row">
			<div class="bg-w col-sm-6 col-xs-12"><a href="{{store url='faq'}}">Odpowiedzi na najczęstsze pytania</a><br /></div>
			<div class="bg-w col-sm-6 col-xs-12">II</div>
		</div>
		<div class="row">
			<div class="bg-w col-sm-6 col-xs-12">III</div>
			<div class="bg-w col-sm-6 col-xs-12">
				<a href="{{store url='help/contact/gallery'}}">Kontakt z galerią</a><br />
				<a href="{{store url='help/contact/vendor'}}">Kontakt ze sklepem</a><br />
			</div>
		</div>
		<div class="row">
			<div class="bg-w col-sm-6 col-xs-12">V</div>
			<div class="bg-w col-sm-6 col-xs-12">VI</div>
		</div>
	</div>
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

