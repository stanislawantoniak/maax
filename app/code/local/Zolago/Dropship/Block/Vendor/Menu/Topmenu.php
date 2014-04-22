<?php

class Zolago_Dropship_Block_Vendor_Menu_Topmenu
	extends Zolago_Dropship_Block_Vendor_Menu_Abstract
{
	/**
	 * @var array
	 */
	protected $_sections = array(
		self::ITEM_ORDER,
		self::ITEM_HELPDESK,
		self::ITEM_RMA
	);
	/**
	 * @return array
	 */
	public function getMenu() {
		return array_intersect_key($this->getFullMenu(), array_flip($this->getTopmenuSections()));
	}
	/**
	 * @return array
	 */
	public function getTopmenuSections(){
		return $this->_sections;
	}
	
	public function renderMenu(array $menu, $useContainer=false, $isSubmenu=false) {
		$str = $useContainer ? "<ul".($isSubmenu ? " class=\"dropdown-menu\"" : "") . "\">" : "";
		foreach($menu as $item){
			if(is_array($item)){
				$className = array();
				$hasChildren = isset($item["children"]);
				if($hasChildren){
					$className[] = "dropdown";
				}
				if(isset($item['class'])){
					$className += is_array($item['class']) ? $item['class'] : explode(" ", $item['class']);
				}

				$label = isset($item['label']) ? $this->escapeHtml($item['label']) : "No-Label";
				$arrow = $hasChildren ? " <i class=\"icon-caret-down small\"></i>" : "";
				if(isset($item['url'])){
					$anchor = "<a title=\"%s\" %s href=\"{$this->escapeHtml($item['url'])}\">%s%s%s</a>";
				}else{
					$anchor = "<a title=\"%s\" %s>%s%s%s</a>";
				}

				$icon = "";
				if(isset($item['icon']) && $isSubmenu){
					$icon = "<i class=\"{$item['icon']}\"></i>";
				}

				$dropdownString="";
				if($hasChildren){
					$dropdownString="class=\"dropdown-toggle\" data-toggle=\"dropdown\"";
				}
				
				$str .= count($className) ?  "<li class=\"".  implode(" ", $className)."\">" : "<li>";
				$str .= sprintf($anchor, $label, $dropdownString, $icon, $label, $arrow);
				if($hasChildren){
					$str.=$this->renderMenu($item["children"], true, true);
				}
				$str .= "</li>";
			}elseif(is_string($item) && $item==self::SEPARATOR && $isSubmenu){
				$str .= "<li class=\"divider\"></li>";
			}
		}
		$str .= $useContainer ? "</ul>" : "";
		return $str;
	}
}