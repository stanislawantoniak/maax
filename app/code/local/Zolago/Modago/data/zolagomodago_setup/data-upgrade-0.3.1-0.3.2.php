<?php

$cms = array(
    array(
        'title'         => 'Customer Forgot Password Message',
        'identifier'    => 'customer_forgotpassword_message',
        'content'       =>
            <<<EOD
<section class="section clearfix">
    <header class="title-section">
        <h2>ODBIERZ MAILA, ABY USTAWIĆ NOWE HASŁO</h2>
    </header>
<p class="form-instructions ff_os fz_11">Jeśli konto <strong>{{var customer_email}}</strong> istnieje w naszym serwisie, otrzymasz zaraz od nas maila.</p>
        <p class="form-instructions ff_os fz_11">W mailu znajdziesz linka, który skieruje Cię do strony, na której możesz zmienić swoje hasło w serwisie. Po kliknięciu w link zostaniesz automatycznie zalogowany do serwisu.
</p>
        <p class="form-instructions ff_os fz_11">W przypadku nieotrzymania wiadomości, sprawdź czy e-mail nie trafił do folderu spam. Jeśli po 15 minutach mail nie dotrze, skontaktuj się z naszym biurem obsługi klienta.</p>
</section>
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