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
                        <span class="footer-pay-visa "></span>
                        <span class="footer-pay-master "></span>
                        <span class="footer-pay-paypal "></span>
                    </div>
                    <div class="footer-utils visible-xs">
                        <div>
                            <a href=" ">Polityka prywatności <i class="fa fa-angle-right"></i></a><br/>
		            {{block type='zolagopersistent/forget_footerlink' anchor_text='Usuń moje dane z urządzenia'}}
                        </div>
                    </div>
                    <div class="hidden-xs pull-right">
		            {{block type='zolagopersistent/forget_footerlink' anchor_text='Usuń moje dane z urządzenia'}}
                    </div>
                    <div class="copy">
                        {{config path='design/footer/copyright'}}
                    </div>

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
        'title'         => 'Privacy settings remember me description',
        'identifier'    => 'privacy-settings-remember-me-description',
        'content'       => <<<EOD
<p>Zaznacz opcję jeżeli chcesz aby po wylogowaniu lub zamknięciu przeglądarki Twoje produkty znajdujące się w koszyku, w ulubionych, ostatnio oglądane nie były widoczne na żadnym z Twoich urządzeń.</p>
<p>Jeżeli chcesz jednorazowo skasować dane z tego urządzania to nie zaznaczaj powyższej opcji a skorzystaj z linku „usuń moje dane z urządzenia” znajdującego się po wylogowaniu w stopce serwisu. Dodatkowo zawsze po wylogowaniu z serwisu, będzie można to zrobić linkiem znajdującym się w komunikacie potwierdzającym wylogowanie.</p>
<p>Jeżeli opcja nie jest zaznaczona to Twoje produkty są widoczne bez konieczności logowania a dodanie produktu na dowolnym Twoim urządzeniu powoduje, że widać je na każdym urządzaniu, z którego kiedykolwiek logowano się do serwisu.</p>
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