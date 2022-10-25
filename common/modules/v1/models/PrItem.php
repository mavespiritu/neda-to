<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_pr_item".
 *
 * @property int $id
 * @property int|null $pr_id
 * @property int|null $ris_id
 * @property int|null $ris_item_id
 * @property int|null $ppmp_item_id
 * @property int|null $month_id
 * @property float|null $cost
 * @property int|null $quantity
 * @property string|null $type
 *
 * @property PpmpPr $pr
 * @property PpmpRisItem $risItem
 */
class PrItem extends \yii\db\ActiveRecord
{
    public $item_id;
    public $activity_id;
    public $sub_activity_id;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_pr_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pr_id', 'ris_id', 'ris_item_id', 'ppmp_item_id', 'month_id', 'quantity'], 'integer'],
            [['cost'], 'number'],
            [['type'], 'string'],
            [['pr_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pr::className(), 'targetAttribute' => ['pr_id' => 'id']],
            [['ris_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => RisItem::className(), 'targetAttribute' => ['ris_item_id' => 'id']],
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
            'ris_id' => 'Ris ID',
            'ris_item_id' => 'Ris Item',
            'ppmp_item_id' => 'Ppmp Item',
            'month_id' => 'Month ID',
            'cost' => 'Cost',
            'quantity' => 'Quantity',
            'type' => 'Type',
        ];
    }

    /**
     * Gets query for [[Pr]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPr()
    {
        return $this->hasOne(Pr::className(), ['id' => 'pr_id']);
    }

    /**
     * Gets query for [[RisItem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRisItem()
    {
        return $this->hasOne(RisItem::className(), ['id' => 'ris_item_id']);
    }

    public function getPpmpItem()
    {
        return $this->hasOne(PpmpItem::className(), ['id' => 'ppmp_item_id']);
    }

    public function getIncludedItems()
    {
        $items = [];

        return $items;
    }
}
