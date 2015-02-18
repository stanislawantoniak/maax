<?php
$newContent = <<<EOD
Potwierdzenie zamówienia przez kliknięcie przycisku "Zamawiam i płacę" wiąże się z obowiązkiem opłacenia zamówienia. </br>
Nie będzie już można dokonać zmian w koszyku.
EOD;


$cms = Mage::getModel('cms/block')->load('checkout-review-footer-1');
if ($cms->getBlockId()) {
    $data = $cms->getData();
} else {
    $data = array(
        'stores' => 0,
        'is_active' => 1,
    );
}
$data['content'] = $newContent;
$data['identifier'] = 'checkout-review-footer-1';
$data['title'] = 'Checkout review footer';
$cms->setData($data)->save();

$newContent = <<<EOD
Potwierdzenie zamówienia przez kliknięcie przycisku "Zamawiam i płacę" wiąże się z obowiązkiem zapłaty przy odbiorze przesyłki.
EOD;


$cms = Mage::getModel('cms/block')->load('checkout-review-footer-cod-1');
if ($cms->getBlockId()) {
    $data = $cms->getData();
} else {
    $data = array(
        'stores' => 0,
        'is_active' => 1,
    );
}
$data['content'] = $newContent;
$data['identifier'] = 'checkout-review-footer-cod-1';
$data['title'] = 'Checkout review footer for COD';
$cms->setData($data)->save();

