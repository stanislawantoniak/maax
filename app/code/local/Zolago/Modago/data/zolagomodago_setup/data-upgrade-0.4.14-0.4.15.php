<?php


// installation of mobile menu wrapper cms blocks

$newNavigationWrapperContent = <<<EOD
<div class="header_bottom">
<div class="container-fluid">
<nav role="navigation">
<div id="navigation">

{{block id='navigation-main-desktop'}}
{{block type="zolagomodago/page_header_navmobilemenu" name="navigation_main_mobile" template="page/html/header/top.mobile.menu.phtml"}}
{{block type='zolagomodago/catalog_category' block_id='category.main.menu.mobile' template='catalog/category/category.main.menu.mobile.phtml'}}
</div>

</nav>
</div>

</div>
<div id="clone_submenu" class="hidden-xs clearfix">
    <div class="container-fluid">

    </div>
</div>
EOD;
Mage::getModel('cms/block')->load('navigation-main-wrapper')->setData('content', $newNavigationWrapperContent)->save();


