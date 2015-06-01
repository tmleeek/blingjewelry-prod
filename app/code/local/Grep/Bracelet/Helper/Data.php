<?php
class Grep_Bracelet_Helper_Data extends Mage_Payment_Helper_Data
{
    protected $_pendants = null;
    protected $_count = null;
    
    public function getLimit()
    {
        return Mage::getStoreConfig('grep_bracelet/settings/charms_per_page');
    }
    
    public function getFilters()
    {
        $filters = Mage::app()->getRequest()->getParam('filters');
        
        return $filters;
    }
    
    public function getFilterAttribute()
    {
        return Mage::getStoreConfig('grep_bracelet/settings/filter_attribute_code');
    }
    
    public function getBracelets()
    {
        $category_id = Mage::getStoreConfig('grep_bracelet/settings/bracelet_category');
        
        $category = Mage::getModel('catalog/category')->load($category_id);
        
        $collection = $category->getProductCollection();
        $collection->addAttributeToSelect('*');
        
        return $collection;
    }
    
    public function getCount()
    {
        if ($this->_count == null) {
            $filters = Mage::app()->getRequest()->getParam('filters');
            if ($filters) {
                $filters = explode(',', $filters);
            }
            
            $category_id = Mage::getStoreConfig('grep_bracelet/settings/pendant_category');
            
            $category = Mage::getModel('catalog/category')->load($category_id);
            
            $collection = $category->getProductCollection();
            $collection->addAttributeToSelect('*');
            
            if (is_array($filters) && count($filters)) {
                $conditions = array();
                foreach ($filters as $filter) {
                    $conditions[] = array('attribute' => $this->getFilterAttribute(), 'finset' => $filter);
                }
                
                $collection->addAttributeToFilter($conditions);
            }
            
            $this->_count = count($collection);
        }
        
        return $this->_count;
    }
    
    public function getPendants()
    {
        if ($this->_pendants === null) {
            $limit = $this->getLimit();
            $page = Mage::app()->getRequest()->getParam('page');
            
            if (!$page) {
                $page = 1;
            }
            
            $filters = Mage::app()->getRequest()->getParam('filters');
            if ($filters) {
                $filters = explode(',', $filters);
            }
            
            $category_id = Mage::getStoreConfig('grep_bracelet/settings/pendant_category');
            
            $category = Mage::getModel('catalog/category')->load($category_id);
            
            $collection = $category->getProductCollection();
            $collection->addAttributeToSelect('*')->addAttributeToSort('price', 'ASC');
            
            if (is_array($filters) && count($filters)) {
                $conditions = array();
                foreach ($filters as $filter) {
                    $conditions[] = array('attribute' => $this->getFilterAttribute(), 'finset' => $filter);
                }
                
                $collection->addAttributeToFilter($conditions);
            }
            
            $query = (string)$collection->getSelect();
            
            $collection->setPage($page, $limit);
            
            $this->_pendants = $collection;
        }
        
        return $this->_pendants;
    }
    
    public function getPageCount()
    {
        return ceil($this->getCount()/$this->getLimit());
    }
    
    public function getChecked($id)
    {
        $filters = Mage::app()->getRequest()->getParam('filters');
        $filters = explode(',', $filters);
        
        if (in_array($id, $filters)) {
            return true;
        }
        
        return false;
    }
    
    public function getImage($product, $attribute, $x=null, $y=null)
    {
        $image = '';
        
        try {
            $image = Mage::helper('catalog/image')->init($product, $attribute)->resize($x, $y);
        } catch (Exception $e) {
            if ($attribute != 'image') {
                $image = $this->getImage($product, 'image', $x, $y);
            }
        }
        
        return $image;
    }
    
    public function getFilterValues()
    {
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $this->getFilterAttribute());
        $options = $attribute->getSource()->getAllOptions(true, true);
        
        $values = array();
        
        $category_id = Mage::getStoreConfig('grep_bracelet/settings/pendant_category');
        
        foreach ($options as $option) {
            if (isset($option['value']) && $option['value']) {
                $value = new Varien_Object();
                
                $value->setName($option['label']);
                $value->setValue($option['value']);
                $value->setChecked($this->getChecked($option['value']));
                
                $category = Mage::getModel('catalog/category')->load($category_id);
                
                $c = $category->getProductCollection();
                
                $count = $c->addAttributeToFilter($this->getFilterAttribute(), array('finset' => $option['value']))->count();
                
                $value->setCount($count);
                
                $values[$option['value']] = $value;
            }
        }
        
        return $values;
    }
}
