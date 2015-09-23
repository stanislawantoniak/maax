<?php

// installation privacy-settings-remember-me-description cms blocks
$cmsNavigationBlocks = array(
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
		'title'         => 'Account promotions persistent',
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
		'title'         => 'Account promotions logged in no coupons',
		'identifier'    => 'mypromotions_logged_nocoupons',
		'content'       => <<<EOD
<div class="mypromotions-cms-container">
	<section class="main bg-w mypromotions-cms col-xs-12">
		<div class="mypromotions-cms-pretitle">
			W tej chwili nie masz żadnych rabatów
		</div>
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
		'title'         => 'Account promotions registration header',
		'identifier'    => 'mypromotions_registration_header',
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