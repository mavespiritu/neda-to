<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbltev_drivers".
 *
 * @property string $emp_id
 *
 * @property Tblemployee $emp
 * @property TbltevToVehicle[] $tbltevToVehicles
 */
class Driver extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbltev_drivers';
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
            [['emp_id'], 'required'],
            [['emp_id'], 'string', 'max' => 32],
            [['emp_id'], 'unique'],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'emp_id' => 'Emp ID',
        ];
    }

    /**
     * Gets query for [[Emp]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'emp_id']);
    }

    public function getDriverName()
    {
        return $this->employee ? $this->employee->fname.' '.$this->employee->lname : '';
    }

    /**
     * Gets query for [[TbltevToVehicles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTravelOrderVehicles()
    {
        return $this->hasMany(TravelOrderVehicle::className(), ['driver_id' => 'emp_id']);
    }
}
