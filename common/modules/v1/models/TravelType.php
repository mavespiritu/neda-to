<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbltev_typeoftravel".
 *
 * @property int $id
 * @property string|null $description
 *
 * @property TbltevTravelorder[] $tbltevTravelorders
 */
class TravelType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbltev_typeoftravel';
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
            [['id'], 'integer'],
            [['description'], 'string'],
            [['id'], 'unique'],
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
        ];
    }

    /**
     * Gets query for [[TbltevTravelorders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTravelOrders()
    {
        return $this->hasMany(TravelOrder::className(), ['type_of_travel' => 'id']);
    }
}
