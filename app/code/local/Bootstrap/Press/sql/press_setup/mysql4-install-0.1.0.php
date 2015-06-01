<?php
$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$installer->getTable('press/press')}` (
  `press_id` int(11) NOT NULL auto_increment,
  `title` varchar(100),
  `subtitle` varchar(100),
  `description` text,
  `category` varchar(50),
  `type` varchar(50), 
  `date` datetime default NULL,
  `thumbnail` varchar(50),
  `image` varchar(50),
  `url` varchar(100),
  `video` varchar(100),
  `pdf` varchar(50),
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`press_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
//demo 

//Mage::getModel('core/url_rewrite')->setId(null);

//demo 
$installer->endSetup();