<?php
namespace kilyakus\portlet;

// class PortletAsset extends \yii\web\AssetBundle
// {
//     public function init()
//     {
//         $this->sourcePath = __DIR__ . '/assets';

//         $this->css[] = 'css/widget-portlet.min.css';
        
//         $this->js[] = 'js/widget-portlet.min.js';

//         parent::init();
//     }
// }

use kilyakus\widgets\AssetBundle;

class PortletAsset extends AssetBundle
{
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['css/widget-portlet.min']);
        $this->setupAssets('js', ['js/widget-portlet.min']);
        parent::init();
    }
}
