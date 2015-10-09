<?php

// installation Vendor regulations accept text  cms blocks
$data =
	array(
		'title' => 'Vendor regulations accept text',
		'identifier' => 'vendor_regulations_accept',
		'content' =>
<<<EOD
<p>
Lorem Ipsum is simply dummy text of the printing and typesetting industry.
Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
<p>
Lorem Ipsum is simply dummy text of the printing and typesetting industry.
Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
EOD
	,
		'is_active' => 1,
		'stores' => 0

);

Mage::getModel('cms/block')->setData($data)->save();