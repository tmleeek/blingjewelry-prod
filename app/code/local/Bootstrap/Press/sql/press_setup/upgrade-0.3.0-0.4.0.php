<?php
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('press/press'),
    'circle',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => false,
        'default' => '',
        'comment' => 'Circle Image'
    )
);
$installer->getConnection()->addColumn($installer->getTable('press/press'),
    'circle_link',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => false,
        'default' => '',
        'comment' => 'Circle Link'
    )
);
$installer->endSetup();