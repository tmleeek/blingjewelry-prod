<?php
/**
 * @category Interactone
 * @package Interactone_CoreMods
 * @author Alexey Poletaev (alexey.poletaev@cyberhull.com)
 */
class Interactone_CoreMods_Block_Adminhtml_Report_Product_Sold_Grid
    extends Mage_Adminhtml_Block_Report_Product_Sold_Grid
{
    /**
     * SKU column was added
     * @see Mage_Adminhtml_Block_Report_Product_Sold_Grid::_prepareColumns()
     * @author Alexey Poletaev (alexey.poletaev@cyberhull.com)
     */
    protected function _prepareColumns()
    {
        $this->addColumn('sku', array(
            'header'    =>Mage::helper('reports')->__('SKU'),
            'index'     =>'sku',
        ));

        return parent::_prepareColumns();
    }
}