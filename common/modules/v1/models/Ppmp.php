<?php

namespace common\modules\v1\models;

use Yii;
use markavespiritu\user\models\Office;
use markavespiritu\user\models\Section;
use markavespiritu\user\models\Unit;
use markavespiritu\user\models\UserInfo;
/**
 * This is the model class for table "ppmp_ppmp".
 *
 * @property int $id
 * @property int|null $division_id
 * @property string|null $stage
 * @property int|null $year
 * @property int|null $created_by
 * @property string|null $date_created
 * @property int|null $updated_by
 * @property string|null $date_updated
 *
 * @property PpmpPpmpItem[] $ppmpPpmpItems
 */
class Ppmp extends \yii\db\ActiveRecord
{
    public $cse;
    public $type;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_ppmp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['year','stage'], 'validatePpmp'],
            [['office_id', 'year', 'stage', 'copy', 'data'], 'required', 'on' => 'isAdminCopy'],
            [['year', 'stage', 'copy', 'data'], 'required', 'on' => 'isUserCopy'],
            [['year','stage'], 'required'],
            [['year','stage'], 'required', 'on' => 'isUser'],
            [['year','stage', 'office_id'], 'required', 'on' =>  'isAdmin'],
            [['office_id', 'section_id', 'unit_id', 'year', 'created_by', 'updated_by'], 'integer'],
            [['stage'], 'string'],
            [['date_created', 'date_updated'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'office_id' => 'Division',
            'officeName' => 'Division',
            'section_id' => 'Section',
            'sectionName' => 'Section',
            'unit_id' => 'Unit',
            'unitName' => 'Unit',
            'stage' => 'Stage',
            'year' => 'Year',
            'copy' => 'Copy From',
            'data' => 'Data',
            'created_by' => 'Created By',
            'creatorName' => 'Created By',
            'date_created' => 'Date Created',
            'updated_by' => 'Updated By',
            'updaterName' => 'Updated By',
            'date_updated' => 'Date Updated',
            'status' => 'Status',
            'statusName' => 'Status',
            'type' => 'Type',
            'cse' => 'CSE',
            'title' => 'Code'
        ];
    }

    public function validatePpmp($attribute, $params, $validator)
    {
        $model = Yii::$app->user->can('Administrator')? Ppmp::findOne(['office_id' => $this->office_id, 'year' => $this->year, 'stage' => $this->stage]) : 
        Ppmp::findOne(['office_id' => Yii::$app->user->identity->userinfo->OFFICE_C, 'year' => $this->year, 'stage' => $this->stage]);

        if($model)
        {
            $this->addError($attribute, 'This PPMP already exists');
        }
    }

    /**
     * Gets query for [[PpmpPpmpItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPpmpItems()
    {
        return $this->hasMany(PpmpItem::className(), ['ppmp_id' => 'id']);
    }

    public function getOffice()
    {
        return $this->hasOne(Office::className(), ['id' => 'office_id']);
    }

    public function getOfficeName()
    {
        return $this->office ? $this->office->abbreviation : '';
    }

    public function getSection()
    {
        return $this->hasOne(Section::className(), ['id' => 'section_id']);
    }

    public function getSectionName()
    {
        return $this->office ? $this->section->abbreviation : '';
    }

    public function getUnit()
    {
        return $this->hasOne(Unit::className(), ['id' => 'unit_id']);
    }

    public function getUnitName()
    {
        return $this->office ? $this->unit->abbreviation : '';
    }

    public function getCreator()
    {
        return $this->hasOne(UserInfo::className(), ['user_id' => 'created_by']); 
    }

    public function getCreatorName()
    {
        return $this->creator ? ucwords(strtolower($this->creator->FIRST_M.' '.$this->creator->LAST_M)) : '';
    }

    public function getUpdater()
    {
        return $this->hasOne(UserInfo::className(), ['user_id' => 'updated_by']); 
    }

    public function getUpdaterName()
    {
        return $this->updater ? ucwords(strtolower($this->updater->FIRST_M.' '.$this->updater->LAST_M)) : '';
    }

    public function getTitle()
    {
        return 'PPMP-'.$this->office->abbreviation.'-'.strtoupper($this->stage).'-'.$this->year;
    }

    public function getReference()
    {
        if($this->stage == 'Indicative')
        {
            return Appropriation::findOne(['type' => 'GAA', 'year' => $this->year -1]);
        }
        else if($this->stage == 'Adjusted')
        {
            return Appropriation::findOne(['type' => 'NEP', 'year' => $this->year]);
        }
        else if($this->stage == 'Final')
        {
            return Appropriation::findOne(['type' => 'GAA', 'year' => $this->year]);
        }
    }

    public function getTotal()
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
                    'ppmp_id' => $this->id,
                ])
                ->asArray()
                ->one();
        
        return $total['total'];
    }

    public function getOriginalTotal()
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
                    'ppmp_id' => $this->id,
                    'type' => 'Original'
                ])
                ->asArray()
                ->one();
        
        return $total['total'];
    }

    public function getSupplementalTotal()
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
                    'ppmp_id' => $this->id,
                    'type' => 'Supplemental'
                ])
                ->asArray()
                ->one();
        
        return $total['total'];
    }

    public static function pageQuantityTotal($provider, $fieldName)
    {
        $total = 0;
        foreach($provider as $item){
            $total+=$item[$fieldName];
        }
        return '<b>'.number_format($total, 2).'</b>';
    }

    public function getStatus()
    {
        return $this->hasOne(Transaction::className(), ['model_id' => 'id'])->onCondition(['model' => 'Ppmp'])->orderBy(['datetime' => SORT_DESC]);
    }

    public function getStatusName()
    {
        return $this->status ? $this->status->status : 'No status';
    }
    

    public function afterSave($insert, $changedAttributes){
        if($insert){
            {
                $status = new Transaction();
                $status->actor = Yii::$app->user->identity->userinfo->EMP_N;
                $status->model = 'Ppmp';
                $status->model_id = $this->id;
                $status->status = 'Draft';
                $status->save();

                if($this->copy != '')
                {
                    $model = Ppmp::findOne(['id' => $this->copy]);
                    if($model)
                    {
                        $connection = \Yii::$app->db;
                        $costs = ItemCost::find()
                        ->alias('c')
                        ->select([
                            'c.id',
                            'item_id',
                            'cost'
                        ])
                        ->innerJoin(['costs' => '(SELECT max(id) as id from ppmp_item_cost group by item_id)'], 'costs.id = c.id')
                        ->groupBy(['c.item_id'])
                        ->createCommand()
                        ->getRawSql();
                        
                        // update cost if not equal years : use updated cost, else use ppmp cost
                        $items = $this->year != $model->year ? PpmpItem::find()
                        ->select([
                            'ppmp_ppmp_item.id',
                            'activity_id',
                            'fund_source_id',
                            'sub_activity_id',
                            'obj_id',
                            'concat("'.$this->id.'")',
                            'ppmp_ppmp_item.item_id',
                            'costs.cost',
                            'remarks',
                            'concat("Original")'
                        ])
                        ->leftJoin(['costs' => '('.$costs.')'], 'costs.item_id = ppmp_ppmp_item.item_id')
                        ->where(['ppmp_id' => $model->id])
                        ->createCommand()
                        ->getRawSql() : PpmpItem::find()
                        ->select([
                            'ppmp_ppmp_item.id',
                            'activity_id',
                            'fund_source_id',
                            'sub_activity_id',
                            'obj_id',
                            'concat("'.$this->id.'")',
                            'ppmp_ppmp_item.item_id',
                            'ppmp_ppmp_item.cost',
                            'remarks',
                            'concat("Original")'
                        ])
                        ->where(['ppmp_id' => $model->id])
                        ->createCommand()
                        ->getRawSql(); 

                        $connection->createCommand('INSERT into ppmp_ppmp_item (source_id, activity_id, fund_source_id, sub_activity_id, obj_id, ppmp_id, item_id, cost, remarks, type) '.$items)->execute();

                        if($this->data == 2)
                        {
                            $newItems = $model->ppmpItems;

                            if($newItems)
                            {
                                foreach($newItems as $item)
                                {
                                    $newItem = PpmpItem::findOne([
                                        'ppmp_id' => $this->id,
                                        'source_id' => $item->id,
                                    ]);

                                    if($newItem)
                                    {
                                        $breakdown = $item->getItemBreakdowns()->select(['concat("'.$newItem->id.'")', 'month_id', 'quantity'])->createCommand()->getRawSql();

                                        $connection->createCommand('INSERT into ppmp_ppmp_item_breakdown (ppmp_item_id, month_id, quantity) '.$breakdown)->execute();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }
}
