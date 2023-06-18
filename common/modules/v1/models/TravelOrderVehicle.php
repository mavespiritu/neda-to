<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbltev_to_vehicle".
 *
 * @property int $id
 * @property string|null $TO_NO
 * @property string|null $vehicle_id
 * @property string|null $driver_id
 * @property string|null $isapproved
 * @property string|null $approvedby
 * @property string|null $date_approved
 * @property string|null $remarks
 *
 * @property Tblvehicle $vehicle
 * @property TbltevTravelorder $tONO
 * @property TbltevDrivers $driver
 */
class TravelOrderVehicle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbltev_to_vehicle';
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
            [['vehicle_id', 'driver_id'], 'required'],
            [['remarks'], 'string'],
            [['TO_NO', 'vehicle_id', 'driver_id', 'date_approved'], 'string', 'max' => 20],
            [['isapproved'], 'string', 'max' => 5],
            [['approvedby'], 'string', 'max' => 30],
            [['vehicle_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vehicle::className(), 'targetAttribute' => ['vehicle_id' => 'vehicle_code']],
            [['TO_NO'], 'exist', 'skipOnError' => true, 'targetClass' => TravelOrder::className(), 'targetAttribute' => ['TO_NO' => 'TO_NO']],
            [['driver_id'], 'exist', 'skipOnError' => true, 'targetClass' => Driver::className(), 'targetAttribute' => ['driver_id' => 'emp_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'TO_NO' => 'To  No',
            'vehicle_id' => 'Vehicle',
            'driver_id' => 'Driver',
            'isapproved' => 'Approval Status',
            'approvedby' => 'Approved By',
            'date_approved' => 'Date Approved',
            'remarks' => 'Remarks',
        ];
    }

    /**
     * Gets query for [[Vehicle]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVehicle()
    {
        return $this->hasOne(Vehicle::className(), ['vehicle_code' => 'vehicle_id']);
    }

    /**
     * Gets query for [[TONO]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTravelOrder()
    {
        return $this->hasOne(TravelOrder::className(), ['TO_NO' => 'TO_NO']);
    }

    /**
     * Gets query for [[Driver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDriver()
    {
        return $this->hasOne(Driver::className(), ['emp_id' => 'driver_id']);
    }

    /**
     * Gets query for [[Driver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApprover()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'approvedby']);
    }
}
