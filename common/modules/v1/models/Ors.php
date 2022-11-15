<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_ors".
 *
 * @property int $id
 * @property int|null $pr_id
 * @property int|null $po_id
 * @property string|null $ors_no
 * @property string|null $ors_date
 * @property string|null $responsibility_center
 * @property int|null $created_by
 * @property string|null $date_created
 *
 * @property PpmpPo $po
 */
class Ors extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_ors';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pr_id', 'po_id', 'created_by'], 'integer'],
            [['ors_date', 'date_created'], 'safe'],
            [['ors_no'], 'string', 'max' => 20],
            [['responsibility_center'], 'string', 'max' => 50],
            [['po_id'], 'exist', 'skipOnError' => true, 'targetClass' => Po::className(), 'targetAttribute' => ['po_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pr_id' => 'Pr ID',
            'po_id' => 'Po ID',
            'ors_no' => 'ORS No',
            'ors_date' => 'ORS Date',
            'responsibility_center' => 'Responsibility Center',
            'created_by' => 'Created By',
            'date_created' => 'Date Created',
        ];
    }

    /**
     * Gets query for [[Po]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPo()
    {
        return $this->hasOne(PpmpPo::className(), ['id' => 'po_id']);
    }
}
