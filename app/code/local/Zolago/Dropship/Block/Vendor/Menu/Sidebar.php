<?php

class Zolago_Dropship_Block_Vendor_Menu_Sidebar 
	extends Zolago_Dropship_Block_Vendor_Menu_Abstract
{
	public function getMenu() {
		return $this->getFullMenu();
	}
	
	public function renderMenu(array $menu, $useContainer=false, $isSubmenu=false) {
		$str = $useContainer ? "<ul".($isSubmenu ? " class=\"sub-menu\"" : "") . ">" : "";
		foreach($menu as $item){
			if(is_array($item)){
				$className = array();
				$hasChildren = isset($item["children"]);
				if(isset($item['class'])){
					$className += is_array($item['class']) ? $item['class'] : explode(" ", $item['class']);
				}
				if(isset($item['active']) && $item['active']){
					$className[] = "current";
					$className[] = "open";
				}

				$label = isset($item['label']) ? $this->escapeHtml($item['label']) : "No-Label";

				if(isset($item['url'])){
					$anchor = "<a title=\"%s\" href=\"{$this->escapeHtml($item['url'])}\">%s%s</a>";
				}else{
					$anchor = "<a title=\"%s\">%s%s</a>";
				}

				$icon = "";
				if(isset($item['icon'])){
					$icon = "<i class=\"{$item['icon']}\"></i>";
				}

				$str .= count($className) ?  "<li class=\"".  implode(" ", $className)."\">" : "<li>";
				$str .= sprintf($anchor, $label, $icon, $label);
				if($hasChildren){
					$str.=$this->renderMenu($item["children"], true, true);
				}
				$str .= "</li>";
			}
		}
		$str .= $useContainer ? "</ul>" : "";
		return $str;
	}
}