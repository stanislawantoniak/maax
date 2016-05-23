<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */


$installer->getConnection()->insert($installer->getTable('zolagorma/rma_reason'), array(
	"name"		=>	"dostawa niewłaściwego produktu",
	"auto_days"	=>	30,
	"allowed_days"	=>	30,
	"message"	=> "Niestety nie możesz już zwrócić tego produktu. Minął termin, który zgodnie z regulaminem sklepu przysługiwał na zwrot.  Jeśli masz jakieś wątpliwości czy pytania, skontaktuj się ze sklepem. Więcej informacji o zasadach zwrotu przeczytasz na stronach Pomoc.",
	"visible_on_front" => true,
));

$installer->getConnection()->insert($installer->getTable('zolagorma/rma_reason'), array(
	"name"		=>	"produkt za mały",
	"auto_days"	=>	30,
	"allowed_days"	=>	30,
	"message"	=> "Niestety nie możesz już zwrócić tego produktu. Minął termin, który zgodnie z regulaminem sklepu przysługiwał na zwrot.  Jeśli masz jakieś wątpliwości czy pytania, skontaktuj się ze sklepem. Więcej informacji o zasadach zwrotu przeczytasz na stronach Pomoc.",
	"visible_on_front" => true,
));

$installer->getConnection()->insert($installer->getTable('zolagorma/rma_reason'), array(
	"name"		=>	"produkt za duży",
	"auto_days"	=>	30,
	"allowed_days"	=>	30,
	"message"	=> "Niestety nie możesz już zwrócić tego produktu. Minął termin, który zgodnie z regulaminem sklepu przysługiwał na zwrot.  Jeśli masz jakieś wątpliwości czy pytania, skontaktuj się ze sklepem. Więcej informacji o zasadach zwrotu przeczytasz na stronach Pomoc.",
	"visible_on_front" => true,
));

$installer->getConnection()->insert($installer->getTable('zolagorma/rma_reason'), array(
	"name"		=>	"reklamacja",
	"auto_days"	=>	0,
	"allowed_days"	=>	730,
	"message"	=> "Niestety nie możesz już zareklamować tego produktu. Reklamację można złożyć do 2 lat od odebrania produktu. Jeśli masz jakieś wątpliwości czy pytania, skontaktuj się ze sklepem. Więcej informacji o zasadach zwrotu przeczytasz na stronach Pomoc.",
	"visible_on_front" => true,
));

$installer->getConnection()->insert($installer->getTable('zolagorma/rma_reason'), array(
	"name"		=>	"produkt dotarł uszkodzony",
	"auto_days"	=>	7,
	"allowed_days"	=>	30,
	"message"	=> "Niestety nie możesz już zwrócić tego produktu. Minął termin, który zgodnie z regulaminem sklepu przysługiwał na zwrot.  Jeśli masz jakieś wątpliwości czy pytania, skontaktuj się ze sklepem. Więcej informacji o zasadach zwrotu przeczytasz na stronach Pomoc.",
	"visible_on_front" => true,
));

$installer->getConnection()->insert($installer->getTable('zolagorma/rma_reason'), array(
	"name"		=>	"rozmyśliłam/em się",
	"auto_days"	=>	30,
	"allowed_days"	=>	30,
	"message"	=> "Niestety nie możesz już zwrócić tego produktu. Minął termin, który zgodnie z regulaminem sklepu przysługiwał na zwrot.  Jeśli masz jakieś wątpliwości czy pytania, skontaktuj się ze sklepem. Więcej informacji o zasadach zwrotu przeczytasz na stronach Pomoc.",
	"visible_on_front" => true,
));

