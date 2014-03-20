<?php
/**
 * @category SolrBridge
 * @package Solrbridge_Solrsearch
 * @author	Hau Danh
 * @copyright	Copyright (c) 2011-2014 Solr Bridge (http://www.solrbridge.com)
 *
 */
class SolrBridge_Solrsearch_Model_Solr extends SolrBridge_Solrsearch_Model_Solr_Query
{

	protected $_solrData = null;

    public function query($queryText, $params = array())
    {
        $solrcore = Mage::helper('solrsearch')->getSetting('solr_index');

        $options = array('solrcore' => $solrcore, 'queryText' => $queryText, 'rows' => 20, 'facetlimit' => 200);

        if (!empty($params)) {
            $options = array_merge($options, $params);
        }

        $resultSet = $this->init($options)->prepareQueryData()->execute();

        $this->_solrData = $resultSet;

        return $resultSet;
    }

    public function getSolrData()
    {
        return $this->_solrData;
    }

    public function getCategoryFacets()
    {
    	$facetfield = 'category_path';
    	$query = '*:*';

    	$queryText = Mage::helper('solrsearch')->getParam('q');
    	if (!empty($queryText)) {
    		$query = 'category_text:('.$queryText.')';
    	}

    	$solrcore = Mage::helper('solrsearch')->getSetting('solr_index');

    	$queryUrl = Mage::helper('solrsearch')->getSetting('solr_server_url');

    	$arguments = array(
    			'json.nl' => 'map',
    			'wt'=> 'json',
    	);
    	$queryUrl = trim($queryUrl,'/').'/'.$solrcore;
    	$url = trim($queryUrl,'/').'/select/?q='.$query.'&rows=-1&facet=true&facet.field='.$facetfield.'&facet.mincount=1&facet.limit=5000';

    	$resultSet = Mage::getResourceModel('solrsearch/solr')->doRequest($url, $arguments, 'array');

    	$returnData = array();
    	if(isset($resultSet['facet_counts']['facet_fields'][$facetfield]) && is_array($resultSet['facet_counts']['facet_fields'][$facetfield]))
    	{
    		$returnData = $resultSet['facet_counts']['facet_fields'][$facetfield];
    	}

    	return $returnData;
    }

    public function getBrandsFacets()
    {
    	//display brand suggestion
    	$display_brand_suggestion = Mage::helper('solrsearch')->getSetting('display_brand_suggestion');
    	//display brand suggestion attribute code
    	$brand_attribute_code = Mage::helper('solrsearch')->getSetting('brand_attribute_code');
    	$brand_attribute_code = trim($brand_attribute_code);
    	if ($display_brand_suggestion > 0 && !empty($brand_attribute_code)) {
    		$facetfield = $brand_attribute_code.'_facet';
    	}


    	$query = '*:*';

    	$queryText = Mage::helper('solrsearch')->getParam('q');
    	if (!empty($queryText)) {
    		$query = $brand_attribute_code.'_text:('.$queryText.')';
    	}

    	$solrcore = Mage::helper('solrsearch')->getSetting('solr_index');

    	$queryUrl = Mage::helper('solrsearch')->getSetting('solr_server_url');

    	$arguments = array(
    			'json.nl' => 'map',
    			'wt'=> 'json',
    	);
    	$queryUrl = trim($queryUrl,'/').'/'.$solrcore;
    	$url = trim($queryUrl,'/').'/select/?q='.$query.'&rows=-1&facet=true&facet.field='.$facetfield.'&facet.mincount=1&facet.limit=5000';

    	$resultSet = Mage::getResourceModel('solrsearch/solr')->doRequest($url, $arguments, 'array');

    	$returnData = array();
    	if(isset($resultSet['facet_counts']['facet_fields'][$facetfield]) && is_array($resultSet['facet_counts']['facet_fields'][$facetfield]))
    	{
    		$returnData = $resultSet['facet_counts']['facet_fields'][$facetfield];
    	}

    	return $returnData;
    }
}