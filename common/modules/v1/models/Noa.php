<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_noa".
 *
 * @property int $id
 * @property int|null $pr_id
 * @property int|null $po_id
 * @property int|null $created_by
 * @property string|null $date_created
 *
 * @property PpmpPo $po
 */
class Noa extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_noa';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_created'], 'required'],
            [['pr_id', 'po_id', 'created_by'], 'integer'],
            [['date_created'], 'safe'],
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
        return $this->hasOne(Po::className(), ['id' => 'po_id']);
    }
}
