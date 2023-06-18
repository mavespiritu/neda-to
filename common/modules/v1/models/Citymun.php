<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbltev_municipality".
 *
 * @property int $id
 * @property string|null $description
 * @property int|null $province_ID
 *
 * @property TbltevProvince $province
 * @property TbltevSpecificlocation[] $tbltevSpecificlocations
 */
class Citymun extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbltev_municipality';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db2');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'province_ID'], 'integer'],
            [['description'], 'string'],
            [['id'], 'unique'],
            [['province_ID'], 'exist', 'skipOnError' => true, 'targetClass' => Province::className(), 'targetAttribute' => ['province_ID' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Description',
            'province_ID' => 'Province  ID',
        ];
    }

    /**
     * Gets query for [[Province]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProvince()
    {
        return $this->hasOne(Province::className(), ['id' => 'province_ID']);
    }

    /**
     * Gets query for [[TbltevSpecificlocations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLocation()
    {
        return $this->hasMany(Location::className(), ['municipality' => 'id']);
    }
}
