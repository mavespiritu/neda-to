<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_lot_item".
 *
 * @property int $id
 * @property int|null $lot_id
 * @property int|null $pr_item_id
 *
 * @property PpmpLot $lot
 * @property PpmpPrItem $prItem
 */
class LotItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_lot_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lot_id', 'pr_item_id'], 'integer'],
            [['lot_id'], 'exist', 'skipOnError' => true, 'targetClass' => Lot::className(), 'targetAttribute' => ['lot_id' => 'id']],
            [['pr_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => PrItem::className(), 'targetAttribute' => ['pr_item_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lot_id' => 'Lot ID',
            'pr_item_id' => 'Pr Item ID',
        ];
    }

    /**
     * Gets query for [[Lot]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLot()
    {
        return $this->hasOne(Lot::className(), ['id' => 'lot_id']);
    }

    /**
     * Gets query for [[PrItem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrItem()
    {
        return $this->hasOne(PrItem::className(), ['id' => 'pr_item_id']);
    }
}
