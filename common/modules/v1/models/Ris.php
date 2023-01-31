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
 * This is the model class for table "ppmp_ris".
 *
 * @property int $id
 * @property string|null $ris_no
 * @property int|null $office_id
 * @property int|null $section_id
 * @property int|null $unit_id
 * @property int|null $ppmp_id
 * @property int|null $fund_cluster_id
 * @property string|null $purpose
 * @property string|null $date_required
 * @property int|null $created_by
 * @property int|null $requested_by
 * @property int|null $approved_by
 * @property int|null $issued_by
 * @property int|null $received_by
 *
 * @property PpmpRisItem[] $ppmpRisItems
 */
class Ris extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_ris';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['office_id', 'ppmp_id', 'fund_source_id', 'fund_cluster_id', 'requested_by', 'date_requested', 'date_required', 'purpose', 'type'], 'required', 'on' => 'isAdmin'],
            [['ppmp_id', 'fund_source_id', 'fund_cluster_id', 'requested_by', 'date_required', 'purpose', 'type'], 'required', 'on' => 'isUser'],
            [['date_approved'], 'required', 'on' => 'Approve'],
            [['disapproved_by', 'date_disapproved'], 'required', 'on' => 'Disapprove'],
            [['fund_cluster_id'], 'integer'],
            [['purpose', 'created_by', 'requested_by', 'approved_by', 'issued_by', 'received_by', 'office_id', 'section_id', 'unit_id'], 'string'],
            [['date_required', 'date_created', 'date_requested', 'date_approved', 'date_issued', 'date_received'], 'safe'],
            [['ris_no'], 'string', 'max' => 15],
            [['ppmp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ppmp::className(), 'targetAttribute' => ['ppmp_id' => 'id']],
            [['fund_source_id'], 'exist', 'skipOnError' => true, 'targetClass' => FundSource::className(), 'targetAttribute' => ['fund_source_id' => 'id']],
            [['fund_cluster_id'], 'exist', 'skipOnError' => true, 'targetClass' => FundCluster::className(), 'targetAttribute' => ['fund_cluster_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ris_no' => 'RIS No.',
            'office_id' => 'Division',
            'ppmp_id' => 'Year',
            'ppmpName' => 'Year',
            'officeName' => 'Division',
            'section_id' => 'Section',
            'unit_id' => 'Unit',
            'fund_source_id' => 'Fund Source',
            'fundSourceName' => 'Fund Source',
            'fund_cluster_id' => 'Fund Cluster',
            'fundClusterName' => 'Fund Cluster',
            'purpose' => 'Purpose',
            'date_required' => 'Date Required',
            'created_by' => 'Created By',
            'creatorName' => 'Created By',
            'date_created' => 'Date Created',
            'requested_by' => 'Requested By',
            'requesterName' => 'Requested By',
            'date_requested' => 'Date Requested',
            'approved_by' => 'Approved By',
            'date_approved' => 'Date Approved',
            'disapproved_by' => 'Disapproved By',
            'date_disapproved' => 'Date Disapproved',
            'issued_by' => 'Issued By',
            'date_issued' => 'Date Issued',
            'received_by' => 'Received By',
            'date_received' => 'Date Received',
            'status' => 'Status',
            'statusName' => 'Status',
            'type' => 'Type',
            'total' => 'Total',
            'prNos' => 'PR',
            'prNo' => 'PR No.',
        ];
    }

    /**
     * Gets query for [[PpmpRisItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRisItems()
    {
        return $this->hasMany(RisItem::className(), ['ris_id' => 'id']);
    }

    public function getRisSources()
    {
        return $this->hasMany(RisSource::className(), ['ris_id' => 'id']);
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

    public function getPpmp()
    {
        return $this->hasOne(Ppmp::className(), ['id' => 'ppmp_id']);
    }

    public function getPpmpName()
    {
        return $this->ppmp ? Yii::$app->user->can('Administrator') ? $this->ppmp->title : $this->ppmp->year : '';
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

    public function getIssuer()
    {
        return $this->hasOne(UserInfo::className(), ['EMP_N' => 'issued_by']); 
    }

    public function getIssuerName()
    {
        return $this->issuer ? ucwords(strtolower($this->issuer->FIRST_M.' '.$this->issuer->LAST_M)) : '';
    }

    public function getReceiver()
    {
        return $this->hasOne(UserInfo::className(), ['EMP_N' => 'received_by']); 
    }

    public function getReceiverName()
    {
        return $this->receiver ? ucwords(strtolower($this->receiver->FIRST_M.' '.$this->receiver->LAST_M)) : '';
    }

    public function getDisapprover()
    {
        return $this->hasOne(Signatory::className(), ['emp_id' => 'disapproved_by']);
    }

    public function getDisApproverName()
    {
        return $this->disapprover ? $this->disapprover->name : '';
    }

    public function getStatus()
    {
        return $this->hasOne(Transaction::className(), ['model_id' => 'id'])->onCondition(['model' => 'Ris'])->orderBy(['datetime' => SORT_DESC]);
    }

    public function getStatusName()
    {
        return $this->status ? $this->status->status : 'No status';
    }
    
    public function getItems($type)
    {
        $items = RisItem::find()->where(['ris_id' => $this->id, 'type' => $type])->all();

        return $items;
    }

    public function getItemsTotal($type)
    {
        $total = RisItem::find()
                ->select(['COALESCE(sum(ppmp_ris_item.cost * quantity), 0) as total'])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->where([
                    'ris_id' => $this->id,
                    'ppmp_ris_item.type' => $type
                ])
                ->asArray()
                ->one();
        
        return !empty($total) ? $total['total'] : 0;
    }

    public function getItemSourceTotal($type)
    {
        $total = RisSource::find()
                ->select(['COALESCE(sum(ppmp_ris_item.cost * ppmp_ris_source.quantity), 0) as total'])
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_ris_source.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->where([
                    'ppmp_ris_source.ris_id' => $this->id,
                    'ppmp_ris_item.type' => $type,
                    'ppmp_ris_source.type' => $type
                ])
                ->asArray()
                ->one();
        
        return !empty($total) ? $total['total'] : 0;
    }

    public function getRealignAmount()
    {
        $supplementalTotal = $this->getItemsTotal('Supplemental');

        return $supplementalTotal ? $supplementalTotal : 0;
    }

    public function getRealignedAmount()
    {
        $supplementalTotal = $this->getItemsTotal('Supplemental');
        $realignedSourceTotal = $this->getItemSourceTotal('Realigned');

        return $supplementalTotal - $realignedSourceTotal > 0 ? $supplementalTotal - $realignedSourceTotal : 0;
    }

    public function getTotal()
    {
        $total = $this->getItemsTotal('Original') + $this->getItemsTotal('Supplemental');

        return $total;
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

    public function getPrNos()
    {
        $prItems = PrItem::findAll(['ris_id' => $this->id]);
        $prIDs = ArrayHelper::map($prItems, 'pr_id', 'pr_id');
        
        $prNos = Pr::find()->select(['id', 'pr_no'])->where(['id' => $prIDs])->asArray()->all();
        $ids = [];

        if(!empty($prNos))
        {
            foreach($prNos as $pr)
            {
                $ids[] = Html::a($pr['pr_no'], ['/v1/pr/view', 'id' => $pr['id']], ['target' => '_blank']);
            }
        }

        return implode('<br>', $ids);
    }

    public function getPrexcs()
    {
        $items = RisItem::find()
                ->select([
                    'IF(ppmp_pap.short_code IS NULL,
                    concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code,"-",
                        ppmp_sub_activity.code
                    )
                    ,
                    concat(
                        ppmp_pap.short_code,"-",
                        ppmp_activity.code,"-",
                        ppmp_sub_activity.code
                    )
                ) as prexc'
                ])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->andWhere(['ris_id' => $this->id])
                ->andWhere(['in', 'ppmp_ris_item.type', ['Original', 'Supplemental']])
                ->asArray()
                ->all();
        
        $items = ArrayHelper::map($items, 'prexc', 'prexc');

        return implode('<br>', $items);
    }
    
    public function getRealignedPrexcs()
    {
        $items = RisItem::find()
                ->select([
                    'IF(ppmp_pap.short_code IS NULL,
                    concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code,"-",
                        ppmp_sub_activity.code
                    )
                    ,
                    concat(
                        ppmp_pap.short_code,"-",
                        ppmp_activity.code,"-",
                        ppmp_sub_activity.code
                    )
                ) as prexc'
                ])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->andWhere(['ris_id' => $this->id])
                ->andWhere(['in', 'ppmp_ris_item.type', ['Realigned']])
                ->asArray()
                ->all();
        
        $items = ArrayHelper::map($items, 'prexc', 'prexc');

        return implode('<br>', $items);
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
            $status->model = 'Ris';
            $status->model_id = $this->id;
            $status->status = 'Draft';
            $status->save();
        }

        parent::afterSave($insert, $changedAttributes);
    }
}
