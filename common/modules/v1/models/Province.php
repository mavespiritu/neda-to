<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbltev_province".
 *
 * @property int $id
 * @property string|null $description
 * @property int|null $region
 *
 * @property TbltevMunicipality[] $tbltevMunicipalities
 * @property TbltevRegion $region0
 */
class Province extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbltev_province';
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
            [['id', 'region'], 'integer'],
            [['description'], 'string'],
            [['id'], 'unique'],
            [['region'], 'exist', 'skipOnError' => true, 'targetClass' => Region::className(), 'targetAttribute' => ['region' => 'id']],
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
            'region' => 'Region',
        ];
    }

    /**
     * Gets query for [[TbltevMunicipalities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCitymun()
    {
        return $this->hasMany(Citymun::className(), ['province_ID' => 'id']);
    }

    /**
     * Gets query for [[Region0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRegionTitle()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }
}
