<?php

class Grep_Minicart_Block_Messages extends Mage_Core_Block_Messages
{
    public function getGroupedHtml()
    {
        $html = parent::getGroupedHtml();
        $html = "<div class='messages-block'>$html</div>";
        return $html;
    }
}
