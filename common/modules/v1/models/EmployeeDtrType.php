<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tblemp_dtr_type".
 *
 * @property string $emp_id
 * @property string $dtr_id
 * @property string $date
 * @property string|null $total_with_out_pass_slip
 * @property string|null $total_with_pass_slip
 * @property string|null $total_tardy
 * @property string|null $total_UT
 * @property string|null $total_pass_slip
 * @property string|null $am_in
 * @property string|null $am_out
 * @property string|null $pm_in
 * @property string|null $pm_out
 * @property string|null $total_OT
 * @property string|null $multiplied_total_OT
 *
 * @property Tblemployee $emp
 * @property TbldtrType $dtr
 */
class EmployeeDtrType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tblemp_dtr_type';
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
            [['emp_id', 'dtr_id', 'date'], 'required'],
            [['date'], 'safe'],
            [['emp_id', 'dtr_id', 'total_with_out_pass_slip', 'total_with_pass_slip', 'total_tardy', 'total_UT', 'total_pass_slip', 'am_in', 'am_out', 'pm_in', 'pm_out', 'total_OT', 'multiplied_total_OT'], 'string', 'max' => 20],
            [['emp_id', 'dtr_id', 'date'], 'unique', 'targetAttribute' => ['emp_id', 'dtr_id', 'date']],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tblemployee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
            [['dtr_id'], 'exist', 'skipOnError' => true, 'targetClass' => TbldtrType::className(), 'targetAttribute' => ['dtr_id' => 'dtr_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'emp_id' => 'Emp ID',
            'dtr_id' => 'Dtr ID',
            'date' => 'Date',
            'total_with_out_pass_slip' => 'Total With Out Pass Slip',
            'total_with_pass_slip' => 'Total With Pass Slip',
            'total_tardy' => 'Total Tardy',
            'total_UT' => 'Total  Ut',
            'total_pass_slip' => 'Total Pass Slip',
            'am_in' => 'Am In',
            'am_out' => 'Am Out',
            'pm_in' => 'Pm In',
            'pm_out' => 'Pm Out',
            'total_OT' => 'Total  Ot',
            'multiplied_total_OT' => 'Multiplied Total  Ot',
        ];
    }

    /**
     * Gets query for [[Emp]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmp()
    {
        return $this->hasOne(Tblemployee::className(), ['emp_id' => 'emp_id']);
    }

    /**
     * Gets query for [[Dtr]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDtr()
    {
        return $this->hasOne(TbldtrType::className(), ['dtr_id' => 'dtr_id']);
    }
}
