<?php
/**
 * Created by internetsite.com.ua
 * User: Tymofeiev Maksym
 * Date: 23.08.2017
 * Time: 18:46
 */

namespace wokster\behaviors;


use yii\base\Behavior;
use yii\bootstrap\Html;

class StatusBehavior extends Behavior
{
  public $status_value = 0;
  public $statusList = ['1' => 'актив', '0' => 'пассив'];
  public $statusColors = ['1' => 'success', '0' => 'default'];

  public function getStatus()
  {
    $owner = $this->owner;
    return $this->statusList[$owner->status_id];
  }

  public function getStatusList()
  {
    return $this->statusList;
  }

  public function getStatusColor()
  {
    $owner = $this->owner;
    return $this->statusColors[$owner->status_id];
  }

  public function getStatusColorList()
  {
    return $this->statusColors;
  }

  public function getStatusBadge()
  {
    return Html::tag('span',$this->status,['class'=>'label label-'.$this->statusColor]);
  }
}