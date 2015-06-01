<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Block_Result extends Mage_Core_Block_Template
{
	/**
	 * Catalog Product collection
	 *
	 * @var Mage_CatalogSearch_Model_Mysql4_Fulltext_Collection
	 */
	protected $_productCollection;
	
	/**
	 * Retrieve salesperson session
	 *
	 * @return Mage_Catalog_Model_Session
	 */

	var $helper;

	protected function _getSession()
	{
		return Mage::getSingleton('salesperson/session');
	}

	/**
	 * Retrieve QwiserSearchApi model object
	 *
	 * @return Mage_CatalogSearch_Model_Query
	 */
	protected function _getSalespersonApi()
	{
		if(!$this->helper)
			$this->helper = $this->helper('salesperson');

		return $this->helper->getSalespersonApi();
	}

	protected function _getSalespersonAnlxApi()
	{
		if(!$this->helper)
			$this->helper = $this->helper('salesperson');

		return $this->helper->getSalespersonAnlxApi();
	}

	/**
	 * Prepare layout
	 *
	 * @return Mage_CatalogSearch_Block_Result
	 */
	protected function _prepareLayout()
	{
		// add Home breadcrumb
		$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
		if ($breadcrumbs) {
			$title = $this->__("Search results for: '%s'", $this->helper('salesperson')->getQueryText());

			$breadcrumbs->addCrumb('home', array(
                'label' => $this->__('Home'),
                'title' => $this->__('Go to Home Page'),
                'link'  => Mage::getBaseUrl()
			));
			if(Mage::getStoreConfigFlag('salesperson/display_settings/breadcrumbs') && count($this->_getSalespersonApi()->results->SearchPath->Items) > 0){
				$breadcrumbs->addCrumb('search', array(
		                'label' => $title,
		                'title' => $title,
						'link' => Mage::Helper('salesperson')->getResultUrl($this->helper('salesperson')->getQueryText())
				));
				$searchPathEntry = 0;
				$paths = array();
				$totalEntries = $this->_getSalespersonApi()->results->SearchPath->Count;
				$searchPaths = $this->_getSalespersonApi()->results->SearchPath->Items;
				for($i = 0; $i < $totalEntries; $i++){
					$paths[] = $searchPaths[($totalEntries - 1) - $i]->Answers->Items[0]->Id;
				}
				array_pop($paths);
				foreach($searchPaths as $key => $searchPath){
					$crumb = array(
			                'label' => $searchPath->Answers->Items[0]->Text,
			                'title' => $searchPath->Answers->Items[0]->Text,
					);
					$searchPathEntry++;
					if($searchPathEntry < $totalEntries){
						$crumb['link'] = Mage::getBlockSingleton('salesperson/layer_state')->getRemoveAnswersFromBredcrumbsUrl($paths);
						array_pop($paths);
					}
					$breadcrumbs->addCrumb($searchPath->Answers->Items[0]->Id, $crumb);
				}
			}
			else {
				$breadcrumbs->addCrumb('search', array(
	                'label' => $title,
	                'title' => $title,
				));
			}
		}

		// modify page title
		$title = $this->__("Search results for: '%s'", $this->helper('salesperson')->getEscapedQueryText());
		$this->getLayout()->getBlock('head')->setTitle($title);
		Mage::Helper('salesperson')->setRelatedSearches($this->hasRelatedSearches());
		
		return parent::_prepareLayout();
	}

	/**
	 * Retrieve search list toolbar block
	 *
	 * @return Mage_Catalog_Block_Product_List
	 */
	public function getListBlock()
	{
		return $this->getChild('search_result_list');
	}

	/**
	 * Set search available list orders
	 *
	 * @return Mage_CatalogSearch_Block_Result
	 */
	public function setListOrders() {
		//echo "<!-- " . print_r($this, true) . " -->";
		return $this;
	}

	/**
	 * Set available view mode
	 *
	 * @return Mage_CatalogSearch_Block_Result
	 */
	public function setListModes() {
		        $this->getListBlock()
		            ->setModes(array(
		                'grid' => $this->__('Grid'),
		                'list' => $this->__('List'))
		            );
		return $this;
	}

	public function setCanonical() {
		
        $headBlock = $this->getLayout()->getBlock('head');
		if ($headBlock) {
			$url = $_SERVER['REQUEST_URI'];
			if(!$url) return $this;
			$url = '/salesperson/result/index/';
			if(isset($_GET['q']))
				$url .= '?q=' . $_GET['q'];
			//$url = str_replace('&', '&amp;', preg_replace('/&?(SI|x|y|dir|sort)=[^&]*/i', '', $url));

			$headBlock->_data['items'] = array_merge(array("link_rel/$url" => array('type' => 'link_rel', 'name' => $url, 'params' => 'rel="canonical"')), $headBlock->_data['items']);
			//$headBlock->addLinkRel('canonical', $url);
			if(isset($_GET['debug'])&&$_GET['debug']=='1')
			{
				header('Content-Type: text/plain');
				print_r($headBlock->_data);
				die();
			}
		}
		return $this;
	}

	/**
	 * Set Search Result collection
	 *
	 * @return Mage_CatalogSearch_Block_Result
	 */
	public function setListCollection() {

	}

	/**
	 * Retrieve Search result list HTML output
	 *
	 * @return string
	 */
	public function getProductListHtml()
	{
		 
		return $this->getChildHtml('search_result_list');
	}

	/**
	 * Retrieve loaded category collection
	 *
	 * @return Celebros_Helper_QwiserApi_QwiserProduct
	 */
	protected function getProductCollection()
	{
		if (is_null($this->_productCollection)) {
			$query = $this->_getSalespersonApi();
			if(count($query->qsr->Products)>0){
				$this->_productCollection = $query->qsr->Products->Items;
			}
		}

		return $this->_productCollection;
	}

	/**
	 * Retrieve search result count
	 *
	 * @return string
	 */
	public function getResultCount()
	{
		if($this->_getSalespersonApi()->results){
			return $this->_getSalespersonApi()->results->GetRelevantProductsCount();
		}
	}

	/**
	 * Retrieve no Minimum query length Text
	 *
	 * @return string
	 */
	public function getNoMinQueryLengthText()
	{
		if (Mage::helper('salesperson')->isMinQueryLength()) {
			return Mage::helper('salesperson')->__('Minimum Search query length is %s', $this->_getSalespersonApi()->getMinQueryLength());
		}
		return $this->_getData('no_result_text');
	}

	
	public function hasRelatedSearches(){
		if(!empty($this->_getSalespersonApi()->results->RelatedSearches)){
			$relatedSearches = $this->_getSalespersonApi()->results->RelatedSearches;
			$out = array();
			foreach ($relatedSearches as $key => $relatedSearch){
			$urlParams = array();
				$urlParams['_current']  = false;
				$urlParams['_escape']   = true;
				$urlParams['_use_rewrite']   = true;
				$urlParams['_query']    = array(
	        	'q' => $relatedSearch,
				);
				$out[$relatedSearch] = Mage::getUrl('*/*/index', $urlParams);
			}
			return $out;
		}
		return false;
	}
	
	public function getBannerImage(){
		return $this->bannerImage != '' ? $this->bannerImage : false;
	}
	
	public function getCustomMessage(){
		return $this->customMessage != '' ? $this->customMessage : false;
	}
	
	
	/**
	 * get's the Celebros_Analytics_SearchResults function from the API
	 * and return the image
	 */
	/*public function getAnlxSerachResultFunction(){
		return Mage::getModel('salesperson/api_anlx_analyticsFunctions')
			->Celebros_Analytics_SearchResults(
			Mage::Helper('salesperson')->getSalespersonApi()->results->GetSearchHandle(),//sSearchHandle
	    	Mage::Helper('salesperson')->getSalespersonApi()->results->GetLogHandle(),//sLogHandle
	    	Mage::getStoreConfig('salesperson/general_settings/sitekey'),//sUserID
	    	'1',//sGroupID
	    	$this->_getSession()->getId(),//sWebSessionID
	    	$_SERVER['PHP_SELF'],//sPreviousPageURL
	    	true,//bIsSSL
    		true//bFromQwiser
    		);
	}*/
}
