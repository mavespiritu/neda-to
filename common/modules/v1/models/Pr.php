<?php

namespace common\modules\v1\models;

use Yii;
use markavespiritu\user\models\Office;
use markavespiritu\user\models\Section;
use markavespiritu\user\models\Unit;
use markavespiritu\user\models\UserInfo;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
/**
 * This is the model class for table "ppmp_pr".
 *
 * @property int $id
 * @property string|null $pr_no
 * @property string|null $office_id
 * @property string|null $section_id
 * @property string|null $unit_id
 * @property int|null $fund_source_id
 * @property int|null $fund_cluster_id
 * @property string|null $purpose
 * @property string|null $requested_by
 * @property string|null $date_requested
 * @property string|null $approved_by
 * @property string|null $date_approved
 * @property string|null $type
 */
class Pr extends \yii\db\ActiveRecord
{
    public $ris_id;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_pr';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ris_id'], 'required', 'on' => 'selectRis'],
            [['type', 'office_id', 'year', 'fund_source_id', 'fund_cluster_id', 'purpose', 'date_requested', 'requested_by', 'procurement_mode_id'], 'required'],
            [['date_prepared'], 'required', 'on' => 'printPr'],
            [['fund_source_id', 'fund_cluster_id'], 'integer'],
            [['purpose', 'type'], 'string'],
            [['year'], 'integer'],
            [['date_requested', 'date_approved', 'date_created'], 'safe'],
            [['pr_no', 'office_id', 'section_id', 'unit_id', 'requested_by', 'approved_by', 'created_by'], 'string', 'max' => 100],
            [['fund_source_id'], 'exist', 'skipOnError' => true, 'targetClass' => FundSource::className(), 'targetAttribute' => ['fund_source_id' => 'id']],
            [['fund_cluster_id'], 'exist', 'skipOnError' => true, 'targetClass' => FundCluster::className(), 'targetAttribute' => ['fund_cluster_id' => 'id']],
            [['procurement_mode_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProcurementMode::className(), 'targetAttribute' => ['procurement_mode_id' => 'id']],
            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pr_no' => 'PR No.',
            'office_id' => 'Division',
            'officeName' => 'Division',
            'section_id' => 'Section',
            'unit_id' => 'Unit',
            'fund_source_id' => 'Fund Source',
            'fundSourceName' => 'Fund Source',
            'fund_cluster_id' => 'Fund Cluster',
            'fundClusterName' => 'Fund Cluster',
            'purpose' => 'Purpose',
            'created_by' => 'Created By',
            'creatorName' => 'Created By',
            'date_created' => 'Date Created',
            'requested_by' => 'Requested By',
            'requesterName' => 'Requested By',
            'date_requested' => 'Date Requested',
            'approved_by' => 'Approved By',
            'approverName' => 'Approved By',
            'date_approved' => 'Date Approved',
            'disapproved_by' => 'Disapproved By',
            'date_disapproved' => 'Date Disapproved',
            'year' => 'Year',
            'type' => 'Type',
            'procurement_mode_id' => 'Mode of Procurement',
            'procurementModeName' => 'Mode of Procurement',
            'ris_id' => 'Approved RIS',
            'risNos' => 'RIS',
            'risNo' => 'RIS No.',
            'statusName' => 'Status',
            'date_prepared' => 'Date Prepared'
        ];
    }

    public function getPrItems()
    {
        return $this->hasMany(PrItem::className(), ['pr_id' => 'id']);
    }

    public function getPos()
    {
        return $this->hasMany(Po::className(), ['pr_id' => 'id']);
    }

    public function getRisNos()
    {
        $prItems = PrItem::findAll(['pr_id' => $this->id]);
        $risIDs = ArrayHelper::map($prItems, 'ris_id', 'ris_id');
        
        $risNos = Ris::find()->select(['id', 'ris_no'])->where(['id' => $risIDs])->asArray()->all();
        $ids = [];

        if($risNos)
        {
            foreach($risNos as $ris)
            {
                $ids[] = Html::a($ris['ris_no'], ['/v1/ris/info', 'id' => $ris['id']], ['target' => '_blank']);
            }
        }

        return implode('<br>', $ids);
    }

    public function getRfqs()
    {
        return $this->hasMany(Rfq::className(), ['pr_id' => 'id']);
    }

    public function getHasBid()
    {
        $rfqs = $this->rfqs;
        $withBidCount = $this->getRfqs()->count();
        $hasBid = 0;
        if($rfqs)
        {
            foreach($rfqs as $rfq)
            {
                $hasBid = $rfq->hasBid ? $hasBid + 1 : $hasBid;
            }
        }

        return $withBidCount > 0 ? $withBidCount == $hasBid ? true : false : false;
    }

    public function getHasNoa()
    {
        $withNoaCount = $this->noaCount;

        $winners = BidWinner::find()
                ->leftJoin('ppmp_bid', 'ppmp_bid.id = ppmp_bid_winner.bid_id')
                ->andWhere(['pr_id' => $this->id])
                ->andWhere(['IS NOT', 'supplier_id', null])
                ->all();
        $winners = ArrayHelper::map($winners, 'supplier_id', 'supplier_id');

        return count($winners) > 0 ? count($winners) == $withNoaCount ? true : false : false;
    }

    public function getSupplierHasPoOrContract($bid_id, $supplier_id)
    {
        $po = !is_null($bid_id) ? Po::findOne(['pr_id' => $this->id, 'bid_id' => $bid_id, 'supplier_id' => $supplier_id]) : Po::find()
                            ->andWhere(['pr_id' => $this->id, 'supplier_id' => $supplier_id])
                            ->andWhere(['is', 'bid_id', null])
                            ->one();

        return $po ? true : false;
    }

    public function getSupplierHasPo($bid_id, $supplier_id, $type)
    {
        $po = !is_null($bid_id) ? Po::findOne(['pr_id' => $this->id, 'bid_id' => $bid_id, 'supplier_id' => $supplier_id, 'type' => $type]) : Po::find()
                            ->andWhere(['pr_id' => $this->id, 'supplier_id' => $supplier_id, 'type' => $type])
                            ->andWhere(['is', 'bid_id', null])
                            ->one();
        return $po ? true : false;
    }

    public function getSupplierHasNtps($supplier_id)
    {
        $pos = Po::findAll(['pr_id' => $this->id, 'supplier_id' => $supplier_id]);
        $ntps = Ntp::find()
                ->leftJoin('ppmp_po', 'ppmp_po.id = ppmp_ntp.po_id')
                ->andWhere(['ppmp_po.supplier_id' => $supplier_id])
                ->andWhere(['ppmp_ntp.pr_id' => $this->id])
                ->all();

        return count($pos) > 0 ? count($pos) == count($ntps) ? true : false : false;
    }

    public function getSupplierHasNtp($po_id)
    {
        $ntp = Ntp::findOne(['pr_id' => $this->id, 'po_id' => $po_id]);

        return $ntp ? true : false;
    }

    public function getItemsHasOrs($apr_id, $po_id, $type)
    {
        $ors = null;
        if($type == 'APR')
        {
            $ors = $apr_id != 'null' ? Ors::find()->andWhere(['pr_id' => $this->id, 'apr_id' => $apr_id])->andWhere(['is', 'po_id', null])->all() : null;

        }else if($type == 'PO')
        {
            $ors = $po_id != 'null' ? Ors::find()->andWhere(['pr_id' => $this->id, 'po_id' => $po_id])->andWhere(['is', 'apr_id', null])->all() : null;

        }else if($type == 'NP')
        {
            $ors = $po_id != 'null' ? Ors::find()->andWhere(['pr_id' => $this->id])->andWhere(['is', 'apr_id', null])->andWhere(['is', 'po_id', null])->all() : null;
        }

        return !is_null($ors) ? true : false;
    }

    public function getHasOrs()
    {
        $nonProcurableItemIDs = NonProcurableItem::find()
                    ->where(['pr_id' => $this->id])
                    ->asArray()
                    ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $bidIDs = Bid::find()
        ->select(['ppmp_bid.id as id'])
        ->leftJoin('ppmp_rfq', 'ppmp_rfq.id = ppmp_bid.rfq_id')
        ->where(['ppmp_bid.pr_id' => $this->id])
        ->asArray()
        ->all();

        $bidIDs = ArrayHelper::map($bidIDs, 'id', 'id');

        $awardedItemIDs = BidWinner::find()->select(['pr_item_id'])->andWhere(['bid_id' => $bidIDs])->andWhere(['status' => 'Awarded'])->asArray()->all();

        $awardedItemIDs = ArrayHelper::map($awardedItemIDs, 'pr_item_id', 'pr_item_id');

        $forOrsIDs = array_merge($nonProcurableItemIDs, $awardedItemIDs);

        $orsItemIDs = OrsItem::find()
                ->select(['pr_item_id'])
                ->where(['pr_id' => $this->id])
                ->asArray()
                ->all();

        $orsItemIDs = ArrayHelper::map($orsItemIDs, 'pr_item_id', 'pr_item_id');

        return $awardedItemIDs > 0 ? empty(array_diff($forOrsIDs, $orsItemIDs)) ? true : false : false;
    }
    
    public function getLots()
    {
        return $this->hasMany(Lot::className(), ['pr_id' => 'id']);
    }

    public function getRfqInfos()
    {
        $total = RfqInfo::find()
                    ->leftJoin('ppmp_rfq', 'ppmp_rfq.id = ppmp_rfq_info.rfq_id')
                    ->where(['ppmp_rfq.pr_id' => $this->id])
                    ->all();
        return $total;
    }

    public function getRfqInfoCount()
    {
        $total = RfqInfo::find()
                    ->leftJoin('ppmp_rfq', 'ppmp_rfq.id = ppmp_rfq_info.rfq_id')
                    ->where(['ppmp_rfq.pr_id' => $this->id])
                    ->count();
        return $total;
    }

    public function getAprInfoCount()
    {
        $total = PrItemCost::find()
                ->where(['pr_id' => $this->id, 'supplier_id' => 1])
                ->count();

        return $total;
    }

    public function getAprCount()
    {
        $total = Apr::find()->where(['pr_id' => $this->id])->count();

        return $total;
    }

    public function getRfqCount()
    {
        $total = Rfq::find()->where(['pr_id' => $this->id])->count();

        return $total;
    }

    public function getNonProcurableCount()
    {
        $total = NonProcurableItem::find()
                    ->where(['pr_id' => $this->id])
                    ->count();
        return $total;
    }

    public function getBidCount()
    {
        $total = Bid::find()
                    ->leftJoin('ppmp_rfq', 'ppmp_rfq.id = ppmp_bid.rfq_id')
                    ->where(['ppmp_bid.pr_id' => $this->id])
                    ->count();
        return $total;
    }

    public function getWinners()
    {
        $bids = Bid::findAll(['pr_id' => $this->id]);
        $bids = ArrayHelper::map($bids, 'id', 'id');

        $winners = BidWinner::find()
                ->select(['supplier_id'])
                ->andWhere(['bid_id' => $bids])
                ->andWhere(['status' => 'Awarded'])
                ->asArray()
                ->all();
        
        $winners = ArrayHelper::map($winners, 'supplier_id', 'supplier_id');

        $suppliers = Supplier::find()->andWhere(['<>', 'supplier_id', 1])->andWhere(['id' => $winners])->all();

        return $suppliers;
    }

    public function getWinnerCount()
    {
        $bids = Bid::findAll(['pr_id' => $this->id]);
        $bids = ArrayHelper::map($bids, 'id', 'id');

        $winners = BidWinner::find()
                ->select(['supplier_id'])
                ->andWhere(['bid_id' => $bids])
                ->andWhere(['status' => 'Awarded'])
                ->asArray()
                ->all();
        
        $suppliers = ArrayHelper::map($winners, 'supplier_id', 'supplier_id');

        return count($suppliers);
    }

    public function getPoCount()
    {
        $total = Po::find()
                    ->where(['pr_id' => $this->id])
                    ->count();
        return $total;
    }

    public function getNtpCount()
    {
        $total = Ntp::find()
                    ->where(['pr_id' => $this->id])
                    ->count();
        return $total;
    }

    public function getNoaCount()
    {
        $total = Noa::find()
                    ->where(['pr_id' => $this->id])
                    ->count();
        return $total;
    }

    public function getOrsCount()
    {
        $total = Ors::find()
                    ->where(['pr_id' => $this->id])
                    ->count();
        return $total;
    }
    

    public function getOrsWithoutPo()
    {
        $total = Ors::find()
                    ->andWhere(['pr_id' => $this->id, 'type' => 'NP'])
                    ->andWhere(['is', 'po_id', null])
                    ->count();
        return $total;
    }

    public function getOrsOfApr()
    {
        $total = Ors::find()
                    ->andWhere(['pr_id' => $this->id, 'type' => 'APR'])
                    ->andWhere(['is', 'po_id', null])
                    ->count();
        return $total;
    }

    public function getIarCount()
    {
        $total = Iar::find()
                    ->where(['pr_id' => $this->id])
                    ->count();
        return $total;
    }

    public function getItemCount()
    {
        $items = PrItem::find()
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_pr_item.cost and 
                                                    s.type = ppmp_pr_item.type')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $this->id,
                ])
                ->groupBy(['ppmp_item.id', 'ppmp_ris.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
                ->count();
        
        return $items;
    }

    public function getAprItemCount()
    {
        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->where(['pr_id' => $this->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $items = PrItem::find()
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_pr_item.cost and 
                                                    s.type = ppmp_pr_item.type')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $this->id,
                ])
                ->andWhere(['in', 'ppmp_pr_item.id', $aprItemIDs])
                ->groupBy(['ppmp_item.id', 'ppmp_ris.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
                ->count();
        
        return $items;
    }

    public function getRfqItemCount()
    {
        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->where(['pr_id' => $this->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $nonProcurableItemIDs = NonProcurableItem::find()
                ->select(['pr_item_id'])
                ->where(['pr_id' => $this->id])
                ->asArray()
                ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $items = PrItem::find()
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_pr_item.cost and 
                                                    s.type = ppmp_pr_item.type')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $this->id,
                ])
                ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
                ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                ->groupBy(['ppmp_item.id', 'ppmp_ris.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
                ->count();
        
        return $items;
    }

    public function getNonProcurableItemCount()
    {
        $nonProcurableItemIDs = NonProcurableItem::find()
                ->select(['pr_item_id'])
                ->where(['pr_id' => $this->id])
                ->asArray()
                ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $items = PrItem::find()
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_pr_item.cost and 
                                                    s.type = ppmp_pr_item.type')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $this->id,
                ])
                ->andWhere(['in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                ->groupBy(['ppmp_item.id', 'ppmp_ris.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
                ->count();
        
        return $items;
    }

    public function getOrsItemCount()
    {
        $orsItemIDs = OrsItem::find()
                ->select(['pr_item_id'])
                ->where(['pr_id' => $this->id])
                ->asArray()
                ->all();

        $orsItemIDs = ArrayHelper::map($orsItemIDs, 'pr_item_id', 'pr_item_id');

        $items = PrItem::find()
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_pr_item.cost and 
                                                    s.type = ppmp_pr_item.type')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $this->id,
                ])
                ->andWhere(['in', 'ppmp_pr_item.id', $orsItemIDs])
                ->groupBy(['ppmp_item.id', 'ppmp_ris.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
                ->count();
        
        return $items;
    }

    public function getRfqTotal()
    {
        $total = 0;

        $items = $this->rfqItemsWithAprItems;
        
        if(!empty($items))
        {
            foreach($items as $item)
            {
                $total += $item['cost'] * $item['total'];
            }
        }
        
        return $total;
    }

    public function getTotal()
    {
        $total = PrItem::find()
                ->select(['COALESCE(sum(cost * quantity), 0) as total'])
                ->where([
                    'pr_id' => $this->id
                ])
                ->asArray()
                ->one();
        
        return !empty($total) ? $total['total'] : 0;
    }

    public function getStatus()
    {
        return $this->hasOne(Transaction::className(), ['model_id' => 'id'])->onCondition(['model' => 'Pr'])->orderBy(['datetime' => SORT_DESC]);
    }

    public function getStatusName()
    {
        return $this->status ? $this->status->status : 'No status';
    }

    public function getApr()
    {
        return $this->hasOne(Apr::className(), ['pr_id' => 'id']);
    }

    public function getAprItems()
    {
        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->where(['pr_id' => $this->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $aprItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                's.id as ris_item_spec_id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                s.activity_id = ppmp_ppmp_item.activity_id and 
                                                s.item_id = ppmp_ppmp_item.item_id and 
                                                s.cost = ppmp_pr_item.cost and 
                                                s.type = ppmp_pr_item.type')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $this->id,
            ])
            ->andWhere(['in', 'ppmp_pr_item.id', $aprItemIDs])
            ->groupBy(['ppmp_item.id', 'ppmp_ris.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
            ->asArray()
            ->all();
        
        return $aprItems;
    }

    public function getRfqItems()
    {
        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->andWhere(['pr_id' => $this->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $nonProcurableItemIDs = NonProcurableItem::find()
                ->select(['pr_item_id'])
                ->where(['pr_id' => $this->id])
                ->asArray()
                ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $rfqItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $this->id,
            ])
            ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
            ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();
        
        return $rfqItems;
    }

    public function getRfqItemsWithAprItems()
    {
        $aprItemsWithValueIDs = PrItemCost::find()
                            ->select(['pr_item_id'])
                            ->andWhere(['pr_id' => $this->id])
                            ->andWhere(['supplier_id' => 1])
                            ->andWhere(['>', 'cost', 0])
                            ->asArray()
                            ->all();
        
        $aprItemsWithValueIDs = ArrayHelper::map($aprItemsWithValueIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->andWhere(['pr_id' => $this->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = array_intersect($aprItemIDs, $aprItemsWithValueIDs);

        $nonProcurableItemIDs = NonProcurableItem::find()
                ->select(['pr_item_id'])
                ->where(['pr_id' => $this->id])
                ->asArray()
                ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $rfqItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'IF(ppmp_lot.title IS NOT NULL, concat("Lot No. ",ppmp_lot.lot_no," - ",ppmp_lot.title), 0) as lotTitle',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
            ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $this->id,
            ])
            ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
            ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 'lotTitle'])
            ->orderBy(['lotTitle' => SORT_ASC, 'item' => SORT_ASC])
            ->asArray()
            ->all();
        
        return $rfqItems;
    }

    public function getNonProcurableItems()
    {
        $nonProcurableItemIDs = NonProcurableItem::find()
                ->select(['pr_item_id'])
                ->where(['pr_id' => $this->id])
                ->asArray()
                ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $items = PrItem::find()
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_pr_item.cost and 
                                                    s.type = ppmp_pr_item.type')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $this->id,
                ])
                ->andWhere(['in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                ->groupBy(['ppmp_item.id', 'ppmp_ris.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
                ->asArray()
                ->all();
        
        return $items;
    }

    public function getOrsItems()
    {
        $orsItemIDs = OrsItem::find()
                    ->select(['pr_item_id'])
                    ->where(['pr_id' => $this->id])
                    ->asArray()
                    ->all();

        $orsItemIDs = ArrayHelper::map($orsItemIDs, 'pr_item_id', 'pr_item_id');

        $orsItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                's.id as ris_item_spec_id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                s.activity_id = ppmp_ppmp_item.activity_id and 
                                                s.item_id = ppmp_ppmp_item.item_id and 
                                                s.cost = ppmp_pr_item.cost and 
                                                s.type = ppmp_pr_item.type')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $this->id,
            ])
            ->andWhere(['in', 'ppmp_pr_item.id', $orsItemIDs])
            ->groupBy(['ppmp_item.id', 's.id', 'ppmp_pr_item.cost'])
            ->asArray()
            ->all();
        
        return $orsItems;
    }

    public function getFundSource()
    {
        return $this->hasOne(FundSource::className(), ['id' => 'fund_source_id']);
    }

    public function getFundSourceName()
    {
        return $this->fundSource ? $this->fundSource->code : '';
    }

    public function getFundCluster()
    {
        return $this->hasOne(FundCluster::className(), ['id' => 'fund_cluster_id']);
    }

    public function getFundClusterName()
    {
        return $this->fundCluster ? $this->fundCluster->title : '';
    }

    public function getProcurementMode()
    {
        return $this->hasOne(ProcurementMode::className(), ['id' => 'procurement_mode_id']);
    }

    public function getProcurementModeName()
    {
        return $this->procurementMode ? $this->procurementMode->title : '';
    }

    public function getOffice()
    {
        return $this->hasOne(Office::className(), ['abbreviation' => 'office_id']);
    }

    public function getOfficeName()
    {
        return $this->office ? $this->office->abbreviation : '';
    }

    public function getSection()
    {
        return $this->hasOne(Section::className(), ['abbreviation' => 'section_id']);
    }

    public function getSectionName()
    {
        return $this->office ? $this->section->abbreviation : '';
    }

    public function getUnit()
    {
        return $this->hasOne(Unit::className(), ['abbreviation' => 'unit_id']);
    }

    public function getUnitName()
    {
        return $this->office ? $this->unit->abbreviation : '';
    }

    public function getRequester()
    {
        return $this->hasOne(Signatory::className(), ['emp_id' => 'requested_by']);
    }

    public function getRequesterName()
    {
        return $this->requester ? $this->requester->name : '';
    }

    public function getCreator()
    {
        return $this->hasOne(UserInfo::className(), ['EMP_N' => 'created_by']); 
    }

    public function getCreatorName()
    {
        return $this->creator ? ucwords(strtolower($this->creator->FIRST_M.' '.$this->creator->LAST_M)) : '';
    }

    public function getApprover()
    {
        return $this->hasOne(Signatory::className(), ['emp_id' => 'approved_by']);
    }

    public function getApproverName() 
    {
        return $this->approver ? $this->approver->name : '';
    }

    public function getDisapprover()
    {
        return $this->hasOne(Signatory::className(), ['emp_id' => 'disapproved_by']);
    }

    public function getDisapproverName()
    {
        return $this->disapprover ? $this->disapprover->name : '';
    }

    public static function pageQuantityTotal($provider, $fieldName)
    {
        $total = 0;
        foreach($provider as $item){
            $total+=$item[$fieldName];
        }
        return '<b>'.number_format($total, 2).'</b>';
    }

    public function afterSave($insert, $changedAttributes){

        if($insert)
        {
            $status = new Transaction();
            $status->actor = Yii::$app->user->identity->userinfo->EMP_N;
            $status->model = 'Pr';
            $status->model_id = $this->id;
            $status->status = 'Draft';
            $status->save();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function getMenu()
    {
        $items = [];
        $i = 0;
        $j = 1;

        $items[$i]['label'] = '<span onclick="selectItemMenu('.$this->id.','.$j.')">'.$j.'. Manage Purchase Request (PR) Items</span>';
        $items[$i]['content'] = '<div id="select-item-menu"></div>';
        $items[$i]['options'] = ['class' => $this->prItems ? 'panel panel-success' : 'panel panel-default'];

        $i++;
        $j++;

        $items[$i]['label'] = '<span onclick="groupItemMenu('.$this->id.','.$j.')">'.$j.'. Group Purchase Request (PR) Items</span>';
        $items[$i]['content'] = '<div id="group-item-menu"></div>';
        $items[$i]['options'] = ['class' => !empty($this->aprItems) || !empty($this->rfqItems) || !empty($this->nonProcurableItems) ? 'panel panel-success' : 'panel panel-default'];

        $i++;
        $j++;

        if(!empty($this->aprItems))
        {
            $apr = $this->apr;

            $items[$i]['label'] = '<span onclick="aprMenu('.$this->id.','.$j.')">'.$j.'. Agency Procurement Request (APR)</span>';
            $items[$i]['content'] = '<div id="apr-menu"></div>';
            $items[$i]['options'] = ['class' => $this->apr ? !is_null($this->apr->date_prepared) ? 'panel panel-success' : 'panel panel-default' : 'panel panel-default'];

            $i++;
            $j++;

            //Activate if PO is also given to DBM
            /* if(empty($this->rfqItemsWithAprItems))
            {
                $items[$i]['label'] = '<span onclick="poMenu('.$this->id.','.$j.')">'.$j.'. Purchase Order (PO) / Contracts</span>';
                $items[$i]['content'] = '<div id="po-menu"></div>';
                $items[$i]['options'] = ['class' => $this->poCount > 0 ? 'panel panel-success' : 'panel panel-default'];

                $i++;
                $j++;
            } */
        }

        if(!empty($this->rfqItemsWithAprItems))
        {
            $items[$i]['label'] = '<span onclick="rfqMenu('.$this->id.','.$j.')">'.$j.'. Request for Quotation (RFQ)</span>';
            $items[$i]['content'] = '<div id="rfq-menu"></div>';
            $items[$i]['options'] = ['class' => $this->rfqInfos ? 'panel panel-success' : 'panel panel-default'];

            $i++;
            $j++;

            $items[$i]['label'] = '<span onclick="aoqMenu('.$this->id.','.$j.')">'.$j.'. Abstract of Quotation (AOQ)</span>';
            $items[$i]['content'] = '<div id="aoq-menu"></div>';
            $items[$i]['options'] = ['class' => $this->hasBid ? 'panel panel-success' : 'panel panel-default'];

            $i++;
            $j++;

            $items[$i]['label'] = '<span onclick="noaMenu('.$this->id.','.$j.')">'.$j.'. Notice of Award (NOA)</span>';
            $items[$i]['content'] = '<div id="noa-menu"></div>';
            $items[$i]['options'] = ['class' => $this->hasNoa ? 'panel panel-success' : 'panel panel-default'];

            $i++;
            $j++;

            $items[$i]['label'] = '<span onclick="poMenu('.$this->id.','.$j.')">'.$j.'. Purchase Order (PO) / Contracts</span>';
            $items[$i]['content'] = '<div id="po-menu"></div>';
            $items[$i]['options'] = ['class' => $this->winnerCount > 0 ? $this->poCount >= $this->winnerCount ? 'panel panel-success' : 'panel panel-default' : 'panel panel-default'];

            $i++;
            $j++;

            $items[$i]['label'] = '<span onclick="ntpMenu('.$this->id.','.$j.')">'.$j.'. Notice to Proceed (NTP)</span>';
            $items[$i]['content'] = '<div id="ntp-menu"></div>';
            $items[$i]['options'] = ['class' => $this->poCount > 0 ? $this->poCount == $this->ntpCount ? 'panel panel-success' : 'panel panel-default' : 'panel panel-default'];

            $i++;
            $j++;
        }

        $items[$i]['label'] = '<span onclick="orsMenu('.$this->id.','.$j.')">'.$j.'. Obligation Request Status (ORS)</span>';
        $items[$i]['content'] = '<div id="ors-menu"></div>';
        $items[$i]['options'] = ['class' => $this->hasOrs ? 'panel panel-success' : 'panel panel-default'];

        $i++;
        $j++;

        return $items;
    }
}
