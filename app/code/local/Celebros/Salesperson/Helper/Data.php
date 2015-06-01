<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Helper_Data extends Mage_CatalogSearch_Helper_Data
{
	const QUERY_VAR_NAME = 'q';
	const MAX_QUERY_LEN  = 200;
	const ICONV_CHARSET = 'UTF-8';

	/**
	 * Query object
	 *
	 * @var Mage_CatalogSearch_Model_Query
	 */
	protected $_query;

	/**
	 * QwiserSearchApi object
	 *
	 * @var Mage_CatalogSearch_Model_Query
	 */
	protected $_api;

	/**
	 * Query string
	 *
	 * @var string
	 */
	protected $_queryText;

	/**
	 * Note messages
	 *
	 * @var array
	 */
	protected $_messages = array();

	/**
	 * Is a maximum length cut
	 *
	 * @var bool
	 */
	protected $_isMaxLength = false;

	protected $_gmessages;

	protected $_bannerImage;
	protected $_customMessage;
	protected $_relatedSearches;

	/**
	 * Retrieve salesperson session
	 *
	 * @return Mage_Catalog_Model_Session
	 */
	protected function _getSession()
	{
		return Mage::getSingleton('salesperson/session');
	}

	/**
	 * Retrieve search query parameter name
	 *
	 * @return string
	 */
	public function getQueryParamName()
	{
		return self::QUERY_VAR_NAME;
	}

	public function getSalespersonApi()
	{
		if (!$this->_api) {
			$this->_api = Mage::getModel('salesperson/salespersonSearchApi');
		}
		return $this->_api;
	}

	public function getSalespersonAnlxApi()
	{
		return Mage::getModel('salesperson/salespersonAnalyticsApi');
	}

	/**
	  *
	  * Clean non UTF-8 characters
	  *
	  * @param string $string
	  * @return string
	  * @author Sveta Oksen copied from Magento v1.5
	  * @since 31/03/2011
	  */
	public function cleanString($string)
	{
		return '"libiconv"' == ICONV_IMPL ? iconv(self::ICONV_CHARSET, self::ICONV_CHARSET . '//IGNORE', $string) : $string;
	}

	public function getQueryText()
	{
		if($this->getSalespersonApi()->results){
			return $this->getSalespersonApi()->results->SearchInformation->Query;
		}
		if (is_null($this->_queryText)) {
			$this->_queryText = $this->_getRequest()->getParam($this->getQueryParamName());
			if ($this->_queryText === null) {
				$this->_queryText = '';
			} else {
				if (is_array($this->_queryText)) {
					$this->_queryText = null;
				}
				$this->_queryText = trim($this->_queryText);
				$this->_queryText = $this->cleanString($this->_queryText);

				if (Mage::helper('core/string')->strlen($this->_queryText) > $this->getMaxQueryLength()) {
					$this->_queryText = Mage::helper('core/string')->substr(
					$this->_queryText,
					0,
					$this->getMaxQueryLength()
					);
					$this->_isMaxLength = true;
				}
			}
		}
		return $this->_queryText;
	}

	/**
	 * Retrieve HTML escaped search query
	 *
	 * @return string
	 */
	public function getEscapedQueryText()
	{
		return $this->htmlEscape($this->getQueryText());
	}

	public function getDefaultPageSize(){
		if (substr(Mage::getStoreConfig('catalog/frontend/list_mode'),0,4) == 'grid'){
			return Mage::getStoreConfig('catalog/frontend/grid_per_page');
		}
		else {
			return Mage::getStoreConfig('catalog/frontend/list_per_page');
		}
	}

	public function getDefaultSortByField(){
		return Mage::getStoreConfig('catalog/frontend/default_sort_by');
	}

	public function getStoreSearchProfile(){
		return Mage::getStoreConfig('salesperson/display_settings/search_profile');
	}

	/**
	 * Is a minimum query length
	 *
	 * @return bool
	 */
	public function isMinQueryLength()
	{
		if (Mage::helper('core/string')->strlen($this->getQueryText()) < $this->getMinQueryLength()) {
			return true;
		}
		return false;
	}

	/**
	 * Retrieve minimum query length
	 *
	 * @param mixed $store
	 * @return int
	 */
	public function getMinQueryLength($store = null)
	{
		return Mage::getStoreConfig(Mage_CatalogSearch_Model_Query::XML_PATH_MIN_QUERY_LENGTH, $store);
	}

	/**
	 * Retrieve result page url and set "secure" param to avoid confirm
	 * message when we submit form from secure page to unsecure
	 *
	 * @param   string $query
	 * @return  string
	 */
	public function getResultUrl($query = null)
	{
		$store_id = Mage::app()->getStore()->getId();
		$module_disabled = Mage::app()->getStore($store_id)->getConfig('advanced/modules_disable_output/Celebros_Salesperson');
		
		$helperPath =  $module_disabled ? 'catalogsearch/result' : 'salesperson/result';
	
		return $this->_getUrl($helperPath, array(
            '_query' => array(self::QUERY_VAR_NAME => $query),
            '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()
		));
	}

	/**
	 * Prepare save query for result
	 *
	 * @return Mage_CatalogSearch_Model_Query
	 */
	public function prepare(Mage_CatalogSearch_Model_Query $query, $num_result)
	{
		if (!$query->getId()) {
			$query->setIsActive(0);
			$query->setIsProcessed(0);
			$query->setNumResults($num_result);
			$query->save();
			$query->setIsActive(1);
		}

		return $this;
	}

	/**
	 * Retrieve suggest url
	 *
	 * @return string
	 */
	public function getSuggestUrl()
	{
		return $this->_getUrl('catalogsearch/ajax/suggest');
	}

	/**
	 * Retrieve search term url
	 *
	 * @return string
	 */
	public function getSearchTermUrl()
	{
		return $this->_getUrl('catalogsearch/term/popular');
	}

	/**
	 * Retrieve advanced search URL
	 *
	 * @return string
	 */
	public function getAdvancedSearchUrl()
	{
		return $this->_getUrl('catalogsearch/advanced');
	}

	/**
	 * Retrieve maximum query words count for like search
	 *
	 * @param mixed $store
	 * @return int
	 */
	public function getMaxQueryWords($store = null)
	{
		return Mage::getStoreConfig(Mage_CatalogSearch_Model_Query::XML_PATH_MAX_QUERY_WORDS, $store);
	}

	/**
	 * Retrieve maximum query length
	 *
	 * @param mixed $store
	 * @return int
	 */
	public function getMaxQueryLength($store = null)
	{
		return Mage::getStoreConfig(Mage_CatalogSearch_Model_Query::XML_PATH_MAX_QUERY_LENGTH, $store);
	}

	/**
	 * Add Note message
	 *
	 * @param string $message
	 * @return Mage_CatalogSearch_Helper_Data
	 */
	public function addNoteMessage($message)
	{
		$this->_messages[] = $message;
		return $this;
	}

	/**
	 * Set Note messages
	 *
	 * @param array $messages
	 * @return Mage_CatalogSearch_Helper_Data
	 */
	public function setNoteMessages(array $messages)
	{
		$this->_messages = $messages;
		return $this;
	}

	/**
	 * Get the recommended attribute from salesperson API and add it to NoteMessages
	 *
	 */
	public function getRecommendedMessages(){
		if($this->getSalespersonApi()->results->GetRecommendedMessage() != ''){
			$message = preg_replace('/#%/', '', $this->getSalespersonApi()->results->GetRecommendedMessage());
			$message = preg_replace('/%#/', '', $message);
			$this->addNoteMessage($this->__($message));
		}
	}

	/**
	 * Retrieve Current Note messages
	 *
	 * @return array
	 */
	public function getNoteMessages()
	{
		return $this->_messages;
	}

	/**
	 * Check query of a warnings
	 *
	 * @param mixed $store
	 * @return Celebros_Salesperson_Helper_Data
	 */
	public function checkNotes($store = null)
	{
		if ($this->_isMaxLength) {
			$this->addNoteMessage($this->__('Maximum Search query  length is %s. Your query was cut.', $this->getMaxQueryLength()));
		}

		$stringHelper = Mage::helper('core/string');
		/* @var $stringHelper Mage_Core_Helper_String */

	}


	public function getNonLeadQuestionsPosition(){
		if (Mage::getStoreConfig('salesperson/display_settings/display_non_lead_top'))
		return 'top';
		elseif (Mage::getStoreConfig('salesperson/display_settings/display_non_lead_left'))
		return 'left';
		elseif (Mage::getStoreConfig('salesperson/display_settings/display_non_lead_right'))
		return 'right';
	}
	public function goToProductOnOneResult(){
		return Mage::getStoreConfigFlag('salesperson/display_settings/go_to_product_on_one_result');
	}

	public function getResultCount()
	{
		if($this->getSalespersonApi()->results){
			return $this->getSalespersonApi()->results->GetRelevantProductsCount();
		}
	}

	public function getNoteHelperMessages() {
		return $this->_gmessages;
	}

	public function setNoteHelperMessages($messages) {
		$this->_gmessages = $messages;
	}

	public function setRelatedSearches($relatedSearches){
		return $this->_relatedSearches = $relatedSearches;
	}

	public function setBannerImage($img){
		$this->_bannerImage = $img;
	}
	
	public function setBannerFlash($url){
		$this->_bannerFlash = $url;
	}

	public function setCustomMessage($msg){
		$this->_customMessage = $msg;
	}

	public function getBannerImage(){
		return $this->_bannerImage != '' ? $this->_bannerImage :  false;
	}
	
	public function getBannerFlash(){
		return $this->_bannerFlash != '' ? $this->_bannerFlash :  false;
	}

	public function getCustomMessage(){
		return $this->_customMessage != '' ? $this->_customMessage : false;
	}

	public function getRelatedSearches(){
		return $this->_relatedSearches;
	}
	
	public function isAutoComplete() {
		$res = Mage::getStoreConfig('salesperson/autocomplete_settings/ac_frontend_address')!="" && 
		Mage::getStoreConfig('salesperson/autocomplete_settings/ac_scriptserver_address')!="";
		return $res;
	}
	
	public function getSiteKey() {
		return Mage::getStoreConfig('salesperson/general_settings/sitekey');
	}	
	
	public function getACFrontServerAddress() {
		return Mage::getStoreConfig('salesperson/autocomplete_settings/ac_frontend_address');
	}

	public function getACScriptServerAddress() {
		return Mage::getStoreConfig('salesperson/autocomplete_settings/ac_scriptserver_address');
	}
	
	public function is_EE() {

		/*
		 * determine Magento Edition
		*/
		return file_exists('LICENSE_EE.txt');
	}
	
	public function is_PE() {
		return file_exists('LICENSE_PRO.html');
	}	
	
	public function is_CE() {
		return !$this->is_EE() && !$this->is_PE();
	}
	
	public function is_1_3() {
		$aVersionInfo = Mage::getVersionInfo();
		return $aVersionInfo["major"] == '1' and $aVersionInfo["minor"] == '3';
	}
	
	public function array_implode( $glue, $separator, $array ) {
		if ( ! is_array( $array ) ) return $array;
		$string = array();
		foreach ( $array as $key => $val ) {
			if ( is_array( $val ) )
				$val = implode( ',', $val );
			$string[] = "{$key}{$glue}{$val}";
	
		}
		return implode( $separator, $string );
	}
}
