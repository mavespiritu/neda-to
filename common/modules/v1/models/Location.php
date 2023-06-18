<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbltev_specificlocation".
 *
 * @property int $id
 * @property string|null $description
 * @property int|null $municipality
 *
 * @property TbltevMunicipality $municipality0
 * @property TbltevToLocation[] $tbltevToLocations
 */
class Location extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbltev_specificlocation';
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
            [['id', 'municipality'], 'integer'],
            [['description'], 'string'],
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
            'municipality' => 'Municipality',
        ];
    }

    /**
     * Gets query for [[Municipality0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCitymun()
    {
        return $this->hasOne(Citymun::className(), ['id' => 'municipality']);
    }

    /**
     * Gets query for [[TbltevToLocations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTravelOrderLocations()
    {
        return $this->hasMany(TravelOrderLocations::className(), ['specificLocation' => 'id']);
    }
}
