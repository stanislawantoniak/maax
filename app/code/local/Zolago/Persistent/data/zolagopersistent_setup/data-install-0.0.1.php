<?php
$data = array(
	'title'         => 'Persistent Forget Guest',
	'identifier'    => 'persistent_forget_guest',
	'content'       => '<p>Informacje o zapomnieniu niezalogowany [persistent_forget_guest]</p>',
	'is_active'     => 1,
	'stores'        => 0
);
	
Mage::getModel('cms/block')->setData($data)->save();