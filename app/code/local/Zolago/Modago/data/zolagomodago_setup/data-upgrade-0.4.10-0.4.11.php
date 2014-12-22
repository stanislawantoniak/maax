<?php

// installation privacy-settings-remember-me-description cms blocks
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Privacy settings remember me description',
        'identifier'    => 'privacy-settings-remember-me-description',
        'content'       => <<<EOD
Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),
	array(
        'title'         => 'Notification settings general Subscription',
        'identifier'    => 'notification-settings-general-subscription',
        'content'       =>
<<<EOD
Wyrażam zgodę na przetwarzanie moich danych osobowych, w szczególności imienia, nazwiska, adresu zamieszkania, adresu poczty elektronicznej w celu przetwarzania ofert marketingowych i informacji handlowych przez Modago.pl
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