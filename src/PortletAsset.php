<?php
namespace kilyakus\portlet;

class PortletAsset extends \kilyakus\widgets\AssetBundle
{
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['css/widget-portlet'],'widget-portlet');
        parent::init();
    }
}
