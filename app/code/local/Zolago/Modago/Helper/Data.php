<?php
class Zolago_Modago_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param Varien_Data_Tree_Node_Collection $categories
     * @param int $level
     * @param bool|false $span
     * @param bool|true $allowVendorContext
     * @param bool|FALSE $vendorContext
     * @return array
     */
    public function  getCategoriesTree(Varien_Data_Tree_Node_Collection $categories,
                                       $level = 1, $span = false, $allowVendorContext = true, $vendorContext = FALSE)
    {

        $vendor = $vendorContext ? Mage::helper('umicrosite')->getCurrentVendor() : FALSE;

        $tree = array();
        /** @var Varien_Data_Tree_Node $category */
        foreach ($categories as $category) {

            $cat = Mage::getModel('catalog/category')->load($category->getId());
            $solrProductCount = $cat->getSolrProductsCount($cat, $vendor);
            if ($solrProductCount <= 0) {
                continue;
            }
            if($solrProductCount > 0){
                $tree[$category->getId()] = array(
                    'name' => $category->getName(),
                    'url' => $allowVendorContext ? $cat->getUrl() : $cat->getNoVendorContextUrl(),
                    'category_id' => $category->getId(),
                    'level' => $level,
                    "solr_product_count" => $solrProductCount
                );

                if ($level == 1) {
                    $tree[$category->getId()]['image'] = $cat->getImage();
                }
            }

            if ($span && $level >= $span) {
                continue;
            }
            if ($category->hasChildren()) {
                $children = Mage::getModel('catalog/category')->getCategories($category->getId());
                $tree[$category->getId()]['has_dropdown'] = self::getCategoriesTree($children, $level + 1, $span, $allowVendorContext, $vendorContext);
            } else {
                $tree[$category->getId()]['has_dropdown'] = false;
            }
        }
        return $tree;
    }

    protected function _calculatePluralIndex($num)
    {
        $few = 1;
        if($num === 1) {
            return 0;
        }

        return ($num % 10 >= 2 && $num % 10 <= 4 && ($num % 100 < 10 || $num % 100 >= 20)) ? 1 : 2;
    }

    public function getPluralForm($num, array $forms)
    {
        return array_key_exists($this->_calculatePluralIndex($num), $forms) ? $forms[$this->_calculatePluralIndex($num)] : "";
    }

    public function getVendorQuestionFormAction()
    {
        return Mage::getUrl('udqa/customer/post');
    }

    protected $_checkoutVendors = array();

	public function getAgreementHtml($type) {
		$types = array('tos','newsletter','sms','policy','register_info','dotpay','checkout','ceneo');
		if(!in_array($type,$types)) {
			Mage::throwException("Incorrect agreement type, allowed types are: ".implode(", ",$types));
		}

		$agreementText = Mage::getStoreConfig('customer/agreements/'.$type);

		if(strpos($agreementText,"|") !== false) { //agreement text contains pipeline so we have to show 'more' button
			$agreementText = explode("|",$agreementText,2);
			$html = "";
			$html .= '<span class="agreement-short">'.$agreementText[0].'</span> ';
			$html .= '<a href="#" class="agreement-btn agreement-more-btn" onclick="Mall.showAgreement(this)">'.$this->__("more").'</a> ';
			$html .= '<span class="agreement-more">'.$agreementText[1].'</span> ';
			$html .= '<a href="#" class="agreement-btn agreement-less-btn" onclick="Mall.hideAgreement(this)">'.$this->__("less").'</a> ';

			$return = $html;
		} else {
			$return =  $agreementText;
		}

		if($type == "checkout") {
            if(!$this->_checkoutVendors) {
                $this->_checkoutVendors = Mage::registry('checkoutVendors');
                Mage::unregister('checkoutVendors');
            }

			if(is_array($this->_checkoutVendors) && count($this->_checkoutVendors)) {
				$return = str_replace("{vendors}",implode(", ",$this->_checkoutVendors),$return);
			}
		}

		return $return;
	}
	
    /**
     * returns cached cms block
     *
     * @param string $blockId cms block id
     * @param string $groupId cache group
     * @param string $cacheKeySuffix cache key suffix
     * @return string
     */
     public function getCachedCmsBlock($blockId,$groupId,$cacheKeySuffix = '') {
         $lambda = function($params) {
             return Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($params['blockId'])->toHtml();
         };
         /** @var Zolago_Dropship_Model_Vendor|false $vendor */
         $vendor = Mage::helper("umicrosite")->getCurrentVendor();
         $cacheKey = $blockId.'_'.$cacheKeySuffix.'_'.($vendor ? (int)$vendor->getId() : 0).'_'.Mage::app()->getStore()->getId();
         return Mage::helper('zolagocommon')->getCache($cacheKey,$groupId,$lambda,array('blockId' => $blockId));
         
     }
     
    /**
     * polish suffix for products
     *
     * @param int $count
     * @return string
     */
     public function polishProductSuffix($count) {
         if ('pl_PL'== Mage::app()->getLocale()->getLocaleCode()) {
             if (
                 ($count % 100 == 12) || ($count % 100 == 13) || ($count % 100 == 14) // 12,13,14
                 || !(($count % 10 == 2) || ($count % 10 == 3) || ($count % 10 == 4))) {

                 return sprintf('%s produktów',$count);
             } else {
                 return sprintf('%s produkty',$count);
             }
         } else {
             return $this->__('%s products',$count);
         }
     }
}
