<?php
if(version_compare(Mage::getVersion(), '1.4.0.0', '>=') || version_compare(Mage::getVersion(), '1.6.0.0', '<=')){
//Code
class Mage_Catalog_Block_Product_Special extends Mage_Catalog_Block_Product_List
{
   function get_prod_count()
   {
      //unset any saved limits
      Mage::getSingleton('catalog/session')->unsLimitPage();
      return (isset($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : 40;
   }// get_prod_count
public function getLimit()
    {
        Mage::getSingleton('catalog/session')->unsLimitPage();
        parent::getLimit();
    }
   function get_cur_page()
   {
      return (isset($_REQUEST['p'])) ? intval($_REQUEST['p']) : 1;
   }// get_cur_page

   /**
    * Retrieve loaded category collection
    *
    * @return Mage_Eav_Model_Entity_Collection_Abstract
   **/
   protected function _getProductCollection()
   {
      $todayDate = date('m/d/y');
      $tomorrow = mktime(0, 0, 0, date('m'), date('d')+1, date('y'));
      $tomorrowDate = date('m/d/y', $tomorrow);

        $collection = Mage::getResourceModel('catalogsearch/advanced_collection')
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addMinimalPrice()
            ->addStoreFilter();
       
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
       
        $collection->addAttributeToFilter('special_from_date', array('date' => true, 'to' => $todayDate))
            ->addAttributeToFilter('special_to_date', array('or'=> array(
            0 => array('date' => true, 'from' => $tomorrowDate),
            1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left');
	$collection->addAttributeToFilter('special_price', array('gt' => 0)) /* added by Keyur some products with just sale date set existed  */ 
       ->addAttributeToSort('entity_id', 'desc')
	->setPageSize($this->get_prod_count())
        ->setCurPage($this->get_cur_page());
      $this->setProductCollection($collection);

      return $collection;
   }// _getProductCollection
}// Mage_Catalog_Block_Product_Special
}