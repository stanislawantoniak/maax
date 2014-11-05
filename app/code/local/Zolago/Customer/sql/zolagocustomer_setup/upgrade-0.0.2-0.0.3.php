<?php

$cms = array(
    array(
        'title'         => 'Notification settings general Subscription',
        'identifier'    => 'notification-settings-general-subscription',
        'content'       =>
<<<EOD
Wyrażam zgodę na przetwarzanie moich danych osobowych, w szczególności imienia, nazwiska, adresu zamieszkania, adresu poczty elektronicznej w celu przetwarzania ofert marketingowych i informacji handlowych przez Modago<span class="three-dots">... </span><a href="#" class="more-info"> więcej</a><span class="more-info-txt">.pl</span>
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