<?php
class Bootstrap_Press_Model_Press extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
		$this->_init('press/press');
    }
}