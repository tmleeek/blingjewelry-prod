<?php
$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$installer->getTable('hero/hero')}` (
  `hero_id` int(11) NOT NULL auto_increment,
  `name` varchar(100), 
  `description` text, 
  `image` varchar(50),
  `bg_image` varchar(50),
  `link` varchar(100),
  `sort_order` int(11),
  `active` char(1),
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`hero_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
//demo 

//Mage::getModel('core/url_rewrite')->setId(null);

//demo 
$installer->endSetup();