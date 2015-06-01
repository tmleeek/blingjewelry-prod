<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    OnTap
 * @package     OnTap_Merchandiser
 * @copyright   Copyright (c) 2014 On Tap Networks Ltd. (http://www.ontapgroup.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OnTap_Merchandiser_Block_Search_Result extends Mage_Adminhtml_Block_Abstract
{
    const PAGE_SEARCH_RESULTS = 30;

    /**
     * getProductBlockHtml
     * 
     * @return array
     */
    public function getProductBlockHtml()
    {
        $productCollection = $this->getLoadedProductCollection()
            ->addAttributeToSelect(array(
                'entity_id',
                'visibility'
        ));
        
        $html = array();
        foreach ($productCollection as $_product) {
            $productBox =  $this->getLayout()
                ->createBlock('merchandiser/adminhtml_catalog_product_list')
                ->setTemplate('merchandiser/new/search/productbox.phtml');
                
            $productBox->setPid($_product->getId());
            $productBox->setCurrentPosition(1);
            $html[] = $productBox->toHtml();
        }
        
        return $html;
    }

    /**
     * getSearchModel
     * 
     * @return OnTap_Merchandiser_Model_Search
     */
    public function getSearchModel()
    {
        return Mage::getSingleton('merchandiser/search');
    }

    /**
     * _toHtml
     * 
     * @return string
     */
    public function _toHtml()
    {
        return parent::_toHtml();
    }
    
    /**
     * getLoadedProductCollection
     * 
     * @return Varien_Data_Collection
     */
    public function getLoadedProductCollection()
    {
        $params = $this->getRequest()->getParams();
        
        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $productCollection->addAttributeToSelect('*');
        $productCollection->addAttributeToFilter(
            array(
                array('attribute'=>'name', array('like' => "%".$params['q']."%")),
                array('attribute'=>'sku', array('like' => "%".$params['q']."%")),
            )
        );
        
        $visibleInCatalogIds = Mage::getModel('catalog/product_visibility')->getVisibleInCatalogIds();
        if (Mage::helper('merchandiser')->isHideInvisibleProducts()) {
            $productCollection->addAttributeToFilter('visibility', array(
                'or' => $visibleInCatalogIds
            ));
        }
        
        if (Mage::helper('merchandiser')->isHideDisabledProducts()) {
            $productCollection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        }
        
        return $productCollection;
    }
    
    /**
     * getResultCount
     * 
     * @return int
     */
    public function getResultCount()
    {
        return $this->getLoadedProductCollection()->count();
    }
}
