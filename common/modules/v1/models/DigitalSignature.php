<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbltev_digitalsignature".
 *
 * @property int $autoID
 * @property string|null $emp_id
 * @property string|null $to_no
 * @property string|null $date_approved
 * @property string|null $date_disapproved
 * @property string|null $remarks
 * @property string|null $lvlOfSignature
 * @property string|null $designation
 *
 * @property TbltevTravelorder $toNo
 * @property Tblemployee $emp
 */
class DigitalSignature extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbltev_digitalsignature';
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
            [['remarks'], 'string'],
            [['emp_id', 'to_no', 'date_approved', 'date_disapproved', 'lvlOfSignature'], 'string', 'max' => 32],
            [['designation'], 'string', 'max' => 64],
            [['to_no'], 'exist', 'skipOnError' => true, 'targetClass' => TravelOrder::className(), 'targetAttribute' => ['to_no' => 'TO_NO']],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'autoID' => 'Auto ID',
            'emp_id' => 'Emp ID',
            'to_no' => 'To No',
            'date_approved' => 'Date Approved',
            'date_disapproved' => 'Date Disapproved',
            'remarks' => 'Remarks',
            'lvlOfSignature' => 'Lvl Of Signature',
            'designation' => 'Designation',
        ];
    }

    /**
     * Gets query for [[ToNo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTravelOrder()
    {
        return $this->hasOne(TravelOrder::className(), ['TO_NO' => 'to_no']);
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
}
