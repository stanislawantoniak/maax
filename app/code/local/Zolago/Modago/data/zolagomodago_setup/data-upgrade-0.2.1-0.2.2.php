<?php
// fix for {{store direct_url=''}}
$newContent = <<<EOD
<ul class="nav nav-pills nav-stacked">
    <li>
        <a href="{{store direct_url='dla-niej/ubrania-dla-kobiet/bielizna.html'}}" style="color: #000000;">
            Bielizna </a>
    </li>
    <li>
        <a href="{{store direct_url='dla-niej/ubrania-dla-kobiet/bluzki.html'}}" style="color: #000000;">
            Bluzki </a>
    </li>
    <li>
        <a href="{{store direct_url='dla-niej/ubrania-dla-kobiet/bluzy.html'}}" style="color: #000000;">
            Bluzy </a>
    </li>
    <li>
        <a href="{{store direct_url='dla-niej/ubrania-dla-kobiet/koszule.html'}}" style="color: #000000;">
            Koszule </a>
    </li>
    <li>
        <a href="{{store direct_url='dla-niej/ubrania-dla-kobiet/t-shirty-koszulki.html'}}" style="color: #000000;">
            T-shirty, koszulki </a>
    </li>
    <li>
        <a href="{{store direct_url='dla-niej/ubrania-dla-kobiet/swetry.html'}}" style="color: #000000;">
            Swetry </a>
    </li>
    <li>
        <a href="{{store direct_url='dla-niej/ubrania-dla-kobiet/spodnie.html'}}" style="color: #000000;">
            Spodnie </a>
    </li>
    <li>
        <a href="{{store direct_url='dla-niej/ubrania-dla-kobiet/spodnice.html'}}" style="color: #000000;">
            Spódnice </a>
    </li>
    <li>
        <a href="{{store direct_url='dla-niej/ubrania-dla-kobiet/sukienki.html'}}" style="color: #000000;">
            Sukienki i tuniki </a>
    </li>
    <li>
        <a href="{{store direct_url='dla-niej/ubrania-dla-kobiet/kurtki-plaszcze.html'}}" style="color: #000000;">
            Kurtki, płaszcze </a>
    </li>
    <li>
        <a href="{{store direct_url='dla-niej/ubrania-dla-kobiet/kamizelki-bezrekawniki.html'}}"
           style="color: #000000;">
            Kamizelki, bezrękawniki </a>
    </li>
    <li>
        <a href="{{store direct_url='dla-niej/ubrania-dla-kobiet/marynarki-zakiety.html'}}" style="color: #000000;">
            Marynarki, żakiety </a>
    </li>
</ul>
EOD;

Mage::getModel('cms/block')->load('category-navigation-desktop-v-4')->setData('content', $newContent)->save();

