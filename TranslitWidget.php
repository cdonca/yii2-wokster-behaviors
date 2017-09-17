<?php
/**
 * Created by internetsite.com.ua
 * User: Tymofeiev Maksym
 * Date: 13.09.2017
 * Time: 15:50
 */

namespace wokster\behaviors;

use yii\bootstrap\Html;
use yii\base\Model;
use yii\widgets\InputWidget;

class TranslitWidget extends InputWidget
{

  public $type = 'text';
  public $donor_attribute = 'title';
  public $donor_selector = 'title';
  public $on_update = false;
  public $options = ['class' => 'form-control'];

  public function run(){
    if($this->hasModel() and ($this->model->isNewRecord or $this->on_update))
      $this->registerJs();

    if ($this->hasModel()) {
      echo Html::activeInput($this->type, $this->model, $this->attribute, $this->options);
    } else {
      echo Html::input($this->type, $this->name, $this->value, $this->options);
    }
  }
  
  public function registerJs(){
    $view = $this->view;
    TranslitWidgetAssets::register($view);
    $this_selector = '#'.Html::getInputId($this->model, $this->attribute);
    if($this->hasModel()){
      $donor_selector = '#'.Html::getInputId($this->model, $this->donor_attribute);
    }else{
      $donor_selector = $this->donor_selector;
    }
    $view->registerJs("
      $('".$donor_selector."').liTranslit({
      elAlias: $('".$this_selector."'),
      caseType:	'lower',
      });
    ");
  }

  /**
   * @return bool whether this widget is associated with a data model.
   */
  protected function hasModel()
  {
    return $this->model instanceof Model && $this->attribute !== null;
  }
}