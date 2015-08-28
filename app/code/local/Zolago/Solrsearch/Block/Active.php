<?php

class Zolago_Solrsearch_Block_Active extends Zolago_Solrsearch_Block_Faces
{
	public function _construct(){
		parent::_construct();
		$this->setTemplate("zolagosolrsearch/standard/active.phtml");
	}
	
	public function _prepareLayout() {
		$this->setSkip(1);
		return parent::_prepareLayout();
	}

	public function getItemId($attributeCode, $item) {
		if($attributeCode=="price" && $this->getRequest()->getParam('slider')){
			return "filter_slider";
		}
		return parent::getItemId($attributeCode, $item);
	}
    public function isContentMode() {
        $category = $this->getCurrentCategory();
        $res = false;
        if($category){
            if ($category->getDisplayMode()==Mage_Catalog_Model_Category::DM_PAGE) {
                $res = true;
            }

			/*  @var $lpBlock Zolago_Catalog_Block_Campaign_LandingPage */
			$lpBlock = Mage::getBlockSingleton('zolagocatalog/campaign_landingPage');
			$lpData = $lpBlock->getData('campaign_landing_page');
			$lpData = (array)$lpData;

			if(!empty($lpData)){
				$res = false;
			}
            if (Mage::registry('is_search_mode')) {
                $res = false;
            }
        }
        return $res;
    }
}