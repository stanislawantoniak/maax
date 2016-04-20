<?php

class ZolagoOs_OmniChannelVendorProduct_Helper_Protected
{
	public function getCfgFirstAttributeOptions($product) {
		$values = array();
		$_values = Mage::helper("udprod")->getCfgFirstAttributeValues($product);
		foreach ($_values as $_val) {
			$values[] = $_val["value"];
		}
		return $values;
	}

	public function prepareProductPostData($product, &$productData) {
		ZolagoOs_OmniChannel_Helper_Protected::validateLicense("ZolagoOs_OmniChannelVendorProduct");
		$origCategoryIds = $product->getCategoryIds();
		$product->setOrigData("category_ids", (array)$origCategoryIds);
		$categoryIds = $productData["category_ids"];
		if ($categoryIds === null && !$product->getId() && !Mage::helper("udprod")->getUseTplProdCategoryBySetId($product)) {
			$categoryIds = Mage::helper("udprod")->getDefaultCategoryBySetId($product);
		}

		if (null !== $categoryIds) {
			if (empty($categoryIds)) {
				$categoryIds = array();
			}
			if (is_string($categoryIds)) {
				$categoryIds = explode(",", $categoryIds);
			}
			$productData["category_ids"] = (array)$categoryIds;
		}

		$origWebsiteIds = $product->getWebsiteIds();
		$product->setOrigData("website_ids", (array)$origWebsiteIds);
		$websiteIds = $productData["website_ids"];
		if ($websiteIds === null && !$product->getId() && !Mage::helper("udprod")->getUseTplProdWebsiteBySetId($product)) {
			$websiteIds = Mage::helper("udprod")->getDefaultWebsiteBySetId($product);
		}

		if (null !== $websiteIds) {
			if (empty($websiteIds)) {
				$websiteIds = array();
			}
			if (is_string($websiteIds)) {
				$websiteIds = explode(",", $websiteIds);
			}
			$productData["website_ids"] = (array)$websiteIds;
		}

		$postImages = $productData["media_gallery"]["images"];
		if (!is_array($postImages) && 0 < strlen($postImages)) {
			$postImages = Mage::helper("core")->jsonDecode($postImages);
		}

		if (!is_array($postImages)) {
			$postImages = array();
		}

		if (isset($productData["media_gallery"]) && isset($productData["media_gallery"]["cfg_images"]) && !Mage::getSingleton("udprod/source")->isCfgUploadImagesSimple()) {
			$cfgPostImages = $productData["media_gallery"]["cfg_images"];
			if (!is_array($cfgPostImages) && 0 < strlen($cfgPostImages)) {
				$cfgPostImages = Mage::helper("core")->jsonDecode($cfgPostImages);
			}

			if (!is_array($cfgPostImages)) {
				$cfgPostImages = array();
			}

			$newImages = null;
			foreach ($cfgPostImages as $image) {
				if (!is_array($image)) {
					if (null === $newImages) {
						$newImages = array();
					}

					$image = Mage::helper("core")->jsonDecode($image);
					if (is_array($image)) {
						foreach ($image as &$img) {
							$img["super_attribute"]["main"] = $img["main"];
						}
						unset($img);
						$newImages = array_merge($newImages, $image);
					}
				} else {
					break;
				}
			}
			if (null !== $newImages) {
				$postImages = array_merge($postImages, $newImages);
			}
			$productData["media_gallery"]["images"] = Mage::helper("core")->jsonEncode($postImages);
		}

		$origMediaGallery = $product->getOrigData("media_gallery");
		if ($product->getId() && is_array($origMediaGallery) &&
			!empty($origMediaGallery["images"]) &&
			$product->getTypeId() == "configurable" &&
			($mediaGallery = $productData["media_gallery"]) &&
			!Mage::getSingleton("udprod/source")->isMediaCfgPerOptionHidden() &&
			!Mage::getSingleton("udprod/source")->isCfgUploadImagesSimple() &&
			!Mage::getSingleton("udprod/source")->isMediaCfgShowExplicit())
		{
			$origImages = $origMediaGallery["images"];
			if (!is_array($origImages) && 0 < strlen($origImages)) {
				$origImages = Mage::helper("core")->jsonDecode($origImages);
			}

			if (!is_array($origImages)) {
				$origImages = array();
			}

			$postImages = $mediaGallery["images"];
			if (!is_array($postImages) && 0 < strlen($postImages)) {
				$postImages = Mage::helper("core")->jsonDecode($postImages);
			}

			if (!is_array($postImages)) {
				$postImages = array();
			}

			$cfgFirstAttr = Mage::helper("udprod")->getCfgFirstAttribute($product);
			$cfgFirstAttrId = $cfgFirstAttr->getId();
			$cfgFirstAttrCode = $cfgFirstAttr->getAttributeCode();
			$usedFirstAttrVals = array();
			if (isset($productData["_cfg_attribute"]["quick_create"]) && is_array($productData["_cfg_attribute"]["quick_create"])) {
				foreach ($productData["_cfg_attribute"]["quick_create"] as $qcIdx => $qc) {
					if (isset($qc[$cfgFirstAttrCode]) && $qcIdx != "\$ROW") {
						$usedFirstAttrVals[] = $qc[$cfgFirstAttrCode];
					}
				}
			}

			$usedFirstAttrVals = array_unique($usedFirstAttrVals);
			$_origImages = array();
			foreach ($origImages as &$image) {
				if (!isset($image["super_attribute"][$cfgFirstAttrId]) || !in_array($image["super_attribute"][$cfgFirstAttrId], $usedFirstAttrVals)) {
					$image["removed"] = true;
					$_origImages[] = $image;
				}
			}
			unset($image);
			foreach ($postImages as &$image) {
				if (!isset($image["super_attribute"][$cfgFirstAttrId]) || !in_array($image["super_attribute"][$cfgFirstAttrId], $usedFirstAttrVals)) {
					$image["removed"] = true;
				}
			}
			unset($image);
			$allImages = array_merge($_origImages, $postImages);
			$_allImages = array();
			foreach ($usedFirstAttrVals as $usedFirstAttrVal) {
				foreach ($allImages as $allImg) {
					if ($allImg["removed"] || isset($allImg["super_attribute"][$cfgFirstAttrId]) && $allImg["super_attribute"][$cfgFirstAttrId] == $usedFirstAttrVal) {
						$_allImages[] = $allImg;
					}
				}
			}
			$allImages = $_allImages;
			foreach ($allImages as $image) {
				if (!empty($image["removed"])) {
					Mage::helper("udprod")->setNeedToUnpublish($product, "image_removed");
				}
				if (empty($image["value_id"])) {
					Mage::helper("udprod")->setNeedToUnpublish($product, "image_added");
				}
			}
			$mediaGallery["images"] = Mage::helper("core")->jsonEncode($allImages);
			$productData["media_gallery"] = $mediaGallery;
		}
	}
}


