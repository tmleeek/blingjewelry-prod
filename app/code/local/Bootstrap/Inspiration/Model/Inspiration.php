<?php
class Bootstrap_Inspiration_Model_Inspiration extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
		$this->_init('inspiration/inspiration');
    }
}