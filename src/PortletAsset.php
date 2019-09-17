<?php
namespace kilyakus\portlet;

class ButtonAsset extends \kilyakus\widgets\AssetBundle
{
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['css/portlet'],'widget-portlet');
        parent::init();
    }
}
