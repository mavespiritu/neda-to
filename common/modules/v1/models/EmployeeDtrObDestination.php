<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tblemp_dtr_ob_destination".
 *
 * @property string|null $emp_id
 * @property string|null $date
 * @property string|null $dtr_id
 * @property string|null $destination
 * @property string|null $project_code
 */
class EmployeeDtrObDestination extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tblemp_dtr_ob_destination';
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
            [['date'], 'safe'],
            [['emp_id', 'dtr_id'], 'string', 'max' => 20],
            [['destination'], 'string', 'max' => 765],
            [['project_code'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'emp_id' => 'Emp ID',
            'date' => 'Date',
            'dtr_id' => 'Dtr ID',
            'destination' => 'Destination',
            'project_code' => 'Project Code',
        ];
    }
}
