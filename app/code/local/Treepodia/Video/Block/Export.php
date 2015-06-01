<?php
class Treepodia_Video_Block_Export extends Mage_Core_Block_Template
{
	private function getDefaultStoreId() {
		$websites = Mage::app()->getWebsites(true);
		return $websites[1]->getDefaultStore()->getId();
	}
    public function getDataFeedUrl()
    {
        return Mage::app()->getStore($this->getDefaultStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . 'treepodia/api/get_data_feed/';
    }
	public function getVideoSitemapUrl() {
		return Mage::app()->getStore($this->getDefaultStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . 'treepodia/videositemap/';
	}
}
?>