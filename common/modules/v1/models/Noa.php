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
            [['pr_id', 'bid_id', 'supplier_id', 'created_by'], 'integer'],
            [['date_created'], 'safe'],
            [['bid_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bid::className(), 'targetAttribute' => ['bid_id' => 'id']],
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
            'bid_id' => 'Bid ID',
            'supplier_id' => 'Supplier ID',
            'created_by' => 'Created By',
            'date_created' => 'Date Created',
        ];
    }

    /**
     * Gets query for [[Po]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBid()
    {
        return $this->hasOne(Bid::className(), ['id' => 'bid_id']);
    }

    /**
     * Gets query for [[Po]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(Supplier::className(), ['id' => 'supplier_id']);
    }
}
