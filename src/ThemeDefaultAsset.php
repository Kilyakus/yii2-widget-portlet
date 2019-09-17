<?php
namespace kilyakus\portlet;

class ThemeDefaultAsset extends \kilyakus\widgets\AssetBundle
{
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['css/portlet-default'],'widget-portlet-theme-default');
        parent::init();
    }
}
