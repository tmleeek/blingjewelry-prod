<?php
class Bootstrap_Press_Block_Adminhtml_Press extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
		// blockGroup/controller must match declaration in press.xml
		// press/adminhtml_press
		// ie: blockGroup/controller = press/adminhtml_press
     	$this->_blockGroup = 'press';
     	$this->_controller = 'adminhtml_press';
     	//text in the admin header
     	$this->_headerText = 'Manage Press';
     	//value of the add button
     	$this->_addButtonLabel = 'Add Press Item';
     	parent::__construct();
     }
}