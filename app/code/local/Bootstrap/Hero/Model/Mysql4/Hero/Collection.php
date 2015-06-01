<?php
class Bootstrap_Hero_Model_Mysql4_Hero_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct(){
	 	//parent::_construct(); tutorial has this
		$this->_init("hero/hero");
	}
}
	 