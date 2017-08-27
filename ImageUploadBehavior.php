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
use yii\helpers\Url;
use yii\web\UploadedFile;

class ImageUploadBehavior extends Behavior
{
  public $size_for_resize = [
            [400,400,true],
            [1000,null,false],
            [300,null,false],
            [100,null,false],
            [50,50,true]
        ];
  public $dir_name;
  public $with_model = true;
  public $attribute = 'img';
  public $random_name = false;
  public $default_photo = false;
  public $form_name = false;
  public $image_path;
  public $image_url;

  public function getSmallImage()
  {
    $last = end($this->size_for_resize);
    $size = $last['0'].'x';
    if($last['1'] != null)
      $size .= $last['1'];
    return $this->getImageBySize($size);
  }

  public function getImage()
  {
    return $this->getImageBySize(false);
  }

  public function getBigImage()
  {
    $size = $this->size_for_resize['1']['0'].'x';
    if($this->size_for_resize['1']['1'] != null)
      $size .= $this->size_for_resize['0']['1'];
    return $this->getImageBySize($size);
  }

  public function getImageBySize($size){
    if(!$size){
      $sizes = $this->size_for_resize;
      $size = $sizes[0][0].'x'.$sizes[0][1];
    }
    $owner_image = $this->owner->{$this->attribute};
    if(!empty($owner_image))
      return $this->imagesFolderUrl.'/'.$size.'/'. $owner_image;
    return str_replace('admin.','',Url::home(true)).'img/nophoto.svg';
  }

  public function getImagesFolderUrl(){
    return (empty($this->image_url))?str_replace('admin.','',Url::home(true)).'upload/'.$this->dir_name:$this->image_url;
  }

  public function getAllImages(){
    $arr = [];
    $owner_image = $this->owner->{$this->attribute};
    foreach ($this->size_for_resize as $size){
      $dir_name = $size['0'].'x';
      if(isset($size['1']))
        $dir_name .= $size['1'];
      if(!empty($owner_image)){
        $arr[$dir_name] = $this->imagesFolderUrl.'/'.$dir_name.'/'. $owner_image;
      }else{
        $arr[$dir_name] = str_replace('admin.','',Url::home(true)).'img/nophoto.svg';
      }
    }
    return $arr;
  }

  public function events()
  {
    return [
        ActiveRecord::EVENT_BEFORE_INSERT => 'upload',
        ActiveRecord::EVENT_BEFORE_UPDATE => 'upload',
        ActiveRecord::EVENT_BEFORE_DELETE => 'deleteImage',
    ];
  }

  public function upload($event)
  {
    $owner = $this->owner;
    if($this->random_name){
      $new_name = false;
    }else{
      $new_name = $owner->id;
    }
    if($this->with_model){
      if($img = UploadedFile::getInstance($owner,'file') and ($img->size > 0)){
        $owner->{$this->attribute} = $this->saveImage(
            $this->dir_name,
            $img,
            $owner->{$this->attribute},
            false,
            $this->size_for_resize,
            $new_name
        );
      }
    }else{
      if($this->form_name){
        $post_file = 'SignupForm[file]';
      }else{
        $post_file = 'file';
      }
      if($img = UploadedFile::getInstanceByName($post_file) and ($img->size > 0)){
        $owner->{$this->attribute} = $this->saveImage(
            $this->dir_name,
            $img,
            $owner->{$this->attribute},
            false,
            $this->size_for_resize,
            $new_name
        );
      }
    }
  }
  public function deleteImage($event){
    $name = $this->owner->{$this->attribute};
    $this->deleteAllImageVariant($name);
  }

  public function deleteAllImageVariant($name){
    $dir = (empty($this->image_path))?Yii::getAlias('@upload') . '/'.$this->dir_name.'/':$this->image_path.'/';
    if(!empty($name)){
      foreach ($this->size_for_resize as $one){
        $sub_dir = $one[0].'x';
        if($one[1] != null){
          $sub_dir .= $one[1];
        }
        if(file_exists($dir.$sub_dir.'/'.$name))
          unlink($dir.$sub_dir.'/'.$name);
      }
      if(file_exists($dir.$name))
        unlink($dir.$name);
    }
    return true;
  }

  private function saveImage($this_dir,$file,$old = false,$url = false,$resize=[],$newname = false){
    if($file != null) {
      $dir = (empty($this->image_path))?Yii::getAlias('@upload') . '/'.$this_dir.'/':$this->image_path.'/';
      $ext = strtolower($file->type);
      $ext = str_replace('image/','',$ext);
      if($newname){
        $name = $newname.'.'.$ext;
      }else{
        $name = md5(strtotime('now').rand(100,999)) . '.'.$ext;
      }
      if(($old != false) and !empty($old) and ($old != $name))
      {
        $this->deleteAllImageVariant($old);
      }
      if (!file_exists($dir))
        \yii\helpers\FileHelper::createDirectory($dir, 0775, true);
      // copying
      move_uploaded_file($file->tempName, $dir.$name);
      foreach($resize as $one_resize)
      {
        $resize_dir = $dir . $one_resize[0] . 'x' . $one_resize[1] . '/';
        if (!file_exists($resize_dir)){
          \yii\helpers\FileHelper::createDirectory($resize_dir, 0775, true);
        }
        $imag = Yii::$app->image->load($dir.$name);
        $imag->background('#fff',0);
        $imag->resize($one_resize[0], $one_resize[1], Yii\image\drivers\Image::INVERSE);
        if($one_resize[2])
          $imag->crop($one_resize[0], $one_resize[1]);
        $imag->save($resize_dir.$name, 85);
      }
      if($url){
        $array = [
            'filelink' => $this->imagesFolderUrl.'/'. $name
        ];
        return stripslashes(json_encode($array));
      }
      return $name;
    }else{
      return false;
    }
  }
}