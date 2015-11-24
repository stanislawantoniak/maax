<?php

// installation Vendor regulations accept text cms blocks
$cms =
	array(
		array(
			'title' => 'Vendor regulations accept text (wzór, nie kasować!)',
			'identifier' => 'vendor_regulations_accept',
			'content' =>
				<<<EOD
{{if vendor.regulation_accept_text_top}}
<p>{{var vendor.regulation_accept_text_top}}</p>
{{else}}
<p>Aby prowadzić sprzedaż w serwisie MODAGO musisz zaakceptować jego
	regulamin.</p>
<p>Regulamin musi zostać zaakceptowany przez osobę upoważnioną do jednoosobowego
	reprezentowania
	firmy {{var vendor.company_name}} lub przez osobę posiadającą pełnomocnictwo
	do akceptacji regulaminu
	MODAGO wystawione przez osoby upoważnione do reprezentacji firmy.</p>
{{/if}}
<br/>
<p>
	<b>Pobierz wzór pełnomocnictwa:</b></p>
<p>
	<span>&nbsp;
	<span class="red">
	<i class="fa fa-file-pdf-o fa-lg"></i>
	</span>
	<a target="_blank" style="text-decoration:underline" href="{{if vendor.regulation_proxy_assignment_url}}{{var vendor.regulation_proxy_assignment_url}}{{else}}http://modago.pl/media/pdf/formularz_odstapienia_od_umowy.pdf{{/if}}">
		{{if vendor.regulation_proxy_assignment_url_text}}
			{{var vendor.regulation_proxy_assignment_url_text}}
		{{else}}
			Pełnomocnictwo do akceptacji regulaminu MODAGO
		{{/if}}
	</a>
	</span>
</p>
EOD
		,
			'is_active' => 1,
			'stores' => 0

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