<?php

$installer = $this;

$installer->startSetup();

try{
$installer->run("
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/general/enabled', '0');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/general/store_uuid', '');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/general/kind', 'embedded');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/general/dialog_style_color', '');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/general/dialog_style_include_border', '');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/general/dialog_button_url', '');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/appearance/player_style', 'full');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/appearance/player_skin', 'default');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/appearance/allow_full_screen', '');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/appearance/show_play_button', '1');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/appearance/player_width', '');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/appearance/player_height', '');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/playback/auto_play', '');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/playback/loop_play', '');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/playback/play_on_click', '');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/playback/mute', '');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/playback/initial_volume', '');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/social/share_items', '');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/advance/atc_enable', '');
INSERT INTO {$this->getTable('core_config_data')} (`scope` ,`scope_id`, `path` ,`value`)
VALUES ('default', 0, 'video/configuration/advance/atc_label', '');
");
}catch(Exception $e){}


$installer->endSetup(); 
