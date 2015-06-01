<?php
$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$installer->getConnection()->dropColumn($installer->getTable('hero/hero'), 'bg_image');
$installer->endSetup();