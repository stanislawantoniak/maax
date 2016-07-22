<?php
/* no for standalone version
//1. Create footer default block CMS
$vissaviStore =  Mage::app()->getStore('vissavi')->getId();

$footerBlocks = array(
    array(
        'title' => 'Stopka Vissavi',
        'identifier' => 'footer-website',
        'content' => <<<EOD
<footer id="footer">
    <div class="container-fluid footer-default">
        <div class="col-xs-12">
            <div class="row">
                <div class="footer-logo ">
                    <a href="{{block type="core/template" template="page/html/footer/link-logo.phtml"}}">
                    <img src="{{block type="core/template" template="page/html/footer/image-logo.phtml"}}"  alt="{{config path='design/header/logo_alt'}}" />
                    </a>
                </div>
                <div class="footer-about ">
                    <ul class="hidden-sm hidden-xs">
                        <li><a href="{{store url='koszt-wysylki'}}"><i class="fa fa-angle-right"></i> Koszt wysyłki</a></li>
                        <li><a href="{{store url='wymiana-i-zwroty'}}"><i class="fa fa-angle-right"></i> Wymiana i zwroty</a></li>
                        <li><a href="{{store url='jak-mierzyc'}}"><i class="fa fa-angle-right"></i> Jak mierzyć</a></li>
                        <li><a href="{{store url='regulamin'}}"><i class="fa fa-angle-right"></i> Regulamin</a></li>
                        <li><a href="{{store url='help'}}"><i class="fa fa-angle-right"></i> Pomoc</a></li>
                        <li><a href="{{store url='o-nas'}}"><i class="fa fa-angle-right"></i> O nas</a></li>
                        <li><a href="{{store url='help/contact'}}"><i class="fa fa-angle-right"></i> Kontakt</a></li>
                        <li><a href="{{store url='storesmap'}}"><i class="fa fa-angle-right"></i> Znajdź sklep</a></li>
                    </ul>
                    <ul class="visible-sm visible-xs">
                        <li><a href="{{store url='koszt-wysylki'}}"><i class="fa fa-angle-right"></i> Koszt wysyłki</a></li>
                        <li><a href="{{store url='wymiana-i-zwroty'}}"><i class="fa fa-angle-right"></i> Wymiana i zwroty</a></li>
                        <li><a href="{{store url='jak-mierzyc'}}"><i class="fa fa-angle-right"></i> Jak mierzyć</a></li>
                        <li><a href="{{store url='regulamin'}}"><i class="fa fa-angle-right"></i> Regulamin</a></li>
                        <li><a href="{{store url='help'}}"><i class="fa fa-angle-right"></i> Pomoc</a></li>
                        <li><a href="{{store url='o-nas'}}"><i class="fa fa-angle-right"></i> O nas</a></li>
                        <li><a href="{{store url='help/contact'}}"><i class="fa fa-angle-right"></i> Kontakt</a></li>
                        <li><a href="{{store url='storesmap'}}"><i class="fa fa-angle-right"></i> Znajdź sklep</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
EOD
    ,
        'is_active' => 1,
        'stores' => $vissaviStore
    )
);


foreach ($footerBlocks as $data) {
    $collection = Mage::getModel('cms/block')->getCollection();
    $collection->addStoreFilter($data['stores']);
    $collection->addFieldToFilter("identifier", $data["identifier"]);
    $block = $collection->getFirstItem();

    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData, $data);
    }
    $block->setData($data)->save();
}
unset($data);

$pagesToCreate = array(
    array(
        'title' => 'Koszt wysyłki',
        'identifier' => 'koszt-wysylki',
        'content' =>
            <<<EOD
<section class="cms-section">
    <div class="title-section">
        <h2>Koszt wysyłki</h2>
    </div>
    <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam interdum luctus purus, vel rhoncus libero faucibus vel. Duis a mauris sit
        amet est iaculis fermentum. Nulla a massa vel erat fermentum tincidunt in vel dui. Donec condimentum tincidunt faucibus. Nulla facilisi.
        In luctus mollis orci, sed tristique orci. Duis consectetur, ex sit amet semper efficitur, nulla purus mollis neque, non imperdiet mi velit
        sit amet nibh.
    </p>
    <p>
        Duis eu ultricies purus, at fermentum lacus. Ut ac neque porta, elementum nulla vel, tristique ex. Maecenas gravida facilisis molestie.
        Quisque rhoncus leo ac nibh pharetra faucibus. Aliquam erat volutpat. Suspendisse elementum massa non vulputate ornare. Nulla viverra nisl
        sed sapien maximus sagittis. Ut vitae mauris at quam aliquam ornare sit amet at dolor. In urna dolor, sagittis vitae rhoncus et, cursus
        sollicitudin elit. Praesent commodo suscipit libero, a dignissim sem bibendum pellentesque. Ut cursus ex ut elit ornare auctor. Nullam
        faucibus pharetra massa, sit amet mollis diam congue in. Mauris lobortis justo et tellus tristique, a pharetra sem blandit. Fusce id
        volutpat urna, eu vehicula nulla. Suspendisse potenti. Vestibulum vitae erat venenatis, lacinia magna quis, porttitor leo.
    </p>
    <p>
        Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vestibulum vitae volutpat lectus. Donec auctor, est
        non pharetra semper, sem diam ultrices lacus, vel hendrerit dolor velit ut est. Nam vehicula, magna at imperdiet porta, mauris leo viverra
        nisi, non vehicula augue nulla sed justo. Etiam ut aliquet nunc, non consectetur turpis. Integer sollicitudin mollis consectetur.
        Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Praesent rhoncus neque vitae augue suscipit
        lobortis. Sed sit amet commodo velit, a maximus dolor.
    </p>
    <p>
        Cras vulputate auctor diam, nec fringilla orci. Nulla nec risus sed erat fermentum commodo ac in eros. Aenean ut placerat nunc, eu tempor
        nisl. Etiam eleifend cursus accumsan. In efficitur nulla ac fermentum aliquam. Praesent vestibulum aliquet nulla id auctor. Quisque nunc
        turpis, blandit placerat tristique quis, porttitor et dolor.
    </p>
    <p>
        Nulla placerat nibh convallis, consequat ex vel, maximus velit. Integer ac massa suscipit, hendrerit est vitae, egestas eros. Curabitur vel
        lacinia turpis. Nullam fermentum nunc vehicula felis aliquam, id semper libero tempus. Integer non orci cursus, dictum mauris luctus,
        condimentum neque. Nulla gravida sapien lorem, sit amet posuere mi venenatis at. Maecenas condimentum dolor sit amet vehicula lobortis.
        Donec cursus lectus non lacus hendrerit blandit. Sed viverra nisl lorem. Maecenas iaculis dictum nisl, a egestas ipsum suscipit nec. Cum
        sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas ornare metus venenatis massa commodo, eget mattis
        enim imperdiet. Duis ut sagittis velit.
    </p>
</section>
EOD
    ,
        'root_template' => 'one_column',
        'is_active' => 1,
        'stores' => $vissaviStore
    ),
    array(
        'title' => 'Jak mierzyć',
        'identifier' => 'jak-mierzyc',
        'content' =>
            <<<EOD
<section class="cms-section">
    <div class="title-section">
        <h2>Jak mierzyć</h2>
    </div>
    <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam interdum luctus purus, vel rhoncus libero faucibus vel. Duis a mauris sit
        amet est iaculis fermentum. Nulla a massa vel erat fermentum tincidunt in vel dui. Donec condimentum tincidunt faucibus. Nulla facilisi.
        In luctus mollis orci, sed tristique orci. Duis consectetur, ex sit amet semper efficitur, nulla purus mollis neque, non imperdiet mi velit
        sit amet nibh.
    </p>
    <p>
        Duis eu ultricies purus, at fermentum lacus. Ut ac neque porta, elementum nulla vel, tristique ex. Maecenas gravida facilisis molestie.
        Quisque rhoncus leo ac nibh pharetra faucibus. Aliquam erat volutpat. Suspendisse elementum massa non vulputate ornare. Nulla viverra nisl
        sed sapien maximus sagittis. Ut vitae mauris at quam aliquam ornare sit amet at dolor. In urna dolor, sagittis vitae rhoncus et, cursus
        sollicitudin elit. Praesent commodo suscipit libero, a dignissim sem bibendum pellentesque. Ut cursus ex ut elit ornare auctor. Nullam
        faucibus pharetra massa, sit amet mollis diam congue in. Mauris lobortis justo et tellus tristique, a pharetra sem blandit. Fusce id
        volutpat urna, eu vehicula nulla. Suspendisse potenti. Vestibulum vitae erat venenatis, lacinia magna quis, porttitor leo.
    </p>
    <p>
        Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vestibulum vitae volutpat lectus. Donec auctor, est
        non pharetra semper, sem diam ultrices lacus, vel hendrerit dolor velit ut est. Nam vehicula, magna at imperdiet porta, mauris leo viverra
        nisi, non vehicula augue nulla sed justo. Etiam ut aliquet nunc, non consectetur turpis. Integer sollicitudin mollis consectetur.
        Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Praesent rhoncus neque vitae augue suscipit
        lobortis. Sed sit amet commodo velit, a maximus dolor.
    </p>
    <p>
        Cras vulputate auctor diam, nec fringilla orci. Nulla nec risus sed erat fermentum commodo ac in eros. Aenean ut placerat nunc, eu tempor
        nisl. Etiam eleifend cursus accumsan. In efficitur nulla ac fermentum aliquam. Praesent vestibulum aliquet nulla id auctor. Quisque nunc
        turpis, blandit placerat tristique quis, porttitor et dolor.
    </p>
    <p>
        Nulla placerat nibh convallis, consequat ex vel, maximus velit. Integer ac massa suscipit, hendrerit est vitae, egestas eros. Curabitur vel
        lacinia turpis. Nullam fermentum nunc vehicula felis aliquam, id semper libero tempus. Integer non orci cursus, dictum mauris luctus,
        condimentum neque. Nulla gravida sapien lorem, sit amet posuere mi venenatis at. Maecenas condimentum dolor sit amet vehicula lobortis.
        Donec cursus lectus non lacus hendrerit blandit. Sed viverra nisl lorem. Maecenas iaculis dictum nisl, a egestas ipsum suscipit nec. Cum
        sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas ornare metus venenatis massa commodo, eget mattis
        enim imperdiet. Duis ut sagittis velit.
    </p>
</section>
EOD
    ,
        'root_template' => 'one_column',
        'is_active' => 1,
        'stores' => $vissaviStore
    ),
    array(
        'title' => 'O nas',
        'identifier' => 'o-nas',
        'content' =>
            <<<EOD
<section class="cms-section">
    <div class="title-section">
        <h2>O nas</h2>
    </div>
    <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam interdum luctus purus, vel rhoncus libero faucibus vel. Duis a mauris sit
        amet est iaculis fermentum. Nulla a massa vel erat fermentum tincidunt in vel dui. Donec condimentum tincidunt faucibus. Nulla facilisi.
        In luctus mollis orci, sed tristique orci. Duis consectetur, ex sit amet semper efficitur, nulla purus mollis neque, non imperdiet mi velit
        sit amet nibh.
    </p>
    <p>
        Duis eu ultricies purus, at fermentum lacus. Ut ac neque porta, elementum nulla vel, tristique ex. Maecenas gravida facilisis molestie.
        Quisque rhoncus leo ac nibh pharetra faucibus. Aliquam erat volutpat. Suspendisse elementum massa non vulputate ornare. Nulla viverra nisl
        sed sapien maximus sagittis. Ut vitae mauris at quam aliquam ornare sit amet at dolor. In urna dolor, sagittis vitae rhoncus et, cursus
        sollicitudin elit. Praesent commodo suscipit libero, a dignissim sem bibendum pellentesque. Ut cursus ex ut elit ornare auctor. Nullam
        faucibus pharetra massa, sit amet mollis diam congue in. Mauris lobortis justo et tellus tristique, a pharetra sem blandit. Fusce id
        volutpat urna, eu vehicula nulla. Suspendisse potenti. Vestibulum vitae erat venenatis, lacinia magna quis, porttitor leo.
    </p>
    <p>
        Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vestibulum vitae volutpat lectus. Donec auctor, est
        non pharetra semper, sem diam ultrices lacus, vel hendrerit dolor velit ut est. Nam vehicula, magna at imperdiet porta, mauris leo viverra
        nisi, non vehicula augue nulla sed justo. Etiam ut aliquet nunc, non consectetur turpis. Integer sollicitudin mollis consectetur.
        Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Praesent rhoncus neque vitae augue suscipit
        lobortis. Sed sit amet commodo velit, a maximus dolor.
    </p>
    <p>
        Cras vulputate auctor diam, nec fringilla orci. Nulla nec risus sed erat fermentum commodo ac in eros. Aenean ut placerat nunc, eu tempor
        nisl. Etiam eleifend cursus accumsan. In efficitur nulla ac fermentum aliquam. Praesent vestibulum aliquet nulla id auctor. Quisque nunc
        turpis, blandit placerat tristique quis, porttitor et dolor.
    </p>
    <p>
        Nulla placerat nibh convallis, consequat ex vel, maximus velit. Integer ac massa suscipit, hendrerit est vitae, egestas eros. Curabitur vel
        lacinia turpis. Nullam fermentum nunc vehicula felis aliquam, id semper libero tempus. Integer non orci cursus, dictum mauris luctus,
        condimentum neque. Nulla gravida sapien lorem, sit amet posuere mi venenatis at. Maecenas condimentum dolor sit amet vehicula lobortis.
        Donec cursus lectus non lacus hendrerit blandit. Sed viverra nisl lorem. Maecenas iaculis dictum nisl, a egestas ipsum suscipit nec. Cum
        sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas ornare metus venenatis massa commodo, eget mattis
        enim imperdiet. Duis ut sagittis velit.
    </p>
</section>
EOD
    ,
        'root_template' => 'one_column',
        'is_active' => 1,
        'stores' => $vissaviStore
    ),
    array(
        'title' => 'Regulamin',
        'identifier' => 'regulamin',
        'content' =>
            <<<EOD
<section class="cms-section">
    <div class="title-section">
        <h2>Regulamin</h2>
    </div>
    <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam interdum luctus purus, vel rhoncus libero faucibus vel. Duis a mauris sit
        amet est iaculis fermentum. Nulla a massa vel erat fermentum tincidunt in vel dui. Donec condimentum tincidunt faucibus. Nulla facilisi.
        In luctus mollis orci, sed tristique orci. Duis consectetur, ex sit amet semper efficitur, nulla purus mollis neque, non imperdiet mi velit
        sit amet nibh.
    </p>
    <p>
        Duis eu ultricies purus, at fermentum lacus. Ut ac neque porta, elementum nulla vel, tristique ex. Maecenas gravida facilisis molestie.
        Quisque rhoncus leo ac nibh pharetra faucibus. Aliquam erat volutpat. Suspendisse elementum massa non vulputate ornare. Nulla viverra nisl
        sed sapien maximus sagittis. Ut vitae mauris at quam aliquam ornare sit amet at dolor. In urna dolor, sagittis vitae rhoncus et, cursus
        sollicitudin elit. Praesent commodo suscipit libero, a dignissim sem bibendum pellentesque. Ut cursus ex ut elit ornare auctor. Nullam
        faucibus pharetra massa, sit amet mollis diam congue in. Mauris lobortis justo et tellus tristique, a pharetra sem blandit. Fusce id
        volutpat urna, eu vehicula nulla. Suspendisse potenti. Vestibulum vitae erat venenatis, lacinia magna quis, porttitor leo.
    </p>
    <p>
        Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vestibulum vitae volutpat lectus. Donec auctor, est
        non pharetra semper, sem diam ultrices lacus, vel hendrerit dolor velit ut est. Nam vehicula, magna at imperdiet porta, mauris leo viverra
        nisi, non vehicula augue nulla sed justo. Etiam ut aliquet nunc, non consectetur turpis. Integer sollicitudin mollis consectetur.
        Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Praesent rhoncus neque vitae augue suscipit
        lobortis. Sed sit amet commodo velit, a maximus dolor.
    </p>
    <p>
        Cras vulputate auctor diam, nec fringilla orci. Nulla nec risus sed erat fermentum commodo ac in eros. Aenean ut placerat nunc, eu tempor
        nisl. Etiam eleifend cursus accumsan. In efficitur nulla ac fermentum aliquam. Praesent vestibulum aliquet nulla id auctor. Quisque nunc
        turpis, blandit placerat tristique quis, porttitor et dolor.
    </p>
    <p>
        Nulla placerat nibh convallis, consequat ex vel, maximus velit. Integer ac massa suscipit, hendrerit est vitae, egestas eros. Curabitur vel
        lacinia turpis. Nullam fermentum nunc vehicula felis aliquam, id semper libero tempus. Integer non orci cursus, dictum mauris luctus,
        condimentum neque. Nulla gravida sapien lorem, sit amet posuere mi venenatis at. Maecenas condimentum dolor sit amet vehicula lobortis.
        Donec cursus lectus non lacus hendrerit blandit. Sed viverra nisl lorem. Maecenas iaculis dictum nisl, a egestas ipsum suscipit nec. Cum
        sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas ornare metus venenatis massa commodo, eget mattis
        enim imperdiet. Duis ut sagittis velit.
    </p>
</section>
EOD
    ,
        'root_template' => 'one_column',
        'is_active' => 1,
        'stores' => $vissaviStore
    ),
    array(
        'title' => 'Wymiana i zwroty',
        'identifier' => 'wymiana-i-zwroty',
        'content' =>
            <<<EOD
<section class="cms-section">
    <div class="title-section">
        <h2>Wymiana i zwroty</h2>
    </div>
    <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam interdum luctus purus, vel rhoncus libero faucibus vel. Duis a mauris sit
        amet est iaculis fermentum. Nulla a massa vel erat fermentum tincidunt in vel dui. Donec condimentum tincidunt faucibus. Nulla facilisi.
        In luctus mollis orci, sed tristique orci. Duis consectetur, ex sit amet semper efficitur, nulla purus mollis neque, non imperdiet mi velit
        sit amet nibh.
    </p>
    <p>
        Duis eu ultricies purus, at fermentum lacus. Ut ac neque porta, elementum nulla vel, tristique ex. Maecenas gravida facilisis molestie.
        Quisque rhoncus leo ac nibh pharetra faucibus. Aliquam erat volutpat. Suspendisse elementum massa non vulputate ornare. Nulla viverra nisl
        sed sapien maximus sagittis. Ut vitae mauris at quam aliquam ornare sit amet at dolor. In urna dolor, sagittis vitae rhoncus et, cursus
        sollicitudin elit. Praesent commodo suscipit libero, a dignissim sem bibendum pellentesque. Ut cursus ex ut elit ornare auctor. Nullam
        faucibus pharetra massa, sit amet mollis diam congue in. Mauris lobortis justo et tellus tristique, a pharetra sem blandit. Fusce id
        volutpat urna, eu vehicula nulla. Suspendisse potenti. Vestibulum vitae erat venenatis, lacinia magna quis, porttitor leo.
    </p>
    <p>
        Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vestibulum vitae volutpat lectus. Donec auctor, est
        non pharetra semper, sem diam ultrices lacus, vel hendrerit dolor velit ut est. Nam vehicula, magna at imperdiet porta, mauris leo viverra
        nisi, non vehicula augue nulla sed justo. Etiam ut aliquet nunc, non consectetur turpis. Integer sollicitudin mollis consectetur.
        Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Praesent rhoncus neque vitae augue suscipit
        lobortis. Sed sit amet commodo velit, a maximus dolor.
    </p>
    <p>
        Cras vulputate auctor diam, nec fringilla orci. Nulla nec risus sed erat fermentum commodo ac in eros. Aenean ut placerat nunc, eu tempor
        nisl. Etiam eleifend cursus accumsan. In efficitur nulla ac fermentum aliquam. Praesent vestibulum aliquet nulla id auctor. Quisque nunc
        turpis, blandit placerat tristique quis, porttitor et dolor.
    </p>
    <p>
        Nulla placerat nibh convallis, consequat ex vel, maximus velit. Integer ac massa suscipit, hendrerit est vitae, egestas eros. Curabitur vel
        lacinia turpis. Nullam fermentum nunc vehicula felis aliquam, id semper libero tempus. Integer non orci cursus, dictum mauris luctus,
        condimentum neque. Nulla gravida sapien lorem, sit amet posuere mi venenatis at. Maecenas condimentum dolor sit amet vehicula lobortis.
        Donec cursus lectus non lacus hendrerit blandit. Sed viverra nisl lorem. Maecenas iaculis dictum nisl, a egestas ipsum suscipit nec. Cum
        sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas ornare metus venenatis massa commodo, eget mattis
        enim imperdiet. Duis ut sagittis velit.
    </p>
</section>
EOD
    ,
        'root_template' => 'one_column',
        'is_active' => 1,
        'stores' => $vissaviStore
    )
);

foreach ($pagesToCreate as $pageData) {
    $collection = Mage::getModel('cms/page')->getCollection();
    $collection->addStoreFilter($pageData['stores']);
    $collection->addFieldToFilter('identifier',$pageData["identifier"]);
    $currentPage = $collection->getFirstItem();

    if ($currentPage->getId()) {
        $oldBlock = $currentPage->getData();
        $pageData = array_merge($oldBlock, $pageData);
    }

    $currentPage->setData($pageData)->save();
}

*/