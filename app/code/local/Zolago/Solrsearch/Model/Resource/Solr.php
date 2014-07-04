<?php

class Zolago_Solrsearch_Model_Resource_Solr extends SolrBridge_Solrsearch_Model_Resource_Solr
{
	public function deleteSolrDocumentByProductIds(array $productIds, $core) {
		if(!$productIds || !$core){
			return;
		}
		// Build delete query
		$solrServerUrl =$this->getSolrServerUrl();
		$separator = "+OR+";
		$queryProduct = "";
		
		foreach($productIds as $productId){
			$queryProduct .= "products_id:" . $productId . $separator;
		}
		$queryProduct = "<query>" . trim($queryProduct, $separator) . "</query>";
		
		$url = trim($solrServerUrl,'/').'/'.$core.
			'/update?stream.body=<delete>'.$queryProduct.'</delete>&commit=true&json.nl=map&wt=json';
		
    	$data = $this->doRequest($url);
		
		Mage::log($data);
					
	}
}