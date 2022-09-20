<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_bid_winner".
 *
 * @property int $id
 * @property int|null $bid_id
 * @property int|null $supplier_id
 * @property int|null $pr_item_id
 * @property string|null $justification
 *
 * @property PpmpBid $bid
 */
class BidWinner extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_bid_winner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['supplier_id'], 'required'],
            [['bid_id', 'supplier_id', 'pr_item_id'], 'integer'],
            [['justification', 'status'], 'string'],
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
            'bid_id' => 'Bid ID',
            'supplier_id' => 'Supplier',
            'pr_item_id' => 'Pr Item ID',
            'justification' => 'Justification',
            'status' => 'Status'
        ];
    }

    /**
     * Gets query for [[Bid]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBid()
    {
        return $this->hasOne(Bid::className(), ['id' => 'bid_id']);
    }
}
