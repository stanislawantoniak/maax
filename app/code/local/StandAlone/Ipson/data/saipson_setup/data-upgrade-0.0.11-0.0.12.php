<?php
$cms = array(

    array(
        'title'         => 'wishlist not logged full (default)',
        'identifier'    => 'wishlist-not-logged-full',
        'content'       => <<<EOD
<style><!--
h1.page-title {

    padding-top: 10px;
    padding-bottom: 20px;
    text-align: center;
}

li.cms-content {
    padding: 5px;
    margin-left: 10px
}

#table-shipping p {
    margin-top: 0;
    margin-bottom: 0;
}

#table-shipping .table-shipping-a {
    float: left;
    padding-right: 1%;
    width: 50%;
    position: relative;
}

#table-shipping .table-shipping-b {
    width: 49%;
    position: relative;
    float: left;
}

@media (max-width: 558px) {
    #table-shipping .table-shipping-a {
        float: none;
        width: 100%;
        /*text-align: center;*/
    }

    #table-shipping .table-shipping-b {
        width: 100%;
    }
}

#table-shipping {
    min-width: inherit;
}

/*#table-shipping .table-shipping-c:not(:empty) {
    padding-bottom: 20px;
}*/

.table-shipping-desktop {
    display: block;
}

.table-shipping-mobile {
    display: none;
}

/*
Max width before this PARTICULAR table gets nasty
This query will take effect for any screen smaller than 760px
and also iPads specifically.
*/
.table-shipping-desktop table,
.table-shipping-mobile {
    text-align: left;
    border-collapse: collapse;
    width: 100%;
}

.table-shipping-desktop table tr td,
.table-shipping-mobile table tr td {
    border: 1px solid black !important;
    padding: 5px;
}

@media only screen and (max-width: 780px),
(min-device-width: 780px) and (max-device-width: 1024px) {

    #table-shipping h1 {
        font-size: 1.4em;
    }

    #table-shipping ol,
    #table-shipping ul {
        margin-left: -15px;
    }

    #table-shipping tr {
        border: 1px solid #ccc;
        height: auto !important;
    }

    #table-shipping tr:not(:first-child) td {
        width: 50%;
    }

    .table-shipping-desktop {
        display: none;
    }

    .table-shipping-mobile {
        display: block;
    }
}

@media screen and (max-width: 767px) {
    h1.page-title {
        padding-top: 30px;
        font-size: 22px;

    }
}
--></style>
<div class="wishlist-cms wrapp-section bg-w">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 wishlist-cms-title">
                <h1 class="page-title">TWOJA LISTA ZAKUP&Oacute;W</h1>
            </div>
        </div>
        <div class="row">
            <div class="wishlist-cms-content col-sm-12">
                <p class="fontBlack">Chcesz mieć zawsze dostęp do swojej listy zakup&oacute;w?</p>
                <p style="display: inline;">Zaloguj się lub zał&oacute;ż konto żeby zostały zapamiętane zawsze jak się zalogujesz, niezależne od miejsca i urządzenia.</p>
                <a class="underline" href="/customer/account/login/">Zaloguj&nbsp;się</a>
                <p>Nie masz jeszcze konta? Utw&oacute;rz konto w zaledwie 10 sekund! <a class="underline" href="/customer/account/create/">Zał&oacute;ż&nbsp;konto</a></p>
                <br />
                <p style="font-size: 14px;">Możesz usunąć produkt z listy klikając na przycisk "usuń z listy"</p>
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