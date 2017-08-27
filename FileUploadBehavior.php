<?php
/**
 * Created by internetsite.com.ua
 * User: Tymofeiev Maksym
 * Date: 10.08.2016
 * Time: 20:12
 */

namespace wokster\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

class FileUploadBehavior extends Behavior
{
  public $dir_name;
  public $user;

  public function events()
  {
    return [
        ActiveRecord::EVENT_BEFORE_INSERT => 'upload',
        ActiveRecord::EVENT_BEFORE_UPDATE => 'upload',
    ];
  }

  public function upload($event)
  {
    $owner = $this->owner;
    
    if(($this->user == null) or empty($this->user)){
      $this->user = Yii::$app->user->id;
    }
    
    $dir = $this->dir_name.'/'.$this->user;

    if($file = UploadedFile::getInstance($owner,'file') and ($file->size > 0)){
      $owner->file_name = self::saveFile(
          $dir,
          $file,
          $owner->file_name
      );
    }
  }

  private static function saveFile($this_dir,$file,$old = false){
    if($this_dir != null and $file != null) {
      $dir = Yii::getAlias('@common') . '/'.$this_dir.'/';

      if(($old != false) and !empty($old) and file_exists($dir.$old))
      {
        unlink($dir.$old);
      }
      if (!file_exists($dir))
        \yii\helpers\FileHelper::createDirectory($dir, 0775, true);
      // copying
      $name = strtotime('now').'_'.$file->name;
      move_uploaded_file($file->tempName, $dir.$name);
      return $name;
    }else{
      return false;
    }
  }
}