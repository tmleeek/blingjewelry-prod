<?php
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$installer->getConnection()
    ->dropColumn($installer->getTable('inspiration/inspiration'),'date');
$installer->endSetup();