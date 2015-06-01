<?php
class Bootstrap_Inspiration_Model_Mysql4_Inspiration_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct(){
	 	//parent::_construct(); tutorial has this
		$this->_init("inspiration/inspiration");
	}
}
	 