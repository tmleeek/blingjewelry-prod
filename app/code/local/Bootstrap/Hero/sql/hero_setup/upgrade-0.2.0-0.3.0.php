<?php
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('hero/hero'),
    'retina_image',
    'VARCHAR(50)'
);
$installer->getConnection()->addColumn($installer->getTable('hero/hero'),
    'mobile_image',
    'VARCHAR(50)'
);
$installer->endSetup();