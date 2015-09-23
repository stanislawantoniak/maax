<?php

// installation privacy-settings-remember-me-description cms blocks
$cmsNavigationBlocks = array(
	array(
		'title'         => 'Account promotions not logged in',
		'identifier'    => 'mypromotions_not_logged',
		'content'       => <<<EOD
	<div class="mypromotions-register-header">
		<div class="mypromotions-register-header-title">Twoje ulubione marki do <span class="mypromotions-register-bold">50%&nbsp;taniej!</span></div>
		<div class="mypromotions-register-header-subtitle">Załóż konto i&nbsp;odkryj specjalne rabaty.</div>
		<div class="mypromotions-register-header-text">
			Wyraź zgodę na&nbsp;mailing i&nbsp;odbierz swoje pierwsze kupony.<br />
			Im&nbsp;więcej przeglądasz, im&nbsp;więcej kupujesz ty&nbsp;lepiej dobierzemy dla&nbsp;Ciebie kupony rabatowe.
		</div>
	</div>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	),
	array(
		'title'         => 'Account promotions logged in',
		'identifier'    => 'mypromotions_logged',
		'content'       => <<<EOD
<div class="mypromotions-cms-container">
	<section class="main bg-w mypromotions-cms col-xs-12">
		<div class="mypromotions-cms-title">
			Jak zdobyć dodatkowe rabaty?
		</div>
		<div class="mypromotions-cms-subtitle">
			Nagradzamy Twoją aktywność.
		</div>
		<ul class="mypromotions-cms-list">
			<li>Przeglądaj produkty</li>
			<li>Dodawaj je do ulubionych</li>
			<li>Dziel się nimi ze znajomymi na Facebooku</li>
			<li>Kupuj produkty ulubionych sklepów</li>
		</ul>
		<div class="mypromotions-cms-text">
			Im więcej przeglądasz, im więcej kupujesz tym więcej otrzymasz kodów rabatowych i tym lepiej dobierzemy je do Ciebie.
		</div>
	</section>
</div>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	),
	array(
		'title'         => 'Account promotions persitent',
		'identifier'    => 'mypromotions_persistance',
		'content'       => <<<EOD
<div class="mypromotions-cms-container">
	<section class="main bg-w mypromotions-cms col-xs-12">
		<div class="mypromotions-cms-persistent-title">
			Zaloguj się, by zobaczyć swoje rabaty.
		</div>
		<div class="mypromotions-cms-persistent-subtitle">
			Kupuj swoje ulubione marki do 50% taniej!
		</div>
		<div class="mypromotions-cms-subtitle">
			Nagradzamy Twoją aktywność.
		</div>
		<ul class="mypromotions-cms-list">
			<li>Przeglądaj produkty</li>
			<li>Dodawaj je do ulubionych</li>
			<li>Dziel się nimi ze znajomymi na Facebooku</li>
			<li>Kupuj produkty ulubionych sklepów</li>
		</ul>
		<div class="mypromotions-cms-text">
			Im więcej przeglądasz, im więcej kupujesz tym więcej otrzymasz kodów rabatowych i tym lepiej dobierzemy je do Ciebie.
		</div>
		<div class="mypromotions-cms-buttons">
			<div class="mypromotions-cms-text-register">
				Nie masz konta? <a href="{{store url='customer/account/create/redirect/mypromotions'}}">Zarejestruj się</a>
			</div>
			<div class="mypromotions-cms-button-login button button-primary large link">
				Zaloguj się i zobacz kupony
			</div>
		</div>
	</section>
</div>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	),
	array(
		'title'         => 'Account promotions go to login',
		'identifier'    => 'mypromotions_gotologin',
		'content'       => <<<EOD
<header class="title-section">
	<h2>Masz już konto?</h2>
	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
</header>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
		<div class="row action">
			<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
				Jeżeli masz już konto w naszym sklepie i chcesz zobaczyć kupony musisz się zalogować
			</div>
			<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
				<a href="{{store url='customer/account'}}" class="button button-primary large pull-right">
					Logowanie
				</a>
			</div>
		</div>
	</div>
</div>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	),
	array(
		'title'         => 'Account promotions go to register',
		'identifier'    => 'mypromotions_gotoregister',
		'content'       => <<<EOD
<header class="title-section">
	<h2>Nie masz konta?</h2>
	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
</header>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
		<div class="row action">
			<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
				Jeżeli nie masz konta w naszym sklepie musisz się zalogować i zapisać do newslettera żeby otrzymać kody rabatowe.
			</div>
			<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
				<a href="{{store url='customer/account/create'}}" class="button button-primary large pull-right">
					Rejestracja
				</a>
			</div>
		</div>
	</div>
</div>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	),
	array(
		'title'         => 'Account promotions registration header',
		'identifier'    => 'mypromotions_registration_header',
		'content'       => <<<EOD
<h2>Utwórz konto</h2>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	),
	array(
		'title'         => 'Account promotions no coupons',
		'identifier'    => 'mypromotions_nocoupons',
		'content'       => <<<EOD
<p>W tej chwili nie masz żadnych kodów rabatowych</p>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	),
	array(
		'title'         => 'Account promotions not subscribed, coupons available',
		'identifier'    => 'mypromotions_notsubscribed',
		'content'       => <<<EOD
<header class="title-section">
	<h2>Zapisz się do newslettera!</h2>
	<p>Po zapisie natychmiast dostaniesz nowe kody rabatowe!</p>
</header>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
		<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque maximus feugiat dui, in bibendum est pellentesque a. Quisque placerat felis elit, quis interdum est ullamcorper nec. Donec diam lectus, viverra in mauris in, pretium convallis nisl. Proin eget eros vitae velit maximus rhoncus. Aliquam erat volutpat. Nulla sed sem a nisi pretium mollis. Pellentesque venenatis eros nisl, in imperdiet dolor volutpat in.</p>
	</div>
</div>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	),
	array(
		'title'         => 'My promotions list header',
		'identifier'    => 'mypromotions_list_header',
		'content'       => <<<EOD
<h2>Twoje kody rabatowe</h2>
<p>Super promocje, kupuj z mega zniżkami!</p>
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