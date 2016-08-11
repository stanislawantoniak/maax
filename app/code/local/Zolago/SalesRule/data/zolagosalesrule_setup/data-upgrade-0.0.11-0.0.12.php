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
			Nagradzamy Twoją aktywność:
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
		'title'         => 'Account promotions logged in no newsletter no coupons',
		'identifier'    => 'mypromotions_logged_nocoupons_nonewsletter',
		'content'       => <<<EOD
<div class="mypromotions-cms-container">
	<section class="main bg-w mypromotions-cms col-xs-12">
		<div class="mypromotions-cms-pretitle">
			W tej chwili nie masz żadnych rabatów.
		</div>
		<div class="mypromotions-cms-newsletter-title">
			Odbierz swój pakiet kuponów!
		</div>
		<div class="mypromotions-cms-newsletter-subtitle">
			Zapisz się do newslettera, żeby otrzymać w prezencie wyjątkowy pakiet kodów rabatowych.
		</div>
		{{block type="zolagomodago/promotions_newsletter" template="promotions/newsletter.phtml"}}
	</section>
</div>
<div class="mypromotions-cms-container">
	<section class="main bg-w mypromotions-cms col-xs-12 mypromotions-nolabels">
		<div class="mypromotions-cms-subtitle">
			Nagradzamy Twoją aktywność:
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
		'title'         => 'Account promotions logged in no newsletter',
		'identifier'    => 'mypromotions_logged_nonewsletter',
		'content'       => <<<EOD
<div class="mypromotions-cms-container">
	<section class="main bg-w mypromotions-cms col-xs-12">
		<div class="mypromotions-cms-newsletter-title">
			Odbierz swój pakiet kuponów!
		</div>
		<div class="mypromotions-cms-newsletter-subtitle">
			Zapisz się do newslettera, żeby otrzymać w prezencie wyjątkowy pakiet kodów rabatowych.
		</div>
		{{block type="zolagomodago/promotions_newsletter" template="promotions/newsletter.phtml"}}
	</section>
</div>
<div class="mypromotions-cms-container">
	<section class="main bg-w mypromotions-cms col-xs-12 mypromotions-nolabels">
		<div class="mypromotions-cms-subtitle">
			Nagradzamy Twoją aktywność:
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
<div id="mypromotions-persistent-modal" class="mypromotions-modal">
	<div class="mypromotions-cms-container">
		<section class="main bg-w mypromotions-cms mypromotions-cms-persistent col-xs-12">
			<div class="mypromotions-cms-persistent-title">
				Zaloguj się, by zobaczyć swoje rabaty.
			</div>
			<div class="mypromotions-cms-persistent-subtitle">
				Kupuj swoje ulubione marki do 50% taniej!
			</div>
			<div class="mypromotions-cms-subtitle">
				Nagradzamy Twoją aktywność:
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
				<div class="mypromotions-cms-text-register hidden-xs">
					Nie masz konta? <a href="{{store url='customer/account/create/redirect/mypromotions'}}">Zarejestruj się</a>
				</div>
				<a href="{{store url='customer/account/login/redirect/mypromotions'}}" class="mypromotions-cms-button-login button button-primary large link">
					Zaloguj się i zobacz kupony
				</a>
				<div class="mypromotions-cms-text-register visible-xs">
					Nie masz konta? <a href="{{store url='customer/account/create/redirect/mypromotions'}}">Zarejestruj się</a>
				</div>
			</div>
		</section>
	</div>
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
			W tej chwili nie masz żadnych rabatów.
		</div>
		<div class="mypromotions-cms-title">
			Jak zdobyć dodatkowe rabaty?
		</div>
		<div class="mypromotions-cms-subtitle">
			Nagradzamy Twoją aktywność:
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
	),
	array(
	'title'         => 'Account promotions not logged in fake coupons',
	'identifier'    => 'mypromotions_fake_coupons',
	'content'       => <<<EOD
<div class="promo_item col-xs-4 col-sm-4 col-md-3 col-lg-3" data-couponid="22">
	<div class="promo_item_box">
		<div class="promo_info">
			<figure class="promo_img">
				<img src="{{media url='lp/coupon/image/resized/294/7/7/5/55eda752f21ef_photo-orange4.jpg'}}" alt="test mycoupons">
			</figure>
			<div class="promo_logo">
				<div class="promo_logo_img" style="background-image: url(https://127.0.0.1/skin/frontend/modago/default/images/logo_black.png)"></div>
			</div>
			<h3 class="promo_name" title="test mycoupons" data-promo-name="test mycoupons">
				test mycoupons
			</h3>
			<div class="promo_see_more">
				<a>Dowiedz się więcej &gt;&gt;</a>
			</div>
		</div>
	</div>
</div>
<div class="promo_item col-xs-4 col-sm-4 col-md-3 col-lg-3" data-couponid="22">
	<div class="promo_item_box">
		<div class="promo_info">
			<figure class="promo_img">
				<img src="{{media url='lp/coupon/image/resized/294/7/7/5/55eda752f21ef_photo-orange4.jpg'}}" alt="test mycoupons">
			</figure>
			<div class="promo_logo">
				<div class="promo_logo_img" style="background-image: url(https://127.0.0.1/skin/frontend/modago/default/images/logo_black.png)"></div>
			</div>
			<h3 class="promo_name" title="test mycoupons" data-promo-name="test mycoupons">
				test mycoupons
			</h3>
			<div class="promo_see_more">
				<a>Dowiedz się więcej &gt;&gt;</a>
			</div>
		</div>
	</div>
</div>
<div class="promo_item col-xs-4 col-sm-4 col-md-3 col-lg-3" data-couponid="22">
	<div class="promo_item_box">
		<div class="promo_info">
			<figure class="promo_img">
				<img src="{{media url='lp/coupon/image/resized/294/7/7/5/55eda752f21ef_photo-orange4.jpg'}}" alt="test mycoupons">
			</figure>
			<div class="promo_logo">
				<div class="promo_logo_img" style="background-image: url(https://127.0.0.1/skin/frontend/modago/default/images/logo_black.png)"></div>
			</div>
			<h3 class="promo_name" title="test mycoupons" data-promo-name="test mycoupons">
				test mycoupons
			</h3>
			<div class="promo_see_more">
				<a>Dowiedz się więcej &gt;&gt;</a>
			</div>
		</div>
	</div>
</div>
<div class="promo_item col-xs-4 col-sm-4 col-md-3 col-lg-3" data-couponid="22">
	<div class="promo_item_box">
		<div class="promo_info">
			<figure class="promo_img">
				<img src="{{media url='lp/coupon/image/resized/294/7/7/5/55eda752f21ef_photo-orange4.jpg'}}" alt="test mycoupons">
			</figure>
			<div class="promo_logo">
				<div class="promo_logo_img" style="background-image: url(https://127.0.0.1/skin/frontend/modago/default/images/logo_black.png)"></div>
			</div>
			<h3 class="promo_name" title="test mycoupons" data-promo-name="test mycoupons">
				test mycoupons
			</h3>
			<div class="promo_see_more">
				<a>Dowiedz się więcej &gt;&gt;</a>
			</div>
		</div>
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