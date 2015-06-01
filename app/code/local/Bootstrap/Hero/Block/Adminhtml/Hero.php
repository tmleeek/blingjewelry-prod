<?php
class Bootstrap_Hero_Block_Adminhtml_Hero extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
		// blockGroup/controller must match declaration in hero.xml
		// hero/adminhtml_hero
		// ie: blockGroup/controller = hero/adminhtml_hero
     	$this->_blockGroup = 'hero';
     	$this->_controller = 'adminhtml_hero';
     	//text in the admin header
     	$this->_headerText = 'Manage Hero';
     	//value of the add button
     	$this->_addButtonLabel = 'Add Hero Image';
     	parent::__construct();
     }
}