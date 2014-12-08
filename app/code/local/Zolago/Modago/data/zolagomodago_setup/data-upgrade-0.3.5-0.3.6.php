<?php

$cms = array(
	array(
		'title'         => 'Contact with Modago',
		'identifier'    => 'help-contact-gallery',
		'content'       =>
			<<<EOD
<p>
	<h1>Kontakt z galerią</h1>
</p>
{{block type="udqa/product_question" template="catalog/product/question.phtml"}}
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
<p>
	<h1>Kontakt ze sklepem</h1>
</p>
{{block type="udqa/product_question" template="catalog/product/question.phtml"}}
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	),
	array(
		'title'         => 'Help page',
		'identifier'    => 'help-page',
		'content'       =>
			<<<EOD
<div class="container-fluid">
	<div id="help-index">
		<div class="row">
			<div class="bg-w col-sm-6 col-xs-12">I</div>
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
		'stores'        => 0,
	),
	array(
		'title'         => 'Linki w stopce',
		'identifier'    => 'footer-links-modago',
		'content'       =>
			<<<EOD
<div class="footer-about ">
	<ul class="hidden-sm hidden-xs">
		<li><a href="{{store url='help'}}"><i class="fa fa-angle-right"></i> Pomoc</a></li>
		<li><a href="#"><i class="fa fa-angle-right"></i> Twoje konto</a></li>
		<li><a href="#"><i class="fa fa-angle-right"></i> Regulamin</a></li>
		<li><a href="#"><i class="fa fa-angle-right"></i> O nas</a></li>
	</ul>
	<ul class="visible-sm visible-xs">
		<li><a href="{{store url='help'}}"><i class="fa fa-angle-right"></i> Pomoc</a></li>
		<li><a href="#"><i class="fa fa-angle-right"></i> O nas</a></li>
		<li><a href="#"><i class="fa fa-angle-right"></i> Twoje konto</a></li>
		<li><a href="#"><i class="fa fa-angle-right"></i> Kontakt</a></li>
		<li><a href="#"><i class="fa fa-angle-right"></i> Pełna wersja wpisu</a></li>
	</ul>
</div>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0,
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