<?php
class Bootstrap_Inspiration_Block_Adminhtml_Inspiration_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
   public function __construct()
   {
       parent::__construct();
       $this->setId('inspirationGrid');
       $this->setDefaultSort('inspiration_id');
       $this->setDefaultDir('ASC');
       $this->setSaveParametersInSession(true);
   }
   protected function _prepareCollection()
   {
      $collection = Mage::getModel('inspiration/inspiration')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
	}
   protected function _prepareColumns()
   {
       $this->addColumn('inspiration_id',
             array(
                    'header' => 'ID',
                    'align' =>'right',
                    'width' => '50px',
                    'index' => 'inspiration_id',
               ));
       $this->addColumn('title',
               array(
                    'header' => 'Title',
                    'align' =>'left',
                    'index' => 'title',
              ));
       $this->addColumn('category', array(
                    'header' => 'Category',
                    'align' =>'left',
                    'index' => 'category',
             ));
       $this->addColumn('type', array(
                    'header' => 'Type',
                    'align' =>'left',
                    'index' => 'type',
             ));
       $this->addColumn('sort_order', array(
                    'header' => 'Sort Order',
                    'align' =>'left',
                    'index' => 'sort_order',
             ));
         return parent::_prepareColumns();
    }
    public function getRowUrl($row)
    {
         return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}