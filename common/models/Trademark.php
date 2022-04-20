<?php

namespace common\models;

use Yii;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $file
 * @property int|null $active
 */
class Trademark extends \yii\db\ActiveRecord
{
    public $image;

    public static function tableName()
    {
        return 'trademark';
    }

    public function rules()
    {
        return [
            [['file'], 'string'],
            [['name', 'slug'], 'string', 'max' => 50],
            [['active'], 'integer'],
            [['image'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên',
            'slug' => 'Slug',
            'file' => 'Ảnh đại diện',
            'image' => 'Ảnh đại diện',
            'active' => 'Active',
        ];
    }

    public function beforeSave($insert)
    {
        $this->slug = myAPI::createCode($this->name);
        
        $file = UploadedFile::getInstance($this, 'image');
        if (is_null($file)) {
            if ($insert) {
                $this->file = 'no-image.jpeg';
            }
        } else {
            $this->file = date('Y/m/d') . '/' . $this->slug . myAPI::get_extension_image($file->type);
            if(!$insert){
                $old_trademark = self::findOne($this->id);
                if($old_trademark->file !== 'no-image.jpg'){
                    Yii::$app->session->set('old_image', $old_trademark->file);
                }
            }
        }
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    public function afterSave($insert, $changedAttributes)
    {
        $file = UploadedFile::getInstance($this, 'image');
        if (!is_null($file)) {
            $path = dirname(dirname(__DIR__)).'/images/trademark/';
            if (FileHelper::createDirectory($path . date('Y/m/d') . '/', $mode = 0775, $recursive = true)) {
                $file->saveAs($path . $this->file);
            }
            if(!$insert){
                if(isset(Yii::$app->session['old_image'])) {
                    $old_thuong_hieu = Yii::$app->session->get('old_image');
                    $path = dirname(dirname(__DIR__)).'/images/trademark/'.$old_thuong_hieu;
                    if(is_file($path)){
                        unlink($path);
                    }
                    unset(Yii::$app->session['old_image']);
                }
            }
        }
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    public function beforeDelete()
    {
        $path = dirname(dirname(__DIR__)).'/images/trademark/'.$this->file;
        if(is_file($path)){
            unlink($path);
        }

        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }
}
