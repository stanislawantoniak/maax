<?php
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Stopka Modago',
        'identifier'    => 'footer-modago',
        'content'       => <<<EOD
<footer id="footer">
    <div class="container-fluid footer-black">
        <div class="col-xs-12">
            <div class="row">
                <div class="footer-logo ">
                    <a href="{{block type="core/template" template="page/html/footer/link-logo.phtml"}}"><img src="{{skin url='images/logo.gif'}}" alt="{{config path='design/header/logo_alt'}}" /></a>
                </div>
                {{block id='footer-links-modago'}}
                {{block id='footer-social-icons'}}
            </div>
        </div>
    </div>
    <div class=" footer-gray-wr">
        <div class="container-fluid">
            <div class="col-xs-12">
                <div class="row">
                    <div class="footer-payment  hidden-xs">
                        <span class="footer-pay-visa"></span>
                        <span class="footer-pay-master"></span>
                        <span class="footer-pay-paypal"></span>
                    </div>
                    <div class="footer-utils visible-xs">
                        <div>
                            <a href="#">Polityka prywatności <i class="fa fa-angle-right"></i></a><br/>
							<span id="persistent-forget-mobile"></span>
                        </div>
                    </div>
                    <div class="hidden-xs pull-right" id="persistent-forget-desktop"></div>
                </div>
            </div>
        </div>
    </div>
</footer>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),

    array(
        'title'         => 'Linki w stopce',
        'identifier'    => 'footer-links-modago',
        'content'       => <<<EOD
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
	</ul>
</div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),
);

foreach ($cmsNavigationBlocks as $data) {
    $block = Mage::getModel('cms/block')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData,$data);
    }
    $block->setData($data)->save();
}