<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbltev_concernedstaff".
 *
 * @property int $id
 * @property string|null $TO_NO
 * @property string|null $emp_id
 * @property string|null $date_modified
 * @property string|null $dateApproved
 * @property string|null $dateDisApproved
 * @property string|null $remarks
 *
 * @property TbltevTravelorder $tONO
 * @property Tblemployee $emp
 */
class ConcernStaff extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbltev_concernedstaff';
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
            [['TO_NO', 'emp_id', 'date_modified'], 'string', 'max' => 20],
            [['dateApproved', 'dateDisApproved'], 'string', 'max' => 32],
            [['TO_NO'], 'exist', 'skipOnError' => true, 'targetClass' => TravelOrder::className(), 'targetAttribute' => ['TO_NO' => 'TO_NO']],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
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
            'emp_id' => 'Emp ID',
            'date_modified' => 'Date Modified',
            'dateApproved' => 'Date Approved',
            'dateDisApproved' => 'Date Dis Approved',
            'remarks' => 'Remarks',
        ];
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
     * Gets query for [[Emp]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'emp_id']);
    }

    public function getName()
    {
        return $this->employee ? $this->employee->mname != '' ? $this->employee->fname.' '.substr($this->employee->mname, 0, 1).'. '.$this->employee->lname : $this->employee->fname.' '.$this->employee->lname : '';
    }
}
