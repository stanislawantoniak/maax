<?php
$cmsPage = array(
	array(
	'title'         => 'Persistent Forget Guest',
	'identifier'    => 'persistent_forget_guest',
	'content'       => 
		'<p>Dla zapewnienia Ci wygody pracy na wielu urządzeniach (telefon, tablet, komputer) Twoje produkty dodane d koszyka, dodane do ulubionych, ostatnio oglądane są widoczne bez konieczności logowania a dodanie produktu na dowolnym Twoim urządzeniu powoduje, że widać je na każdym urządzaniu, z którego kiedykolwiek logowano się do serwisu.</p>'.
		'<p>Wciśnij poniżej przycisk „Usuń Moje dane” aby skasować Twoje produkty z tego urządzenia. Jeżeli chcesz aby Twoje produkty nie były widoczne po wylogowaniu lub zamknięciu przeglądarki na żadnym Twoim urządzeniu, skorzystaj z opcji prywatności w Twoim koncie.</p>',
	'is_active'     => 1,
	'stores'        => 0),
);

	
foreach ($cmsPage as $data) {
    $block = Mage::getModel('cms/block')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData,$data);
    }
    $block->setData($data)->save();
}