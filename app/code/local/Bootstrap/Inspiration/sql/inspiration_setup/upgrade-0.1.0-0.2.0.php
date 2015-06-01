<?php
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$installer->getConnection()
    ->addColumn($installer->getTable('inspiration/inspiration'),
    'sort_order',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => false,
        'default' => 100,
        'comment' => 'Sort Order'
    )
);
$installer->endSetup();