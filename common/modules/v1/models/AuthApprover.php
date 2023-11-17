<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbltev_authapprover".
 *
 * @property string $emp_id
 * @property string|null $recommending
 * @property string|null $final
 *
 * @property Tblemployee $emp
 * @property Tblemployee $recommending0
 * @property Tblemployee $final0
 */
class AuthApprover extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbltev_authapprover';
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
            [['final'], 'required'],
            [['emp_id', 'recommending', 'final'], 'string', 'max' => 32],
            [['emp_id'], 'unique'],
            [['emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['emp_id' => 'emp_id']],
            [['recommending'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['recommending' => 'emp_id']],
            [['final'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['final' => 'emp_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'emp_id' => 'Emp ID',
            'recommending' => 'Recommending Approval',
            'final' => 'Final Approval',
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

    /**
     * Gets query for [[Recommending0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecommendingApprover()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'recommending']);
    }

    /**
     * Gets query for [[Final0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFinalApprover()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'final']);
    }
}
