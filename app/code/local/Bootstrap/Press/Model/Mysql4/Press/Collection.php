<?php
class Bootstrap_Press_Model_Mysql4_Press_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct(){
	 	//parent::_construct(); tutorial has this
		$this->_init("press/press");
	}
}
	 