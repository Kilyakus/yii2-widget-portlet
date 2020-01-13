<?php
namespace kilyakus\portlet;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use kilyakus\nav\Nav;
use kilyakus\widget\scrollbar\Scrollbar;

class Portlet extends \kilyakus\widgets\Widget
{
    public $pluginName = 'portlet';
    public $pluginSupport = false;

    const TYPE_NONE = '';
    const TYPE_DEFAULT = 'default';

    const SIZE_SMALL = 'sm';
    const SIZE_LARGE = 'lg';
    const SIZE_EXTRA_LARGE = 'xl';

    const TOOL_MINIMIZE = 'collapse';
    const TOOL_MODAL = 'modal';
    const TOOL_RELOAD = 'reload';
    const TOOL_CLOSE = 'remove';

    const HEAD_FIT_NONE = '';
    const HEAD_FIT = 'fit';

    const BODY_FIT_NONE = '';
    const BODY_FIT = 'fit';
    const BODY_FIT_TOP = 'fit-top';
    const BODY_FIT_BOTTOM = 'fit-bottom';
    const BODY_FIT_X = 'fit-x';
    const BODY_HOR_FIT = 'hor-fit';
    const BODY_FIT_Y = 'fit-y';

    const FOOT_FIT_NONE = '';
    const FOOT_FIT = 'fit';

    public $title;
    public $subtitle;
    public $icon;

    /**
     * @var string The portlet type
     * Valid values are 'box', 'solid', ''
     */
    public $type = self::TYPE_NONE;

    /**
     * @var string The portlet color
     * Valid values are 'light-blue', 'blue', 'red', 'yellow', 'green', 'purple', 'light-grey', 'grey'
     */
    public $color = '';

    /**
     * @var string The portlet background color
     */
    public $background = '';

    /**
     * @var array List of actions, where each element must be specified as a string.
     */
    public $actions = [];

    /**
     * @var array The portlet tools
     * Valid values are 'collapse', 'modal', 'reload', 'remove'
     */
    public $tools = [];

    /**
      Scroller options
      Тут потом переделаю на виджет, у него будут свои надстройки
      ```php
      [
        // required, height of the body portlet as a px
        'height' => 150,
        // optional, HTML attributes of the scroller
        'options' => [],
        // optional, footer of the scroller. May contain string or array(the options of Link component)
        'footer' => [
          'label' => 'Show all',
        ],
      ]
      ```
     */
    public $scrollbar = [];

    public $bordered = true;

    public $unelevate = false;

    // portlet container
    public $options = [];

    // body container
    public $bodyOptions = [];
    public $bodyCover = self::BODY_FIT_NONE;

    // header
    public $headerOptions = [];
    public $headerContent;

    // footer ( content использовать для вставки штмл чистоганом если влом разбираться с []footer )
    public $footerOptions = [];
    public $footerContent;
    public $footer = [];

    public function init()
    {
        parent::init();

        if($this->pluginSupport === true){

          $this->_supportInfo();
          
        } else {
        
          $this->background = $this->background ? 'kt-bg-'.$this->background : '';

          Html::addCssClass($this->options, trim(sprintf('kt-portlet kt-portlet--mobile %s %s', $this->type, $this->background)));

          if($this->bordered){
            Html::addCssClass($this->options, 'kt-portlet--bordered');
          }

          if($this->unelevate){
            Html::addCssClass($this->options, 'kt-portlet--unelevate');
          }

          echo '<!-- begin:: Widgets/Portlet -->';
          echo Html::beginTag('div', $this->options);

          $this->_renderTitle();

          if(!empty($this->scrollbar)){
            if(!$this->scrollbar['id']){
              $this->scrollbar['id'] = $this->id;
            }
            Scrollbar::begin($this->scrollbar);
          }

          Html::addCssClass($this->bodyOptions, 'kt-portlet__body');

          if($this->bodyCover !== self::BODY_FIT_NONE){
            Html::addCssClass($this->bodyOptions, trim(sprintf('kt-portlet__body--%s', $this->bodyCover)));
          }

          echo Html::beginTag('div', $this->bodyOptions);
      }
    }

    public function run()
    {
        if(!empty($this->scrollbar)){
            Scrollbar::end();
        }

        echo Html::endTag('div'); // End portlet body
        $this->_renderFooter();
        echo Html::endTag('div'); // End portlet div
        echo '<!-- end:: Widgets/Portlet -->';
        //$loader = Html::img( ---  ::getAssetsUrl($this->view) . '/img/loading-spinner-grey.gif'); Тут пока шляпа
        //$this->clientOptions['loader'] = ArrayHelper::getValue($this->clientOptions, 'loader', $loader);
        PortletAsset::register($this->view);
        //$this->registerPlugin('portlet'); Тут тоже, надо переосмыслить текущее
    }

    private function _renderTitle()
    {
        if($this->headerContent){
            Html::addCssClass($this->headerOptions, 'kt-portlet__head');

            echo Html::beginTag('div', $this->headerOptions);
            echo $this->headerContent;
            echo Html::endTag('div');

        }elseif ($this->title)
        {
            Html::addCssClass($this->headerOptions, 'kt-portlet__head');

            echo Html::beginTag('div', $this->headerOptions);

            echo Html::beginTag('div', ['class' => 'kt-portlet__head-label']);

            if ($this->icon)
            {
                echo Html::beginTag('span', ['class' => 'kt-portlet__head-icon']);
                    echo Html::tag('i', '', ['class' => 'kt-font-brand ' . $this->pushFontColor($this->icon)]);
                echo Html::endTag('span');
            }

            echo Html::tag('h3', $this->title, ['class' => $this->pushFontColor('kt-portlet__head-title')]);

            if ($this->subtitle)
            {
                echo Html::tag('small', $this->subtitle);
            }

            echo Html::endTag('div');

            $this->_renderTools();

            $this->_renderActions();

            echo Html::endTag('div');
        }
    }

    private function _renderTools()
    {
        if (!empty($this->tools))
        {
            $tools = [];

            foreach ($this->tools as $tool)
            {
                $class = '';
                switch ($tool)
                {
                    case self::TOOL_CLOSE :
                        $class = 'remove';
                        break;

                    case self::TOOL_MINIMIZE :
                        $class = 'collapse';
                        break;

                    case self::TOOL_RELOAD :
                        $class = 'reload';
                        break;
                }
                $tools[] = Html::tag('a', ($tool['label'] ? $tool['label'] : ''), ['class' => 'btn btn-clean btn-icon-sm '.$class, 'href' => '']);
            }

            echo Html::tag('div', implode("\n", $tools), ['class' => 'kt-portlet__head-toolbar']);
        }
    }

    private function _renderActions()
    {
        if (!empty($this->actions))
        {
            echo Html::tag('div', Html::tag('div', implode("\n", $this->actions), ['class' => 'kt-portlet__head-actions']), ['class' => 'kt-portlet__head-toolbar']);
        }
    }

    private function _renderFooter()
    {
        if($this->footerContent || $this->footer){

            Html::addCssClass($this->footerOptions, 'kt-portlet__foot align-items-center');

            echo Html::beginTag('div', $this->footerOptions);

            if($this->footerContent){

                echo $this->footerContent;

            }elseif ($this->footer)
            {
                echo Nav::widget([
                    'encodeLabels' => false,
                    'items' => $this->footer,
                ]);
            }

            echo Html::endTag('div');
        }
    }

    protected function getFontColor()
    {
        if ($this->color)
        {
            return sprintf('font-%s', $this->color);
        }

        return '';
    }

    protected function pushFontColor($string)
    {
        $color = $this->getFontColor();

        if ($color)
        {
            return sprintf('%s %s', $string, $color);
        }

        return $string;
    }

    private function _supportInfo()
    {
        echo "<pre>
  Модуль еще в доработке, использовать на свой страх и риск 
  
  Examples:

  Portlet renders a engine portlet.
  Any content enclosed between the [[begin()]] and [[end()]] calls of Portlet
  is treated as the content of the portlet.

  Portlet::begin([
    'icon' => 'fa fa-check',
    'title' => 'Title Portlet',
    'pluginSupport' => false,
  ]);
  echo 'Body portlet';
  Portlet::end();

  Portlet with tools, actions, scroller, events and remote content

  Portlet::begin([
    'title' => 'Extended Portlet',
    'scroller' => [
      'height' => 150,
      'footer' => ['label' => 'Show all', 'url' => '#'],
    ],
    'clientOptions' => [
      'loadSuccess' => new \yii\web\JsExpression('function(){ console.log(\"load success\"); }'),
      'remote' => '/?r=site/about',
    ],
    'clientEvents' => [
      'close.mr.portlet' => 'function(e) { console.log(\"portlet closed\"); e.preventDefault(); }'
    ],
    'tools' => [
      Portlet::TOOL_RELOAD,
      Portlet::TOOL_MINIMIZE,
      Portlet::TOOL_CLOSE,
    ],
    'pluginSupport' => false,
  ]);
  echo 'Body portlet';
  Portlet::end();
          </pre>";
    }
}
