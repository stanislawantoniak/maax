<?php


// installation content blocks of continue 

$cmsNavigationBlocks = array(
    array(
        'title'         => 'Modago | Normal Login Page | Continue',
        'identifier'    => 'modago-login-continue-normal',
        'content'       => 
<<<EOD
        <section class="section">
			<header class="title-section">
				<h2>Nie masz konta?</h2>
			</header>
			<ul class="benefits list bullet_01">
				<li>[Treść do uzupełnienia]</li>
			</ul>
			<footer class="footer-section">
				<a title="Załóż konto" class="button button-primary large link pull-right" href="{{store url='customer/account/create'}}"><span><span>Załóż konto</span></span></a>
			</footer>
		</section>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'Modago | Checkout Login Page | Continue',
        'identifier'    => 'modago-login-continue-checkout',
        'content'       => 
<<<EOD
        <section class="section">
			<header class="title-section">
				<h2>Nie masz konta?</h2>
			</header>
			<ul class="benefits list bullet_01">
				<li>możesz je łatwo założyć przy składaniu zamówienia</li>
				<li>możesz zrobić zakupy bez rejestracji</li>
			</ul>
			<footer class="footer-section">
				<a title="Kontynuuj" class="button button-primary large link pull-right" href="{{store url='checkout/guest/continue'}}"><span><span>Kontynuuj</span></span></a>
			</footer>
		</section>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    )
);

foreach ($cmsNavigationBlocks as $data) {
    Mage::getModel('cms/block')->setData($data)->save();
}

