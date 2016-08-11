<?php

// installation Vendor regulations accept text cms blocks
$cms =
	array(
		array(
			'title' => 'Vendor regulations  ACCEPTED text',
			'identifier' => 'vendor_regulations_accept_accepted',
			'content' =>
				<<<EOD
<div class="col-md-8 col-md-offset-2">
    <div class="">
        <div style="margin-top:100px;">
            <!--=== Page Header ===-->
            <div class="page-header">
                <div class="page-title">
                    <h3>Zaakceptowali Państwo regulamin serwisu MODAGO.PL.</h3>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    Dziękujemy za akceptację regulaminu. Zweryfikujemy Państwa akceptację najszybciej jak to możliwe. 
                </div>
            </div>
        </div>
    </div>
</div>

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