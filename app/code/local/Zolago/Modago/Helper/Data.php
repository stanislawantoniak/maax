<?php
class Zolago_Modago_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param      $categories
     * @param int  $level
     * @param int|bool $span
     *
     * @return array
     */
    public function  getCategoriesTree(Varien_Data_Tree_Node_Collection $categories, 
			$level = 1, $span = false, $allowVendorContext = true)
    {
		Varien_Profiler::start("todo: Zolago_Modago_Helper_Data::getCategoriesTree");
        $tree = array();
        foreach ($categories as $category) {
            $cat = Mage::getModel('catalog/category')->load($category->getId());

            $tree[$category->getId()] = array(
                'name'           => $category->getName(),
                'url'            => $allowVendorContext ? $cat->getUrl() : $cat->getNoVendorContextUrl(),
                'category_id'    => $category->getId(),
                'level'          => $level,
                'products_count' => $cat->getProductCount()
            );

//            echo Mage::getUrl($cat->getUrlPath())."\n";
            if ($level == 1) {
                $tree[$category->getId()]['image'] = $cat->getImage();
            }
            if ($span && $level >= $span) {
                continue;
            }
            if ($category->hasChildren()) {
                $children = Mage::getModel('catalog/category')->getCategories($category->getId());
                $tree[$category->getId()]['has_dropdown'] = self::getCategoriesTree($children, $level + 1, $span, $allowVendorContext);
            }else{
				$tree[$category->getId()]['has_dropdown']  = false;
			}
        }
		Varien_Profiler::start("todo: Zolago_Modago_Helper_Data::getCategoriesTree");
        return $tree;
    }

    /**
     * @param $parentId
     *
     * @return array
     */
    public function getSubCategories($parentId)
    {
        $children = Mage::getModel('catalog/category')->getCategories($parentId);
        $subCategories = array();
        if (!empty($children)) {
            foreach ($children as $cat) {
                $subCategories[$cat->getId()] = array(
                    'url'   => $cat->getRequestPath(),
                    'label' => $cat->getName()
                );
            }
        }
        return $subCategories;
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

	public function getAgreementHtml($type) {
		$types = array('tos','newsletter','sms','policy','register_info','dotpay','checkout');
		if(!in_array($type,$types)) {
			Mage::throwException("Incorrect agreement type, allowed types are: ".implode(", ",$types));
		}

		$agreementText = Mage::getStoreConfig('customer/agreements/'.$type);

		if(strpos($agreementText,"|") !== false) { //agreement text contains pipeline so we have to show 'more' button
			$agreementText = explode("|",$agreementText,2);
			$html = "";
			$html .= '<span class="agreement-short">'.$agreementText[0].'</span> ';
			$html .= '<a href="#" class="agreement-btn agreement-more-btn" onclick="Mall.showAgreement(this)">'.$this->__("more").'</a> ';
			$html .= '<span class="agreement-more"><br /><br />'.$agreementText[1].'</span> ';
			$html .= '<a href="#" class="agreement-btn agreement-less-btn" onclick="Mall.hideAgreement(this)">'.$this->__("less").'</a> ';

			if($type == "dotpay") {
				//todo: replace {vendors} with vendors' legal entities
			}

			return $html;
		} else {
			return $agreementText;
		}
	}
}
