<?php

$cms = array(
    array(
        'title'         => 'Brands page header',
        'identifier'    => 'brands-page-header',
        'content'       =>
            <<<EOD

<style>

@media screen and (min-width: 768px) {

ul ul {
            padding-left: 20px !important;
        }
ul ul ul {
            padding-left: 20px !important;
        }

}

@media screen and (min-width: 481px) and (max-width: 767px) {

ul ul {
            padding-left: 15px !important;
        }
ul ul ul {
            padding-left: 15px !important;
        }


.page-title, h1 {

       font-size: 28px;

        }

}

@media screen and (max-width: 480px) {
#content {padding-left: 5px}
#content {padding-right: 5px}
ul ul {
            padding-left: 8px !important;
        }
ul ul ul {
            padding-left: 8px !important;
        }

.page-title, h1 {

       font-size: 22px;

        }

}

.western {

text-align: center;
margin-bottom: 0px;
line-height: 120%;
font-size: 16px;
/*font-weight:bold;*/

}

</style>


<div class="brands-cms wrapp-section bg-w">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 brands-cms-title">
                <h1>
                    <span class="span-title">Marki</span>
                </h1>
            </div>
        </div>
        <div class="row">
            <div class="brands-cms-content col-sm-12">
                <p>Znajdziesz u nas topowe sklepy, oferujące najlepsze modowe marki. Dopiero wystartowaliśmy, ale już wkrótce otwarcie kolejnych sklepów. Nie ma marki, której szukasz? Zajrzyj do nas niedługo.</p>
            </div>
        </div>
    </div>
</div>

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

