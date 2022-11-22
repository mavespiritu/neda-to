<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_pr_item_cost".
 *
 * @property int $id
 * @property int|null $pr_id
 * @property int|null $pr_item_id
 * @property int|null $supplier_id
 * @property float|null $cost
 *
 * @property PpmpPrItem $prItem
 * @property PpmpSupplier $supplier
 */
class PrItemCost extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_pr_item_cost';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cost'], 'required'],
            [['pr_id', 'pr_item_id', 'rfq_id', 'rfq_info_id', 'supplier_id'], 'integer'],
            [['cost'], 'safe'],
            [['specification'], 'string'],
            [['pr_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => PrItem::className(), 'targetAttribute' => ['pr_item_id' => 'id']],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => Supplier::className(), 'targetAttribute' => ['supplier_id' => 'id']],
            [['rfq_info_id'], 'exist', 'skipOnError' => true, 'targetClass' => RfqInfo::className(), 'targetAttribute' => ['rfq_info_id' => 'id']],
            [['rfq_id'], 'exist', 'skipOnError' => true, 'targetClass' => Rfq::className(), 'targetAttribute' => ['supplier_id' => 'id']],
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
            'pr_item_id' => 'Pr Item ID',
            'supplier_id' => 'Supplier ID',
            'rfq_id' => 'Rfq ID',
            'rfq_info_id' => 'Rfq Info ID',
            'cost' => 'Cost',
            'specification' => 'Specification'
        ];
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

    /**
     * Gets query for [[Supplier]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(Supplier::className(), ['id' => 'supplier_id']);
    }

    /**
     * Gets query for [[Supplier]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRfq()
    {
        return $this->hasOne(Rfq::className(), ['id' => 'rfq_id']);
    }

    /**
     * Gets query for [[Supplier]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRfqInfo()
    {
        return $this->hasOne(RfqInfo::className(), ['id' => 'rfq_info_id']);
    }
}
