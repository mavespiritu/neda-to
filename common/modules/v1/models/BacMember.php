<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_bac_member".
 *
 * @property string $emp_id
 * @property string|null $office_id
 * @property string|null $bac_group
 * @property string|null $expertise
 */
class BacMember extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_bac_member';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['emp_id'], 'required'],
            [['bac_group'], 'string'],
            [['emp_id', 'expertise', 'sub_expertise'], 'string', 'max' => 100],
            [['office_id'], 'string', 'max' => 10],
            [['emp_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'emp_id' => 'Emp ID',
            'office_id' => 'Office ID',
            'bac_group' => 'Bac Group',
            'expertise' => 'Expertise',
        ];
    }
}
