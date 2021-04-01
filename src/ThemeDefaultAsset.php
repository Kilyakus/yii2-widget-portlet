<?php
namespace kilyakus\portlet;

class ThemeDefaultAsset extends \yii\web\AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__ . '/assets';

        $this->css[] = 'css/widget-portletdefault.min.css';

        parent::init();
    }
}