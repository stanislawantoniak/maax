<?php
$newContent = <<<EOD
<div class="sidebar-second col-lg-3 col-md-4 col-sm-12 col-xs-12 col-lg-push-9 col-md-push-8">
    <section class="main bg-w  hidden-sm hidden-xs">
        <header>
            <h2 class="open">Dane do wysyłki</h2>
        </header>
        <div class="row">
			[dev]
            <div class="col-md-12  col-sm-4">
                <h4>Adres dostawy:</h4>
                <dl>
                    <dd>Tomasz Makowski</dd>
                    <dd>ul. Janowicka 6 m27</dd>
                    <dd>01-321 Warszawa</dd>
                    <dd>tel: 505555555</dd>
                </dl>
            </div>
            <div class="col-md-12  col-sm-4">
                <h4>Dokument Sprzedaży:</h4>
                <fieldset class="row clearfix" id="doc_pay">
                    <div class="form-group form-radio col-sm-6 ">
                        <input type="radio" class="css-radio" id="doc_pay_1" name="doc_pay">
                        <label class="css-label" for="doc_pay_1">paragon</label>
                    </div>
                    <div class="form-group form-radio col-sm-6">
                        <input type="radio" class="css-radio" id="doc_pay_2" name="doc_pay">
                        <label class="css-label" for="doc_pay_2">faktura</label>
                    </div>
                </fieldset>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <button class="button button-third large pull-right" id="step-1-prev-right">ZMIEŃ</button>
            </div>
        </div>
    </section>
    <section class="p main bg-w hidden-sm hidden-xs">
        <header>
            <h2 class="open">Dostawa i płatność</h2>
        </header>

        <div class="row">
			[dev]
            <div class="col-md-12  col-sm-4">
                <h4>Rodzaj dostawy:</h4>
                <dl>
                    <dd>Kurier - szybka wysyłka 24h</dd>
                </dl>
            </div>
            <div class="col-md-12  col-sm-4">
                <h4>Rodzaj płatności:</h4>
                <dl>
                    <dd>Przelew elektroniczny</dd>
                    <dd>Bank: mBank</dd>
                </dl>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <button class="button button-third large pull-right" id="step-1-prev-right">ZMIEŃ</button>
            </div>
        </div>

    </section>
</div>

EOD;


$cms = Mage::getModel('cms/block')->load('checkout-right-column-step-3');
if ($cms->getBlockId()) {
    $data = $cms->getData();
} else {
    $data = array(
        'stores' => 0,
        'is_active' => 1,
        'identifier' => 'checkout-right-column-step-3',
        'title' => 'Checkout review footer',
    );
}
$data['content'] = $newContent;
$cms->setData($data)->save();

