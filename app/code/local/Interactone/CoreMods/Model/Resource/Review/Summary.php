<?php
/**
 * @category Interactone
 * @package Interactone_CoreMods
 * @author Alexey Poletaev (alexey.poletaev@cyberhull.com)
 */
class Interactone_CoreMods_Model_Resource_Review_Summary
    extends Mage_Review_Model_Resource_Review_Summary
{
    /**
     * @see Mage_Review_Model_Resource_Review_Summary::_getLoadSelect()
     * @author Alexey Poletaev (alexey.poletaev@cyberhull.com)
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        // $select->where('store_id = ?', (int)$object->getStoreId()); Ashwani Bhasin removed int due to error in logs "unable to convert to int" 8-29-2014
        $select->where('store_id = ?', $object->getStoreId());
        return $select;
    }
}