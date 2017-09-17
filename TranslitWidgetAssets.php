<?php
/**
 * Created by internetsite.com.ua
 * User: Tymofeiev Maksym
 * Date: 13.09.2017
 * Time: 15:51
 */
namespace wokster\behaviors;

use yii\web\AssetBundle;

class TranslitWidgetAssets extends AssetBundle {
  public $sourcePath = '@vendor/wokster/yii2-wokster-behaviors/assets';
  public $js = ['jquery.liTranslit.js'];
}