<?php

// installation Vendor regulations accept text cms blocks
$cms =
	array(
		array(
			'title' => 'Vendor regulations accept text',
			'identifier' => 'vendor_regulations_accept',
			'content' =>
				<<<EOD
<p>Aby prowadzić sprzedaż w serwisie MODAGO musisz zaakceptować jego
                                            regulamin.</p>

                                        <p>Regulamin musi zostać zaakceptowany przez osobę upoważnioną do jednoosobowego
                                            reprezentowania
                                            firmy {{var vendor.company_name}} lub przez osobę posiadającą pełnomocnictwo
                                            do akceptacji regulaminu
                                            MODAGO wystawione przez osoby upoważnione do reprezentacji firmy.</p>

                                        <p>Pobierz wzór pełnomocnictwa:
                                            <span>
                                            <i class="fa fa-file-pdf-o fa-lg"></i>
                                            <a target="_blank"
                                               href="http://modago.pl/media/pdf/formularz_odstapienia_od_umowy.pdf">Pełnomocnictwo
                                                do akceptacji
                                                regulaminu MODAGO</a>
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