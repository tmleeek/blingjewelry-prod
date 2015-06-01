<?php
class Treepodia_Video_Block_View_Dialog extends Mage_Core_Block_Template
{
	
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
		if (!$this->getTemplate() || !Mage::getStoreConfig('video/general/enabled') || Mage::getStoreConfig('video/configuration/general/kind') != 'dialog') {
			return '';
		}
		$html = $this->renderView();
		return $html;
    }
	
	public function getDialogHtml() {
		$_product = $this->_getData('product');
        if (!$_product)
            $_product = Mage::registry('product');
		$productName = addslashes($_product->getName());
		$productName = $this->htmlEscape($productName);
		$dialog_button_url = trim(Mage::getStoreConfig('video/configuration/general/dialog_button_url'));
		if( empty($dialog_button_url) )
			$dialog_button_url = $this->getSkinUrl('treepodia/images/video-player.png');
		$dialog_style_color = Mage::getStoreConfig('video/configuration/general/dialog_style_color');
		if( empty($dialog_style_color) )
			$dialog_style_color = 'FFFFFF';
		$dialog_style_include_border = 'false';
		if( Mage::getStoreConfig('video/configuration/general/dialog_style_include_border') == '1' )
			$dialog_style_include_border = 'true';
		$_html = '<a id="video-btn" class="play-video" href="javascript:showVideoDialog(\''.$productName.'\', video, \'#'.$dialog_style_color.'\', '.$dialog_style_include_border.')" title="Play Video"><img src="'.$dialog_button_url.'" /></a>';
		return $_html;	
	}
}
?>