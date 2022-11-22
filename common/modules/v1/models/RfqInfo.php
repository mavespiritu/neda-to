<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_rfq_info".
 *
 * @property int $id
 * @property int|null $rfq_id
 * @property string|null $date_retrieved
 *
 * @property PpmpRfq $rfq
 */
class RfqInfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_rfq_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rfq_id', 'supplier_id', 'date_retrieved'], 'required'],
            [['rfq_id', 'supplier_id'], 'integer'],
            [['date_retrieved'], 'safe'],
            [['rfq_id'], 'exist', 'skipOnError' => true, 'targetClass' => Rfq::className(), 'targetAttribute' => ['rfq_id' => 'id']],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => Supplier::className(), 'targetAttribute' => ['supplier_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rfq_id' => 'RFQ No.',
            'supplier_id' => 'Supplier',
            'date_retrieved' => 'Date Retrieved',
        ];
    }

    /**
     * Gets query for [[Rfq]].
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
    public function getSupplier()
    {
        return $this->hasOne(Supplier::className(), ['id' => 'supplier_id']);
    }

    public function getTotal()
    {
        $total = PrItemCost::find()
                ->select([
                    'sum(COALESCE(ppmp_pr_item_cost.cost * ppmp_pr_item.quantity, 0)) as total'
                ])
                ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                ->where([
                    'rfq_id' => $this->rfq_id,
                    'rfq_info_id' => $this->id,
                    'supplier_id' => $this->supplier_id
                ])
                ->asArray()
                ->one();

        return $total['total'];
    }
}
