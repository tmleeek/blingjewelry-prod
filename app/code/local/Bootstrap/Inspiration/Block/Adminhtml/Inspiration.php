<?php
class Bootstrap_Inspiration_Block_Adminhtml_Inspiration extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
		// blockGroup/controller must match declaration in inspiration.xml
		// inspiration/adminhtml_inspiration
		// ie: blockGroup/controller = inspiration/adminhtml_inspiration
     	$this->_blockGroup = 'inspiration';
     	$this->_controller = 'adminhtml_inspiration';
     	//text in the admin header
     	$this->_headerText = 'Manage Inspiration';
     	//value of the add button
     	$this->_addButtonLabel = 'Add Inspiration Item';
     	parent::__construct();
     }
}