<?php
namespace kilyakus\portlet;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use kilyakus\nav\Nav;

/*
    Модуль еще в доработке, использовать на свой страх и риск 
    
    Examples:

    Portlet renders a engine portlet.
    Any content enclosed between the [[begin()]] and [[end()]] calls of Portlet
    is treated as the content of the portlet.

    Portlet::begin([
      'icon' => 'fa fa-check',
      'title' => 'Title Portlet',
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
        'loadSuccess' => new \yii\web\JsExpression('function(){ console.log("load success"); }'),
        'remote' => '/?r=site/about',
      ],
      'clientEvents' => [
        'close.mr.portlet' => 'function(e) { console.log("portlet closed"); e.preventDefault(); }'
      ],
      'tools' => [
        Portlet::TOOL_RELOAD,
        Portlet::TOOL_MINIMIZE,
        Portlet::TOOL_CLOSE,
      ],
    ]);
*/

class Portlet extends \kilyakus\widgets\Widget
{
    public $pluginName = 'portlet';

    const TYPE_NONE = '';
    const TYPE_DEFAULT = 'default';

    const SIZE_SMALL = 'sm';
    const SIZE_LARGE = 'lg';
    const SIZE_EXTRA_LARGE = 'xl';

    const TOOL_MINIMIZE = 'collapse';
    const TOOL_MODAL = 'modal';
    const TOOL_RELOAD = 'reload';
    const TOOL_CLOSE = 'remove';

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
     * @var array Scroller options
     * is an array of the following structure:
     * ```php
     * [
     *   // required, height of the body portlet as a px
     *   'height' => 150,
     *   // optional, HTML attributes of the scroller
     *   'options' => [],
     *   // optional, footer of the scroller. May contain string or array(the options of Link component)
     *   'footer' => [
     *     'label' => 'Show all',
     *   ],
     * ]
     * ```
     */
    public $scroller = [];

    /**
     * @var bool Whether the portlet should be bordered
     */
    public $bordered = false;

    /**
     * @var array The HTML attributes for the widget container
     */
    public $options = [];

    /**
     * @var array The HTML attributes for the widget body container
     */
    public $bodyOptions = [];

    /**
     * @var array The HTML attributes for the widget body container
     */
    public $headerOptions = [];

    public $headerContent;

    /**
     * @var array The HTML attributes for the widget body container
     */
    public $footerOptions = [];

    public $footerContent;

    public $footer = [];

    /**
     * Initializes the widget.
     */
    public function init()
    {
        parent::init();
        
        $this->background = $this->background ? 'kt-bg-'.$this->background : '';

        Html::addCssClass($this->options, trim(sprintf('kt-portlet kt-portlet--mobile %s %s', $this->type, $this->background)));
        echo '<!-- begin:: Widgets/Portlet -->';
        echo Html::beginTag('div', $this->options);

        $this->_renderTitle();

        $this->_renderScrollerBegin();

        Html::addCssClass($this->bodyOptions, 'kt-portlet__body');
        echo Html::beginTag('div', $this->bodyOptions);

    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        $this->_renderScrollerEnd();

        echo Html::endTag('div'); // End portlet body
        $this->_renderFooter();
        echo Html::endTag('div'); // End portlet div
        echo '<!-- end:: Widgets/Portlet -->';
        //$loader = Html::img( ???  ::getAssetsUrl($this->view) . '/img/loading-spinner-grey.gif');
        //$this->clientOptions['loader'] = ArrayHelper::getValue($this->clientOptions, 'loader', $loader);
        PortletAsset::register($this->view);
        //$this->registerPlugin('portlet');
    }

    /**
     * Renders portlet title
     */
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

    /**
     * Renders portlet tools
     */
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

    /**
     * Renders portlet actions
     */
    private function _renderActions()
    {
        if (!empty($this->actions))
        {
            echo Html::tag('div', implode("\n", $this->actions), ['class' => 'actions']);
        }
    }

    /**
     * Renders scroller begin
     * @throws InvalidConfigException
     */
    private function _renderScrollerBegin()
    {
        if (!empty($this->scroller))
        {
            if (!isset($this->scroller['height']))
            {
                Yii::$app->session->setFlash('error', 'Widgets/' . (new \ReflectionClass(get_class($this)))->getShortName() . ': ' . Yii::t('easyii', 'The "height" option of the scroller is required.'));
            }
            $options = ArrayHelper::getValue($this->scroller, 'options', []);

            $checkFormat = ($this->scroller['format'] ? $this->scroller['format'] : ($this->scroller['format'] = 'px')) == 'px';

            echo Html::beginTag(
                    'div', ArrayHelper::merge(
                            (
                                $checkFormat ? [
                                    'data-scroll' => 'true', 'data-height' => $this->scroller['height'], 'data-mobile-height' => $this->scroller['height']
                                ] : [
                                    'data-scroll' => 'true',
                                ]
                            ), $options, [
                                'style' => 'height:' . $this->scroller['height'] . $this->scroller['format'] . ';max-height:' . $this->scroller['max-height'] . $this->scroller['format'] . ';'
                            ]
                    )
            );
        }
    }

    /**
     * Renders scroller end
     */
    private function _renderScrollerEnd()
    {
        if (!empty($this->scroller))
        {
            echo Html::endTag('div');
            $footer = ArrayHelper::getValue($this->scroller, 'footer', '');
            if (!empty($footer))
            {
                echo Html::beginTag('div', ['class' => 'scroller-footer']);
                if (is_array($footer))
                {
                    echo Html::tag('div', Link::widget($footer), ['class' => 'pull-right']);
                }
                elseif (is_string($footer))
                {
                    echo $footer;
                }
                echo Html::endTag('div');
            }
        }
    }

    private function _renderFooter()
    {
        if($this->footerContent || $this->footer){

            echo Html::beginTag('div', ['class' => 'kt-portlet__foot']);

            if($this->footerContent){

                echo $this->footerContent;

            }elseif ($this->footer)
            {
                

                Html::addCssClass($this->footerOptions, 'align-items-center');

                echo Html::beginTag('div', $this->footerOptions);

                echo Nav::widget([
                    'encodeLabels' => false,
                    'items' => $this->footer,
                ]);

                echo Html::endTag('div');
            }

            echo Html::endTag('div');
        }
    }

    /**
     * Retrieves font color
     */
    protected function getFontColor()
    {
        if ($this->color)
        {
            return sprintf('font-%s', $this->color);
        }

        return '';
    }

    /**
     * Pushes font color to given string
     */
    protected function pushFontColor($string)
    {
        $color = $this->getFontColor();

        if ($color)
        {
            return sprintf('%s %s', $string, $color);
        }

        return $string;
    }
}
