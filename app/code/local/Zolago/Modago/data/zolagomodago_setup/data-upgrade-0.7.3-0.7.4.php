<?php
//scopes
$allStores = 0;
$modagoStore =  Mage::app()->getStore('default')->getId();

//update scope in 'informacje-o-modago' cms page
$page = Mage::getModel('cms/page')->getCollection()->addFieldToFilter('identifier', 'informacje-o-modago')->getFirstItem();
if($page && $page->getId()) {
	$page->setStores($modagoStore)->save();
}
unset($page);

//remove old pages (just to be sure that everything is set up right)
$pagesToRemove = Mage::getModel('cms/page')->getCollection();
$pagesToRemove->addFieldToFilter("identifier", array('in' => array("no-route")));
foreach($pagesToRemove as $pageToRemove) {
	$pageToRemove->delete();
}
unset($pagesToRemove);

//remove old pages (just to be sure that everything is set up right)
$pagesToRemove = Mage::getModel('cms/page')->getCollection();
$pagesToRemove->addStoreFilter($allStores);
$pagesToRemove->addFieldToFilter("identifier", array('in' => array("regulamin","o-nas")));
foreach($pagesToRemove as $pageToRemove) {
	$pageToRemove->delete();
}


$pagesToCreate = array(
	array(
		'title' => '404 - Nie znaleziono strony',
		'identifier' => 'no-route',
		'content' =>
			<<<EOD
<div id="content" class="container-fluid bg-w">
	<div class="page-title">
		<h1>Przepraszamy - nie znaleziono takiej strony.</h1>
	</div>
	<div class="col-sm-12">
		<div class="row">
			<p>Jeśli wpisałeś adres ręcznie w pasku przeglądarki upewnij się, że wpisałeś poprawny adres.</p>
			<p>Jeśli kliknąłeś na link - zapewne ten link jest przeterminowany.</p>
		</div>
		<div class="row">
			<p>Co możesz zrobić? Możesz użyć następujących linków.</p>
			<p>
				<ul>
					<li><a href={{config path="web/unsecure/base_url"}}>Strona główna <strong>Galerii Handlowej</strong></a></li></br>
					<li><a href="/ubrania-dla-kobiet.html">Ubrania dla <strong>kobiet</strong></a></li></br>
					<li><a href="/ubrania-dla-mezczyzn.html">Ubrania dla <strong>mężczyzn</strong></a></li></br>
					<li><a href="/dla-dziecka.html">Ubrania dla <strong>dzieci</strong></a></li></br>
					<li><strong><a href="#" onclick="history.go(-1); return false;">Wróć</a></strong> do poprzedniej strony.</li></br>
				</ul>
			</p>
		</div>
		<div class="row">
			Możesz także użyć wyszukiwarki w pasku na górze strony.
		</div>
	</div>
</div>
EOD
	,
		'is_active' => 1,
		'stores' => $modagoStore
	),
	array(
		'title' => '404 - Nie znaleziono strony',
		'identifier' => 'no-route',
		'content' =>
			<<<EOD
<div id="content" class="container-fluid bg-w">
	<div class="page-title">
		<h1>Przepraszamy - nie znaleziono takiej strony.</h1>
	</div>
	<div class="col-sm-12">
		<div class="row">
			<p>Jeśli wpisałeś adres ręcznie w pasku przeglądarki upewnij się, że wpisałeś poprawny adres.</p>
			<p>Jeśli kliknąłeś na link - zapewne ten link jest przeterminowany.</p>
		</div>
		<div class="row">
			<p><a href="#" onclick="history.go(-1); return false;"><strong>Wróć</strong> do poprzedniej strony.</a></p>
		</div>
		<div class="row">
			<p>Możesz także użyć wyszukiwarki w pasku na górze strony.</p>
		</div>
	</div>
</div>
EOD
	,
		'is_active' => 1,
		'stores' => $allStores
	),
	array(
		'title' => 'Regulamin',
		'identifier' => 'regulamin',
		'content' =>
			<<<EOD
<div id="content" class="container-fluid bg-w">
	<div class="page-title">
		<h1>REGULAMIN</h1>
	</div>
	<div class="col-sm-12">
		<p style="margin-bottom: 0; line-height: 110%">
			Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam interdum luctus purus, vel rhoncus libero faucibus vel. Duis a mauris sit amet est iaculis fermentum. Nulla a massa vel erat fermentum tincidunt in vel dui. Donec condimentum tincidunt faucibus. Nulla facilisi. In luctus mollis orci, sed tristique orci. Duis consectetur, ex sit amet semper efficitur, nulla purus mollis neque, non imperdiet mi velit sit amet nibh.
		</p>
		<p style="margin-bottom: 0; line-height: 110%">
			Duis eu ultricies purus, at fermentum lacus. Ut ac neque porta, elementum nulla vel, tristique ex. Maecenas gravida facilisis molestie. Quisque rhoncus leo ac nibh pharetra faucibus. Aliquam erat volutpat. Suspendisse elementum massa non vulputate ornare. Nulla viverra nisl sed sapien maximus sagittis. Ut vitae mauris at quam aliquam ornare sit amet at dolor. In urna dolor, sagittis vitae rhoncus et, cursus sollicitudin elit. Praesent commodo suscipit libero, a dignissim sem bibendum pellentesque. Ut cursus ex ut elit ornare auctor. Nullam faucibus pharetra massa, sit amet mollis diam congue in. Mauris lobortis justo et tellus tristique, a pharetra sem blandit. Fusce id volutpat urna, eu vehicula nulla. Suspendisse potenti. Vestibulum vitae erat venenatis, lacinia magna quis, porttitor leo.
		</p>
		<p style="margin-bottom: 0; line-height: 110%">
			Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vestibulum vitae volutpat lectus. Donec auctor, est non pharetra semper, sem diam ultrices lacus, vel hendrerit dolor velit ut est. Nam vehicula, magna at imperdiet porta, mauris leo viverra nisi, non vehicula augue nulla sed justo. Etiam ut aliquet nunc, non consectetur turpis. Integer sollicitudin mollis consectetur. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Praesent rhoncus neque vitae augue suscipit lobortis. Sed sit amet commodo velit, a maximus dolor.
		</p>
		<p style="margin-bottom: 0; line-height: 110%">
			Cras vulputate auctor diam, nec fringilla orci. Nulla nec risus sed erat fermentum commodo ac in eros. Aenean ut placerat nunc, eu tempor nisl. Etiam eleifend cursus accumsan. In efficitur nulla ac fermentum aliquam. Praesent vestibulum aliquet nulla id auctor. Quisque nunc turpis, blandit placerat tristique quis, porttitor et dolor.
		</p>
		<p style="margin-bottom: 0; line-height: 110%">
			Nulla placerat nibh convallis, consequat ex vel, maximus velit. Integer ac massa suscipit, hendrerit est vitae, egestas eros. Curabitur vel lacinia turpis. Nullam fermentum nunc vehicula felis aliquam, id semper libero tempus. Integer non orci cursus, dictum mauris luctus, condimentum neque. Nulla gravida sapien lorem, sit amet posuere mi venenatis at. Maecenas condimentum dolor sit amet vehicula lobortis. Donec cursus lectus non lacus hendrerit blandit. Sed viverra nisl lorem. Maecenas iaculis dictum nisl, a egestas ipsum suscipit nec. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas ornare metus venenatis massa commodo, eget mattis enim imperdiet. Duis ut sagittis velit.
		</p>
	</div>
</div>
EOD
	,
		'is_active' => 1,
		'stores' => $allStores
	),
	array(
		'title' => 'O nas',
		'identifier' => 'o-nas',
		'content' =>
			<<<EOD
	<div id="about" class="container-fluid bg-w">
	<div class="about-header">
		<div class="about-header-title">O nas</div>
		<div class="about-header-subtitle">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
	</div>
	<div>
		<p>
			Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam interdum luctus purus, vel rhoncus libero faucibus vel. Duis a mauris sit amet est iaculis fermentum. Nulla a massa vel erat fermentum tincidunt in vel dui. Donec condimentum tincidunt faucibus. Nulla facilisi. In luctus mollis orci, sed tristique orci. Duis consectetur, ex sit amet semper efficitur, nulla purus mollis neque, non imperdiet mi velit sit amet nibh.
		</p>
		<p>
			Duis eu ultricies purus, at fermentum lacus. Ut ac neque porta, elementum nulla vel, tristique ex. Maecenas gravida facilisis molestie. Quisque rhoncus leo ac nibh pharetra faucibus. Aliquam erat volutpat. Suspendisse elementum massa non vulputate ornare. Nulla viverra nisl sed sapien maximus sagittis. Ut vitae mauris at quam aliquam ornare sit amet at dolor. In urna dolor, sagittis vitae rhoncus et, cursus sollicitudin elit. Praesent commodo suscipit libero, a dignissim sem bibendum pellentesque. Ut cursus ex ut elit ornare auctor. Nullam faucibus pharetra massa, sit amet mollis diam congue in. Mauris lobortis justo et tellus tristique, a pharetra sem blandit. Fusce id volutpat urna, eu vehicula nulla. Suspendisse potenti. Vestibulum vitae erat venenatis, lacinia magna quis, porttitor leo.
		</p>
		<p>
			Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vestibulum vitae volutpat lectus. Donec auctor, est non pharetra semper, sem diam ultrices lacus, vel hendrerit dolor velit ut est. Nam vehicula, magna at imperdiet porta, mauris leo viverra nisi, non vehicula augue nulla sed justo. Etiam ut aliquet nunc, non consectetur turpis. Integer sollicitudin mollis consectetur. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Praesent rhoncus neque vitae augue suscipit lobortis. Sed sit amet commodo velit, a maximus dolor.
		</p>
		<p>
			Cras vulputate auctor diam, nec fringilla orci. Nulla nec risus sed erat fermentum commodo ac in eros. Aenean ut placerat nunc, eu tempor nisl. Etiam eleifend cursus accumsan. In efficitur nulla ac fermentum aliquam. Praesent vestibulum aliquet nulla id auctor. Quisque nunc turpis, blandit placerat tristique quis, porttitor et dolor.
		</p>
		<p>
			Nulla placerat nibh convallis, consequat ex vel, maximus velit. Integer ac massa suscipit, hendrerit est vitae, egestas eros. Curabitur vel lacinia turpis. Nullam fermentum nunc vehicula felis aliquam, id semper libero tempus. Integer non orci cursus, dictum mauris luctus, condimentum neque. Nulla gravida sapien lorem, sit amet posuere mi venenatis at. Maecenas condimentum dolor sit amet vehicula lobortis. Donec cursus lectus non lacus hendrerit blandit. Sed viverra nisl lorem. Maecenas iaculis dictum nisl, a egestas ipsum suscipit nec. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas ornare metus venenatis massa commodo, eget mattis enim imperdiet. Duis ut sagittis velit.
		</p>
	</div>
	<div class="about-footer">
		<div class="about-footer-header">
			Lorem ipsum dolor sit amet, consectetur adipiscing elit!
		</div>
		<div class="about-footer-text">
			Chcesz dowiedzieć się&nbsp;więcej? Masz jakieś pytania?
			<a href="/faq">Zobacz&nbsp;odpowiedzi&nbsp;na&nbsp;najczęściej&nbsp;zadawane&nbsp;pytania.</a>
		</div>
	</div>
</div>
EOD
	,
		'is_active' => 1,
		'stores'    => $allStores
	)
);

foreach ($pagesToCreate as $pageData) {
    $collection = Mage::getModel('cms/page')->getCollection();
    $collection->addStoreFilter($pageData['stores']);
    $collection->addFieldToFilter('identifier',$pageData["identifier"]);
    $currentPage = $collection->getFirstItem();

    if ($currentPage->getBlockId()) {
        $oldBlock = $currentPage->getData();
	    $blockData = array_merge($oldBlock, $pageData);
    }

	$currentPage->setData($pageData)->save();
}