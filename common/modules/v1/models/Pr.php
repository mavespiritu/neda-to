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
        ];
    }

    public function getPrItems()
    {
        return $this->hasMany(PrItem::className(), ['pr_id' => 'id']);
    }

    public function getRisNos()
    {
        $prItems = PrItem::findAll(['pr_id' => $this->id]);
        $risIDs = ArrayHelper::map($prItems, 'ris_id', 'ris_id');
        
        $risNos = Ris::find()->where(['id' => $risIDs])->all();
        $ids = [];

        if($risNos)
        {
            foreach($risNos as $ris)
            {
                $ids[] = Html::a($ris->ris_no, ['/v1/ris/info', 'id' => $ris->id], ['target' => '_blank']);
            }
        }

        return implode('<br>', $ids);
    }

    public function getRfqs()
    {
        return $this->hasMany(Rfq::className(), ['pr_id' => 'id']);
    }

    public function getRfqCount()
    {
        $total = Rfq::find()->where(['pr_id' => $this->id])->count();

        return $total;
    }

    public function getItemCount()
    {
        $items = PrItem::find()
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_pr_item.cost and 
                                                    s.type = ppmp_pr_item.type')
                ->andWhere([
                    'pr_id' => $this->id,
                ])
                ->groupBy(['ppmp_item.id', 's.id', 'ppmp_pr_item.cost'])
                ->count();
        
        return $items;
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
            ->groupBy(['ppmp_item.id', 's.id', 'ppmp_pr_item.cost'])
            ->asArray()
            ->all();
        
        return $aprItems;
    }

    public function getRfqItems()
    {
        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->where(['pr_id' => $this->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $rfqItems = PrItem::find()
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
            ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
            ->groupBy(['ppmp_item.id', 's.id', 'ppmp_pr_item.cost'])
            ->asArray()
            ->all();
        
        return $rfqItems;
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
}
