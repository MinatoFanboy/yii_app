<?php

namespace common\models;

use Yii;
use yii\helpers\Html;
use yii\web\UploadedFile;
use yii\web\HttpException;
use yii\helpers\FileHelper;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $short_description
 * @property string|null $description
 * @property float|null $cost
 * @property float|null $price
 * @property float|null $price_sale
 * @property string|null $exist_day
 * @property int|null $features
 * @property int|null $newest
 * @property int|null $sellest
 * @property int|null $trademark_id
 * @property string|null $trademark_name
 * @property string|null $representation
 * @property string|null $class_type
 * @property int|null $active
 * @property int|null $user_created_id
 * @property string|null $user_created
 * @property int|null $user_updated_id
 * @property string|null $user_updated
 *
 * @property Trademark $trademark
 * @property User $userCreated
 * @property User $userUpdated
 * @property ProductImage[] $productImages
 * @property ProductKeyword[] $productKeywords
 * @property ProductProductType[] $productProductTypes
 */
class Product extends myActiveRecord
{
    public $images;
    public $product_types;
    public $product_keywords;

    public static function tableName()
    {
        return 'product';
    }

    public function rules()
    {
        return [
            [['name'], 'required', 'message' => '{attribute} không được để trống'],
            [['description', 'representation', 'class_type'], 'string'],
            [['cost', 'price', 'price_sale', 'images', 'product_types', 'product_keywords'], 'safe'],
            [['exist_day'], 'safe'],
            [['features', 'newest', 'sellest', 'trademark_id', 'active', 'user_created_id', 'user_updated_id'], 'integer'],
            [['name', 'slug', 'trademark_name', 'user_created', 'user_updated'], 'string', 'max' => 100],
            [['short_description'], 'string', 'max' => 200],
            [['images'], 'validateImages'],
            [['trademark_id'], 'exist', 'skipOnError' => true, 'targetClass' => Trademark::className(), 'targetAttribute' => ['trademark_id' => 'id']],
        ];
    }

    public function validateImages($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $files = UploadedFile::getInstances($this, 'images');
            if ($this->isNewRecord) {
                if (empty($files)) {
                    $this->addError($attribute, "{$this->getAttributeLabel($attribute)} cần ít nhất 1 ảnh minh họa");
                }
            } else {
                if (empty($files) && empty($this->productImages)) {
                    $this->addError($attribute, "{$this->getAttributeLabel($attribute)} cần ít nhất 1 ảnh minh họa");
                }
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên',
            'slug' => 'Slug',
            'short_description' => 'Mô tả ngắn gọn',
            'description' => 'Mô tả chi tiết',
            'cost' => 'Giá nhập',
            'price' => 'Giá bán',
            'price_sale' => 'Giá cạnh tranh',
            'exist_day' => 'Ngày hàng về',
            'features' => 'Nổi bật',
            'newest' => 'Mới về',
            'sellest' => 'Bán chạy',
            'trademark_id' => 'Thương hiệu',
            'trademark_name' => 'Thương hiệu',
            'representation' => 'Ảnh đại diện',
            'class_type' => 'Class Type',
            'active' => 'Active',
            'user_created_id' => 'User Created ID',
            'user_created' => 'User Created',
            'user_updated_id' => 'User Updated ID',
            'user_updated' => 'User Updated',
            'images' => 'Ảnh sản phẩm',
            'product_types' => 'Loại sản phẩm',
            'product_keywords' => 'Từ khóa sản phẩm',
        ];
    }

    public function getTrademark()
    {
        return $this->hasOne(Trademark::className(), ['id' => 'trademark_id']);
    }

    public function getUserCreated()
    {
        return $this->hasOne(User::className(), ['id' => 'user_created_id']);
    }

    public function getUserUpdated()
    {
        return $this->hasOne(User::className(), ['id' => 'user_updated_id']);
    }

    public function getProductImages()
    {
        return $this->hasMany(ProductImage::className(), ['product_id' => 'id']);
    }

    public function getProductKeywords()
    {
        return $this->hasMany(ProductKeyword::className(), ['product_id' => 'id']);
    }

    public function getProductProductTypes()
    {
        return $this->hasMany(ProductProductType::className(), ['product_id' => 'id']);
    }

    public function beforeSave($insert)
    {
        $this->exist_day = myAPI::convertDateSaveIntoDb($this->exist_day);
        if (!$this->trademark_id) {
            $this->trademark_id =  null;
        } else {
            $this->trademark_name = $this->trademark->name;
        }

        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    public function afterSave($insert, $changedAttributes)
    {
        $files = UploadedFile::getInstances($this, 'images');
        if (!empty($files)) {
            foreach ($files as $key => $file) {
                $path = dirname(dirname(__DIR__)) . '/images/product/';
                $link = date('Y/m/d') . '/' . $key . '_' . myAPI::createCode($this->name) . myAPI::get_extension_image($file->type);

                $slider_image = new ProductImage();
                $slider_image->file = $link;
                $slider_image->product_id = $this->id;
                if (!$slider_image->save()) {
                    throw new HttpException(500, Html::errorSummary($slider_image));
                } else {
                    if (FileHelper::createDirectory($path . date('Y/m/d') . '/', $mode = 0775, $recursive = true)) {
                        $file->saveAs($path . $link);
                    }
                    if (!$this->representation) {
                        $this->updateAttributes(['representation' => $link]);
                    }
                }
            }
        }

        ProductProductType::deleteAll(['product_id' => $this->id]);
        if (!empty($this->product_types)) {
            $arr_product_type = [];
            foreach ($this->product_types as $product_type) {
                $product_product_type = new ProductProductType();
                $product_product_type->product_id = $this->id;
                $product_product_type->product_type_id = $product_type;
                if (!$product_product_type->save()) {
                    throw new HttpException(500, Html::errorSummary($product_product_type));
                } else {
                    $arr_product_type[] = $product_product_type->productType->slug;
                }
            }
            $this->updateAttributes(['class_type' => implode(' ', $arr_product_type)]);
        }

        ProductKeyword::deleteAll(['product_id' => $this->id]);
        $arr_keywords = explode(',', $this->product_keywords);
        if (!empty($arr_keywords)) {
            foreach ($arr_keywords as $product_keyword) {
                $keyword = Keyword::findOne(['name' => $product_keyword]);
                if (is_null($keyword)) {
                    $keyword = new Keyword();
                    $keyword->name = $product_keyword;
                    if (!$keyword->save()) {
                        throw new HttpException(500, Html::errorSummary($keyword));
                    }
                }

                $product_keyword = new ProductKeyword();
                $product_keyword->product_id = $this->id;
                $product_keyword->keyword_id = $keyword->id;
                if (!$product_keyword->save()) {
                    throw new HttpException(500, Html::errorSummary($product_keyword));
                }
            }
        }

        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    public function beforeDelete()
    {
        foreach ($this->productImages as $productImage) {
            $productImage->delete();
        }

        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }
}
