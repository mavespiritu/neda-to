<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tblvehicle".
 *
 * @property string $vehicle_code
 * @property string|null $vehicle_description
 * @property int|null $ordering
 * @property string|null $activity
 *
 * @property TbltevToVehicle[] $tbltevToVehicles
 * @property TblvehicleTrip[] $tblvehicleTrips
 */
class Vehicle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tblvehicle';
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
            [['vehicle_code'], 'required'],
            [['ordering'], 'integer'],
            [['vehicle_code'], 'string', 'max' => 50],
            [['vehicle_description'], 'string', 'max' => 255],
            [['activity'], 'string', 'max' => 10],
            [['vehicle_code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'vehicle_code' => 'Vehicle Code',
            'vehicle_description' => 'Vehicle Description',
            'ordering' => 'Ordering',
            'activity' => 'Activity',
        ];
    }

    /**
     * Gets query for [[TbltevToVehicles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTravelOrderVehicles()
    {
        return $this->hasMany(TravelOrderVehicle::className(), ['vehicle_id' => 'vehicle_code']);
    }
}
