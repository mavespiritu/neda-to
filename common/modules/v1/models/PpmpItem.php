<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_ppmp_item".
 *
 * @property int $id
 * @property int|null $appropriation_item_id
 * @property int|null $activity_id
 * @property int|null $sub_activity_id
 * @property int|null $obj_id
 * @property int|null $ppmp_id
 * @property int|null $item_id
 * @property float|null $cost
 * @property string|null $remarks
 *
 * @property PpmpActivity $activity
 * @property PpmpAppropriationItem $appropriationItem
 * @property PpmpPpmp $ppmp
 * @property PpmpItem $item
 * @property PpmpObj $obj
 * @property PpmpSubActivity $subActivity
 * @property PpmpPpmpItemBreakdown[] $ppmpPpmpItemBreakdowns
 */
class PpmpItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_ppmp_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_id', 'sub_activity_id', 'obj_id', 'item_id', 'cost'], 'required', 'on' => 'Supplemental'],
            [['sub_activity_id', 'obj_id', 'item_id'], 'required'],
            [['appropriation_item_id', 'activity_id', 'sub_activity_id', 'obj_id', 'ppmp_id', 'item_id', 'fund_source_id'], 'integer'],
            [['cost'], 'safe'],
            [['remarks', 'type'], 'string'],
            [['activity_id'], 'exist', 'skipOnError' => true, 'targetClass' => Activity::className(), 'targetAttribute' => ['activity_id' => 'id']],
            [['appropriation_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppropriationItem::className(), 'targetAttribute' => ['appropriation_item_id' => 'id']],
            [['ppmp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ppmp::className(), 'targetAttribute' => ['ppmp_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['obj_id'], 'exist', 'skipOnError' => true, 'targetClass' => Obj::className(), 'targetAttribute' => ['obj_id' => 'id']],
            [['sub_activity_id'], 'exist', 'skipOnError' => true, 'targetClass' => SubActivity::className(), 'targetAttribute' => ['sub_activity_id' => 'id']],
            [['fund_source_id'], 'exist', 'skipOnError' => true, 'targetClass' => FundSource::className(), 'targetAttribute' => ['fund_source_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'appropriation_item_id' => 'Appropriation Item ID',
            'source_id' => 'Source',
            'fund_source_id' => 'Fund Source',
            'activity_id' => 'Activity',
            'sub_activity_id' => 'PPA',
            'obj_id' => 'Object',
            'ppmp_id' => 'Ppmp ID',
            'item_id' => 'Item',
            'quantity' => 'Quantity',
            'quantityUsed' => 'Quantity',
            'cost' => 'Cost',
            'remarks' => 'Remarks',
            'type' => 'Type',
            'papName' => 'PAP',
            'activityName' => 'Activity',
            'subActivityName' => 'PPA',
            'objName' => 'Object',
            'itemName' => 'Item',
            'janQty' => 'J',
            'febQty' => 'F',
            'marQty' => 'M',
            'aprQty' => 'A',
            'mayQty' => 'M',
            'junQty' => 'J',
            'julQty' => 'J',
            'augQty' => 'A',
            'sepQty' => 'S',
            'octQty' => 'O',
            'novQty' => 'N',
            'decQty' => 'D',
            'fundSourceName' => 'Fund Source',
            'quantities' => 'Total Qty'
        ];
    }

    /**
     * Gets query for [[Activity]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActivity()
    {
        return $this->hasOne(Activity::className(), ['id' => 'activity_id']);
    }


    public function getActivityName()
    {
        return $this->activity ? $this->activity->pap->codeTitle.'-'.$this->activity->code.' - '.$this->activity->title : '';
    }

    /**
     * Gets query for [[AppropriationItem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAppropriationItem()
    {
        return $this->hasOne(AppropriationItem::className(), ['id' => 'appropriation_item_id']);
    }

    /**
     * Gets query for [[Ppmp]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPpmp()
    {
        return $this->hasOne(Ppmp::className(), ['id' => 'ppmp_id']);
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::className(), ['id' => 'item_id']);
    }

    public function getItemName()
    {
        return $this->item ? $this->item->title : '';
    }

    /**
     * Gets query for [[Obj]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getObj()
    {
        return $this->hasOne(Obj::className(), ['id' => 'obj_id']);
    }

    public function getObjName()
    {
        return $this->obj ? $this->obj->objectTitle : '';
    }

    /**
     * Gets query for [[SubActivity]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubActivity()
    {
        return $this->hasOne(SubActivity::className(), ['id' => 'sub_activity_id']);
    }

    public function getSubActivityName()
    {
        return $this->subActivity ? $this->subActivity->subActivityTitle : '';
    }

    /**
     * Gets query for [[FundSource]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFundSource()
    {
        return $this->hasOne(FundSource::className(), ['id' => 'fund_source_id']);
    }

    public function getFundSourceName()
    {
        return $this->fundSource ? $this->fundSource->code : '';
    }

    /**
     * Gets query for [[PpmpPpmpItemBreakdowns]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemBreakdowns()
    {
        return $this->hasMany(ItemBreakdown::className(), ['ppmp_item_id' => 'id']);
    }

    public static function pageQuantityTotal($provider, $fieldName)
    {
        $total = 0;
        foreach($provider as $item){
            $total+=$item[$fieldName];
        }
        return '<b>'.number_format($total, 2).'</b>';
    }

    public function getRisItems()
    {
        return $this->hasMany(RisItem::className(), ['ppmp_item_id' => 'id']);
    }

    public function getQuantities()
    {
        $total = ItemBreakdown::find()->select(['sum(quantity) as quantity'])->where(['ppmp_item_id' => $this->id])->limit(12)->asArray()->one();

        return $total ? $total['quantity'] : 0;
    }

    public function getRisPerMonth($month_id, $ris_id)
    {
        $total = RisSource::find()->select(['quantity'])
            ->andWhere(['ppmp_item_id' => $this->id, 'month_id' => $month_id, 'ris_id' => $ris_id])
            ->andWhere(['<>', 'type', 'Realigned'])
            ->asArray()
            ->one();

        return $total ? $total['quantity'] : 0;
    }

    public function getRealignedRisPerMonth($month_id, $ris_id)
    {
        $total = RisSource::find()->select(['quantity'])
            ->andWhere(['ppmp_item_id' => $this->id, 'month_id' => $month_id, 'ris_id' => $ris_id])
            ->andWhere(['type' => 'Realigned'])
            ->asArray()
            ->one();

        return $total ? $total['quantity'] : 0;
    }

    public function getPrPerMonth($month_id, $pr_id)
    {
        $total = PrItem::find()->select(['sum(quantity) as quantity'])
            ->andWhere(['ppmp_item_id' => $this->id, 'month_id' => $month_id, 'pr_id' => $pr_id])
            ->asArray()
            ->one();

        return $total ? $total['quantity'] : 0;
    }

    public function getPrAwardedPerMonth($month_id, $pr_id)
    {
        $total = BidWinner::find()->select(['sum(quantity) as quantity'])
            ->leftJoin('ppmp_pr_item p', 'p.id = ppmp_bid_winner.pr_item_id')
            ->andWhere(['p.ppmp_item_id' => $this->id, 'p.month_id' => $month_id, 'p.pr_id' => $pr_id, 'ppmp_bid_winner.status' => 'Awarded'])
            ->asArray()
            ->one();

        return $total ? $total['quantity'] : 0;
    }

    public function getPrObligatedPerMonth($month_id, $pr_id)
    {
        $total = OrsItem::find()->select(['sum(quantity) as quantity'])
            ->leftJoin('ppmp_pr_item p', 'p.id = ppmp_ors_item.pr_item_id')
            ->andWhere(['p.ppmp_item_id' => $this->id, 'p.month_id' => $month_id, 'p.pr_id' => $pr_id])
            ->asArray()
            ->one();

        return $total ? $total['quantity'] : 0;
    }

    public function getPrInspectedPerMonth($month_id, $pr_id)
    {
        $total = IarItem::find()->select(['sum(quantity) as quantity'])
            ->leftJoin('ppmp_pr_item p', 'p.id = ppmp_iar_item.pr_item_id')
            ->andWhere(['p.ppmp_item_id' => $this->id, 'p.month_id' => $month_id, 'p.pr_id' => $pr_id])
            ->asArray()
            ->one();

        return $total ? $total['quantity'] : 0;
    }

    public function getQuantityPerMonth($month_id)
    {
        $total = ItemBreakdown::find()->select(['quantity'])->where(['ppmp_item_id' => $this->id, 'month_id' => $month_id])->asArray()->one();

        return $total ? $total['quantity'] : 0;
    }

    public function getQuantityUsed()
    {
        $total = RisSource::find()->select(['sum(quantity) as quantity'])->where(['ppmp_item_id' => $this->id])->asArray()->one();

        return $total ? $total['quantity'] : 0;
    }

    public function getQuantityUsedPerMonth($month_id)
    {
        $total = RisSource::find()->select(['sum(quantity) as quantity'])->where(['ppmp_item_id' => $this->id, 'month_id' => $month_id])->asArray()->one();

        return $total ? $total['quantity'] : 0;
    }

    public function getQuantityUsedPerMonths($months)
    {
        $total = RisSource::find()->select(['sum(quantity) as quantity'])->where(['ppmp_item_id' => $this->id])->asArray()->one();

        return $total ? $total['quantity'] : 0;
    }


    public function getQuantityPerMonths($months)
    {
        $quantities = $this->getItemBreakdowns()->select(['sum(quantity) as total'])->where(['in', 'month_id', $months])->asArray()->one();

        return $quantities ? $quantities['total'] : 0;
    }

    public function getJanQtyUsed()
    {
        return $this->getQuantityUsedPerMonth(1);
    }

    public function getFebQtyUsed()
    {
        return $this->getQuantityUsedPerMonth(2);
    }

    public function getMarQtyUsed()
    {
        return $this->getQuantityUsedPerMonth(3);
    }

    public function getAprQtyUsed()
    {
        return $this->getQuantityUsedPerMonth(4);
    }

    public function getMayQtyUsed()
    {
        return $this->getQuantityUsedPerMonth(5);
    }

    public function getJunQtyUsed()
    {
        return $this->getQuantityUsedPerMonth(6);
    }

    public function getJulQtyUsed()
    {
        return $this->getQuantityUsedPerMonth(7);
    }

    public function getAugQtyUsed()
    {
        return $this->getQuantityUsedPerMonth(8);
    }

    public function getSepQtyUsed()
    {
        return $this->getQuantityUsedPerMonth(9);
    }

    public function getOctQtyUsed()
    {
        return $this->getQuantityUsedPerMonth(10);
    }

    public function getNovQtyUsed()
    {
        return $this->getQuantityUsedPerMonth(11);
    }

    public function getDecQtyUsed()
    {
        return $this->getQuantityUsedPerMonth(12);
    }

    public function getJanQty()
    {
        return $this->getQuantityPerMonth(1);
    }

    public function getFebQty()
    {
        return $this->getQuantityPerMonth(2);
    }

    public function getMarQty()
    {
        return $this->getQuantityPerMonth(3);
    }

    public function getAprQty()
    {
        return $this->getQuantityPerMonth(4);
    }

    public function getMayQty()
    {
        return $this->getQuantityPerMonth(5);
    }

    public function getJunQty()
    {
        return $this->getQuantityPerMonth(6);
    }

    public function getJulQty()
    {
        return $this->getQuantityPerMonth(7);
    }

    public function getAugQty()
    {
        return $this->getQuantityPerMonth(8);
    }

    public function getSepQty()
    {
        return $this->getQuantityPerMonth(9);
    }

    public function getOctQty()
    {
        return $this->getQuantityPerMonth(10);
    }

    public function getNovQty()
    {
        return $this->getQuantityPerMonth(11);
    }

    public function getDecQty()
    {
        return $this->getQuantityPerMonth(12);
    }

    public function getRemainingQuantity()
    {
        return $this->quantities - $this->quantityUsed;
    }

    public function getRemainingQuantityPerMonth($month_id)
    {
        $maxQuantity = $this->getQuantityPerMonth($month_id);
        $quantityUsed = $this->getQuantityUsedPerMonth($month_id);

        return $maxQuantity - $quantityUsed > 0 ? $maxQuantity - $quantityUsed : 0;
    }

    public function getRemainingQuantityPerMonths($months)
    {
        $maxQuantity = $this->getQuantityPerMonths($months);
        $quantityUsed = $this->getQuantityUsedPerMonths($months);

        return $maxQuantity - $quantityUsed > 0 ? $maxQuantity - $quantityUsed : 0;
    }

    public function getRemainingQuantityTotalCost()
    {
        return $this->remainingQuantity * $this->cost;
    }

    public function getTotalCost()
    {
        return $this->quantities * $this->cost;
    }

    public static function getTotalPerActivity($ppmp_id, $activity_id, $fund_source_id)
    {
        $quantity = ItemBreakdown::find()
                   ->select([
                       'ppmp_item_id',
                       'sum(quantity) as total'
                   ])
                    ->groupBy(['ppmp_item_id'])
                    ->createCommand()
                    ->getRawSql();

        $total = PpmpItem::find()
                ->select([
                    'sum(quantity.total * cost) as total'
                ])
                ->leftJoin(['quantity' => '('.$quantity.')'], 'quantity.ppmp_item_id = ppmp_ppmp_item.id')
                ->andWhere([
                    'ppmp_id' => $ppmp_id,
                    'activity_id' => $activity_id,
                    'fund_source_id' => $fund_source_id,
                ])
                ->asArray()
                ->one();
        
        return $total['total'];
    }

    public static function getTotalPerSubActivity($ppmp_id, $activity_id, $sub_activity_id, $fund_source_id)
    {
        $quantity = ItemBreakdown::find()
                   ->select([
                       'ppmp_item_id',
                       'sum(quantity) as total'
                   ])
                    ->groupBy(['ppmp_item_id'])
                    ->createCommand()
                    ->getRawSql();

        $total = PpmpItem::find()
                ->select([
                    'sum(quantity.total * cost) as total'
                ])
                ->leftJoin(['quantity' => '('.$quantity.')'], 'quantity.ppmp_item_id = ppmp_ppmp_item.id')
                ->andWhere([
                    'ppmp_id' => $ppmp_id,
                    'activity_id' => $activity_id,
                    'sub_activity_id' => $sub_activity_id,
                    'fund_source_id' => $fund_source_id,
                ])
                ->asArray()
                ->one();
        
        return $total['total'];
    }

    public static function getCountPerSubActivity($ppmp_id, $activity_id, $sub_activity_id, $fund_source_id)
    {
        $total = PpmpItem::find()
                ->andWhere([
                    'ppmp_id' => $ppmp_id,
                    'activity_id' => $activity_id,
                    'sub_activity_id' => $sub_activity_id,
                    'fund_source_id' => $fund_source_id,
                ])
                ->count();
        
        return $total > 1 ? $total.' items' : $total.' item';
    }
}
