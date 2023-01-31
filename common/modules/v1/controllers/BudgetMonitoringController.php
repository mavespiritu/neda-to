<?php

namespace common\modules\v1\controllers;

use Yii;
use common\modules\v1\models\Month;
use common\modules\v1\models\Pap;
use common\modules\v1\models\Obj;
use common\modules\v1\models\Appropriation;
use common\modules\v1\models\AppropriationPap;
use common\modules\v1\models\AppropriationObj;
use common\modules\v1\models\FundSource;
use common\modules\v1\models\AppropriationItem;
use common\modules\v1\models\Activity;
use common\modules\v1\models\SubActivity;
use common\modules\v1\models\Ppmp;
use common\modules\v1\models\PpmpItem;
use common\modules\v1\models\PpmpItemSearch;
use common\modules\v1\models\PpmpCondition;
use common\modules\v1\models\Item;
use common\modules\v1\models\ItemCost;
use common\modules\v1\models\ObjectItem;
use common\modules\v1\models\ItemBreakdown;
use common\modules\v1\models\PpmpSearch;
use common\modules\v1\models\Settings;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\widgets\ActiveForm;
use markavespiritu\user\models\Office;
use yii\db\Query;
use yii\helpers\Url;
use kartik\mpdf\Pdf;

class BudgetMonitoringController extends \yii\web\Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['Accounting', 'Administrator'],
                    ],
                ],
            ],
        ];
    }

    function sumSourceElement(array $elements, $key)
    {
        $sum = 0;
        foreach($elements as $element){
            $sum += isset($element['source'][$key]) ? $element['source'][$key] : 0;
            if (isset($element['children'])){
                $sum += $this->sumSourceElement($element['children'], $key);
            }
        }
        return $sum;
    }

    function sumPpmpElement(array $elements, $office, $fundSource)
    {
        $sum = 0;
        foreach($elements as $element){
            $sum += isset($element['ppmp'][$office][$fundSource]) ? $element['ppmp'][$office][$fundSource] : 0;
            if (isset($element['children'])){
                $sum += $this->sumPpmpElement($element['children'], $office, $fundSource);
            }
        }
        return $sum;
    }

    function recursive(array $elements, $parentId = null) {
        $branch = array();
        $offices = Office::find()->orderBy(['abbreviation' => SORT_ASC])->all();
        $fundSources = FundSource::find()->all();

        foreach ($elements as $element) {
            if ($element['obj_id'] == $parentId) {

                $children = $this->recursive($elements, $element['id']);

                if($fundSources)
                {
                    foreach($fundSources as $fundSource)
                    {
                        $element['source'][$fundSource->code] = isset($element['source'][$fundSource->code]) ? $element['source'][$fundSource->code] : 0;
                    }
                }

                if($offices)
                {
                    foreach($offices as $office)
                    {
                        if($fundSources)
                        {
                            foreach($fundSources as $fundSource)
                            {
                                $element['ppmp'][$office->id][$fundSource->code] = isset($element['ppmp'][$office->id][$fundSource->code]) ? $element['ppmp'][$office->id][$fundSource->code] : 0;
                            }
                        }
                    }
                }

                if ($children) {
                    $element['children'] = $children;
                }

                $branch[] = $element;
            }
        }   
    
        return $branch;
    }

    function finalRecursive(array $elements)
    {
        $branch = array();
        $offices = Office::find()->orderBy(['abbreviation' => SORT_ASC])->all();
        $fundSources = FundSource::find()->all();

        foreach ($elements as $element) {
            if(isset($element['children']))
            {
                $children = $this->finalRecursive($element['children']);

                if($fundSources)
                {
                    foreach($fundSources as $fundSource)
                    {
                        $element['source'][$fundSource->code] += $this->sumSourceElement($element['children'], $fundSource->code);
                    }
                }

                if($offices)
                {
                    foreach($offices as $office)
                    {
                        if($fundSources)
                        {
                            foreach($fundSources as $fundSource)
                            {
                                $element['ppmp'][$office->id][$fundSource->code] += $this->sumPpmpElement($element['children'], $office->id, $fundSource->code);
                            }
                        }
                    }
                }

                if ($children) {
                    $element['children'] = $children;
                }
            }
            
            $branch[] = $element;
        }
    
        return $branch;
    }

    function getAppropriationPerObjectPerFundSource($stage, $year, $obj_id, $pap_id, $fund_source_id)
    {
        $appropriation = AppropriationItem::find()
            ->select(['amount'])
            ->leftJoin('ppmp_appropriation', 'ppmp_appropriation.id = ppmp_appropriation_item.appropriation_id');

        if($stage == 'Indicative')
        {
            $appropriation = $appropriation->andWhere(['ppmp_appropriation.type' => 'GAA', 'ppmp_appropriation.year' => $year - 1]);
        }
        else if($stage == 'Adjusted')
        {
            $appropriation = $appropriation->andWhere(['ppmp_appropriation.type' => 'NEP', 'ppmp_appropriation.year' => $year]);
        }
        else if($stage == 'Final')
        {
            $appropriation = $appropriation->andWhere(['ppmp_appropriation.type' => 'GAA', 'ppmp_appropriation.year' => $year]);
        }

        if($obj_id != '')
        {
            $appropriation = $appropriation->andWhere(['ppmp_appropriation_item.obj_id' => $obj_id]);
        }

        if($pap_id != '')
        {
            $appropriation = $appropriation->andWhere(['ppmp_appropriation_item.pap_id' => $pap_id]);
        }

        if($fund_source_id != '')
        {
            $appropriation = $appropriation->andWhere(['ppmp_appropriation_item.fund_source_id' => $fund_source_id]);
        }

        $appropriation = $appropriation
                        ->asArray()
                        ->one();
        
        return isset($appropriation['amount']) ? $appropriation['amount'] : 0;
    }

    function getObjectPerFundSource($stage, $year, $activity_id, $obj_id, $office_id, $fund_source_id)
    {
        $quantities = ItemBreakdown::find()
            ->select([
                'ppmp_item_id',
                'sum(quantity) as total'
            ])
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ppmp_item_breakdown.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id');
        
        $items = PpmpItem::find()
            ->select([
                'SUM(quantities.total * cost) as total'
            ])
            ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id');

            if($year != ''){
                $quantities = $quantities->andWhere(['ppmp_ppmp.year' => $year]);
                $items = $items->andWhere(['ppmp_ppmp.year' => $year]);
            }

            if($stage != ''){
                $quantities = $quantities->andWhere(['ppmp_ppmp.stage' => $stage]);
                $items = $items->andWhere(['ppmp_ppmp.stage' => $stage]);
            }

            if($office_id != ''){
                $quantities = $quantities->andWhere(['ppmp_ppmp.office_id' => $office_id]);
                $items = $items->andWhere(['ppmp_ppmp.office_id' => $office_id]);
            }

            if($activity_id != ''){
                $quantities = $quantities->andWhere(['ppmp_ppmp_item.activity_id' => $activity_id]);
                $items = $items->andWhere(['ppmp_ppmp_item.activity_id' => $activity_id]);
            }

            if($obj_id != ''){
                $quantities = $quantities->andWhere(['ppmp_ppmp_item.obj_id' => $obj_id]);
                $items = $items->andWhere(['ppmp_ppmp_item.obj_id' => $obj_id]);
            }

            if($fund_source_id != ''){
                $quantities = $quantities->andWhere(['ppmp_ppmp_item.fund_source_id' => $fund_source_id]);
                $items = $items->andWhere(['ppmp_ppmp_item.fund_source_id' => $fund_source_id]);
            }

            $quantities = $quantities
            ->andWhere(['ppmp_ppmp_item.type' => 'Original'])
            ->groupBy(['ppmp_item_id'])
            ->createCommand()
            ->getRawSql();

            $items = $items->leftJoin(['quantities' => '('.$quantities.')'], 'quantities.ppmp_item_id = ppmp_ppmp_item.id')
                           ->asArray()
                           ->one();

            return isset($items['total']) ? $items['total'] : 0;
    }

    public function actionIndex()
    {
        $model = new AppropriationItem();
        $model->scenario = 'loadBudgetMonitoring';

        $stages = [
            'Indicative' => 'Indicative',
            'Adjusted' => 'Adjusted',
            'Final' => 'Final',
        ];

        $years = Ppmp::find()->select(['distinct(year) as year'])->asArray()->orderBy(['year' => SORT_DESC])->all();
        $years = ArrayHelper::map($years, 'year', 'year');

        $activities = Activity::find()
                ->select([
                    'ppmp_activity.id as id',
                    'ppmp_activity.pap_id as pap_id',
                    'IF(ppmp_pap.short_code IS NULL,
                        concat(
                            ppmp_cost_structure.code,"",
                            ppmp_organizational_outcome.code,"",
                            ppmp_program.code,"",
                            ppmp_sub_program.code,"",
                            ppmp_identifier.code,"",
                            ppmp_pap.code,"000-",
                            ppmp_activity.code," - ",
                            ppmp_activity.title
                        )
                        ,
                        concat(
                            ppmp_pap.short_code,"-",
                            ppmp_activity.code," - ",
                            ppmp_activity.title
                        )
                    ) as text',
                    'p.title as groupTitle'
                ])
            ->leftJoin(['p' => '(
                SELECT 
                ppmp_pap.id as id, 
                ppmp_pap.code as code, 
                IF(ppmp_pap.short_code IS NULL,
                        concat(
                            ppmp_cost_structure.code,"",
                            ppmp_organizational_outcome.code,"",
                            ppmp_program.code,"",
                            ppmp_sub_program.code,"",
                            ppmp_identifier.code,"",
                            ppmp_pap.code,"000 - ",
                            ppmp_pap.title
                        )
                        ,
                        concat(
                            ppmp_pap.short_code," - ",
                            ppmp_pap.title
                        )
                    ) as title
                from 
                ppmp_pap
                left join ppmp_identifier on ppmp_identifier.id = ppmp_pap.identifier_id
                left join ppmp_sub_program on ppmp_sub_program.id = ppmp_pap.sub_program_id
                left join ppmp_program on ppmp_program.id = ppmp_pap.program_id
                left join ppmp_organizational_outcome on ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id
                left join ppmp_cost_structure on ppmp_cost_structure.id = ppmp_pap.cost_structure_id
            )'], 'p.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->asArray()
            ->all();

        $activities = ArrayHelper::map($activities, 'id', 'text', 'groupTitle');

        if($model->load(Yii::$app->request->post()))
        {
            $activity = Activity::findOne($model->activity_id);

            $temp = [];

            $appropriationItems = [];

            $objects = Obj::find()->asArray()->all();

            $appropriation = Appropriation::find();
            $prevAppropriation = Appropriation::find();

            if($model->stage == 'Indicative')
            {
                $appropriation = $appropriation->andWhere(['type' => 'GAA', 'year' => $model->year - 1]);
            }
            else if($model->stage == 'Adjusted')
            {
                $appropriation = $appropriation->andWhere(['type' => 'NEP', 'year' => $model->year]);
            }
            else if($model->stage == 'Final')
            {
                $appropriation = $appropriation->andWhere(['type' => 'GAA', 'year' => $model->year]);
            }
    
            $appropriation = $appropriation->one();

            if($appropriation)
            {
                $appropriationItems = AppropriationItem::find()
                ->select([
                    'ppmp_appropriation_item.obj_id',
                    'ppmp_appropriation_item.fund_source_id',
                    'ppmp_fund_source.code as fundSource',
                    'COALESCE(SUM(amount), 0) as total'
                ])
                ->leftJoin('ppmp_fund_source', 'ppmp_fund_source.id = ppmp_appropriation_item.fund_source_id');
                
                $appropriationItems = $appropriationItems->andWhere(['ppmp_appropriation_item.pap_id' => $activity->pap_id]);
    
                $appropriationItems = $appropriationItems
                ->andWhere(['ppmp_appropriation_item.appropriation_id' => $appropriation->id])
                ->groupBy([
                    'ppmp_appropriation_item.obj_id',
                    'ppmp_appropriation_item.fund_source_id'
                ])
                ->asArray()
                ->all();
            }

            if(!empty($appropriationItems))
            {
                foreach($appropriationItems as $item)
                {
                    $temp[$item['obj_id']]['source'][$item['fundSource']] = $item['total'];
                }
            }

            if(!empty($objects))
            {
                foreach($objects as $idx => $object)
                {
                    $objects[$idx]['source'] = isset($temp[$object['id']]['source']) ? $temp[$object['id']]['source'] : [];
                }
            }

            $quantities = ItemBreakdown::find()
            ->select([
                'ppmp_item_id',
                'sum(quantity) as total'
            ])
            ->groupBy(['ppmp_item_id'])
            ->createCommand()
            ->getRawSql();
        
            $items = PpmpItem::find()
            ->select([
                'ppmp_ppmp.office_id',
                'ppmp_ppmp_item.fund_source_id',
                'ppmp_fund_source.code as fundSource',
                'ppmp_ppmp_item.obj_id',
                'SUM(quantities.total * ppmp_ppmp_item.cost) as total'
            ])
            ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_fund_source', 'ppmp_fund_source.id = ppmp_ppmp_item.fund_source_id')
            ->leftJoin('ppmp_obj', 'ppmp_obj.id = ppmp_ppmp_item.obj_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->leftJoin(['quantities' => '('.$quantities.')'], 'quantities.ppmp_item_id = ppmp_ppmp_item.id');

            if($model->year != ''){
                $items = $items->andWhere(['ppmp_ppmp.year' => $model->year]);
            }

            if($model->stage != ''){
                $items = $items->andWhere(['ppmp_ppmp.stage' => $model->stage]);
            }

            if($model->activity_id != ''){
                $items = $items->andWhere(['ppmp_ppmp_item.activity_id' => $model->activity_id]);
            }

            $items = $items
            ->groupBy([
                'ppmp_ppmp.office_id',
                'ppmp_ppmp_item.fund_source_id',
                'ppmp_ppmp_item.obj_id',
            ])
            ->asArray()
            ->all();

            if(!empty($items))
            {
                foreach($items as $item)
                {
                    $temp[$item['obj_id']]['ppmp'][$item['office_id']][$item['fundSource']] = $item['total'];
                }
            }

            if(!empty($objects))
            {
                foreach($objects as $idx => $object)
                {
                    $objects[$idx]['ppmp'] = isset($temp[$object['id']]['ppmp']) ? $temp[$object['id']]['ppmp'] : [];
                }
            }

            $data = $this->finalRecursive($this->recursive($objects, null));

            $total = [];

            if(!empty($data))
            {
                foreach($data as $datum)
                {
                    if(isset($datum['source']))
                    {
                        foreach($datum['source'] as $fundSource => $source)
                        {
                            $total['source'][$fundSource] = 0;
                        }
                    }

                    if(isset($datum['ppmp']))
                    {
                        foreach($datum['ppmp'] as $office => $ppmp)
                        {
                            if(!empty($ppmp))
                            {
                                foreach($ppmp as $fundSource => $value)
                                {
                                    $total['ppmp'][$office][$fundSource] = 0;
                                }
                            }
                        }
                    }
                }
            }

            if(!empty($data))
            {
                foreach($data as $datum)
                {
                    if(isset($datum['source']))
                    {
                        foreach($datum['source'] as $fundSource => $source)
                        {
                            $total['source'][$fundSource] += isset($datum['source'][$fundSource]) ? $datum['source'][$fundSource] : 0;
                        }
                    }

                    if(isset($datum['ppmp']))
                    {
                        foreach($datum['ppmp'] as $office => $ppmp)
                        {
                            if(!empty($ppmp))
                            {
                                foreach($ppmp as $fundSource => $value)
                                {
                                    $total['ppmp'][$office][$fundSource] += isset($datum['ppmp'][$office][$fundSource]) ? $datum['ppmp'][$office][$fundSource] : 0;
                                }
                            }
                        }
                    }
                }
            }

            $fundSources = FundSource::find()->all();
            $offices = Office::find()->orderBy(['abbreviation' => SORT_ASC])->all();
            $postData = Yii::$app->request->post('AppropriationItem');
            
            return $this->renderAjax('view',[
                'data' => $data,
                'total' => $total,
                'activity' => $activity,
                'fundSources' => $fundSources,
                'offices' => $offices,
                'year' => $model->year,
                'stage' => $model->stage,
                'postData' => $postData,
            ]);
        }

        return $this->render('index',[
            'model' => $model,
            'stages' => $stages,
            'activities' => $activities,
            'years' => $years,
        ]);
    }

    public function actionViewItems($office_id, $fund_source_id, $activity_id, $stage, $year, $obj_id)
    {
        $office = Office::findOne($office_id);
        $fundSource = FundSource::findOne($fund_source_id);
        $activity = Activity::findOne($activity_id);
        $object = Obj::findOne($obj_id);

        $quantities = ItemBreakdown::find()
            ->select([
                'ppmp_item_id',
                'sum(quantity) as total'
            ])
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ppmp_item_breakdown.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id');
        
        $items = PpmpItem::find()
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
                        ppmp_sub_activity.code," - ",
                        ppmp_sub_activity.title
                    )
                    ,
                    concat(
                        ppmp_pap.short_code,"-",
                        ppmp_activity.code,"-",
                        ppmp_sub_activity.code," - ",
                        ppmp_sub_activity.title
                    )
                ) as pap',
                'ppmp_item.title',
                'ppmp_item.unit_of_measure',
                'ppmp_ppmp_item.cost as cost_per_unit',
                'quantities.total as quantity',
                'ppmp_ppmp_item.remarks'
            ])
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_sub_activity.activity_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id');

            if($year != ''){
                $quantities = $quantities->andWhere(['ppmp_ppmp.year' => $year]);
                $items = $items->andWhere(['ppmp_ppmp.year' => $year]);
            }

            if($stage != ''){
                $quantities = $quantities->andWhere(['ppmp_ppmp.stage' => $stage]);
                $items = $items->andWhere(['ppmp_ppmp.stage' => $stage]);
            }

            if($office_id != ''){
                $quantities = $quantities->andWhere(['ppmp_ppmp.office_id' => $office_id]);
                $items = $items->andWhere(['ppmp_ppmp.office_id' => $office_id]);
            }

            if($activity_id != ''){
                $quantities = $quantities->andWhere(['ppmp_ppmp_item.activity_id' => $activity_id]);
                $items = $items->andWhere(['ppmp_ppmp_item.activity_id' => $activity_id]);
            }

            if($obj_id != ''){
                $quantities = $quantities->andWhere(['ppmp_ppmp_item.obj_id' => $obj_id]);
                $items = $items->andWhere(['ppmp_ppmp_item.obj_id' => $obj_id]);
            }

            if($fund_source_id != ''){
                $quantities = $quantities->andWhere(['ppmp_ppmp_item.fund_source_id' => $fund_source_id]);
                $items = $items->andWhere(['ppmp_ppmp_item.fund_source_id' => $fund_source_id]);
            }

            $quantities = $quantities
            ->groupBy(['ppmp_item_id'])
            ->createCommand()
            ->getRawSql();

            $items = $items->leftJoin(['quantities' => '('.$quantities.')'], 'quantities.ppmp_item_id = ppmp_ppmp_item.id')
                           ->orderBy(['ppmp_sub_activity.title' => SORT_ASC])
                           ->asArray()
                           ->all();
                    
            return $this->renderAjax('items',[
                'items' => $items,
                'office' => $office,
                'fundSource' => $fundSource,
                'activity' => $activity,
                'object' => $object,
                'stage' => $stage,
                'year' => $year,
            ]);
    }

    public function actionDownload($type, $post)
    {
        $postData = json_decode($post, true);

        $activity = Activity::findOne($postData['activity_id']);

        $temp = [];

        $appropriationItems = [];

        $objects = Obj::find()->asArray()->all();

        $appropriation = Appropriation::find();
        $prevAppropriation = Appropriation::find();

        if($postData['stage'] == 'Indicative')
        {
            $appropriation = $appropriation->andWhere(['type' => 'GAA', 'year' => $postData['year'] - 1]);
        }
        else if($postData['stage'] == 'Adjusted')
        {
            $appropriation = $appropriation->andWhere(['type' => 'NEP', 'year' =>$postData['year']]);
        }
        else if($postData['stage'] == 'Final')
        {
            $appropriation = $appropriation->andWhere(['type' => 'GAA', 'year' => $postData['year']]);
        }

        $appropriation = $appropriation->one();

        if($appropriation)
        {
            $appropriationItems = AppropriationItem::find()
            ->select([
                'ppmp_appropriation_item.obj_id',
                'ppmp_appropriation_item.fund_source_id',
                'ppmp_fund_source.code as fundSource',
                'COALESCE(SUM(amount), 0) as total'
            ])
            ->leftJoin('ppmp_fund_source', 'ppmp_fund_source.id = ppmp_appropriation_item.fund_source_id');
            
            $appropriationItems = $appropriationItems->andWhere(['ppmp_appropriation_item.pap_id' => $activity->pap_id]);

            $appropriationItems = $appropriationItems
            ->andWhere(['ppmp_appropriation_item.appropriation_id' => $appropriation->id])
            ->groupBy([
                'ppmp_appropriation_item.obj_id',
                'ppmp_appropriation_item.fund_source_id'
            ])
            ->asArray()
            ->all();
        }

        if(!empty($appropriationItems))
        {
            foreach($appropriationItems as $item)
            {
                $temp[$item['obj_id']]['source'][$item['fundSource']] = $item['total'];
            }
        }

        if(!empty($objects))
        {
            foreach($objects as $idx => $object)
            {
                $objects[$idx]['source'] = isset($temp[$object['id']]['source']) ? $temp[$object['id']]['source'] : [];
            }
        }

        $quantities = ItemBreakdown::find()
        ->select([
            'ppmp_item_id',
            'sum(quantity) as total'
        ])
        ->groupBy(['ppmp_item_id'])
        ->createCommand()
        ->getRawSql();
    
        $items = PpmpItem::find()
        ->select([
            'ppmp_ppmp.office_id',
            'ppmp_ppmp_item.fund_source_id',
            'ppmp_fund_source.code as fundSource',
            'ppmp_ppmp_item.obj_id',
            'SUM(quantities.total * ppmp_ppmp_item.cost) as total'
        ])
        ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id')
        ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
        ->leftJoin('ppmp_fund_source', 'ppmp_fund_source.id = ppmp_ppmp_item.fund_source_id')
        ->leftJoin('ppmp_obj', 'ppmp_obj.id = ppmp_ppmp_item.obj_id')
        ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
        ->leftJoin(['quantities' => '('.$quantities.')'], 'quantities.ppmp_item_id = ppmp_ppmp_item.id');

        if($postData['year'] != ''){
            $items = $items->andWhere(['ppmp_ppmp.year' => $postData['year']]);
        }

        if($postData['stage'] != ''){
            $items = $items->andWhere(['ppmp_ppmp.stage' => $postData['stage']]);
        }

        if($postData['activity_id'] != ''){
            $items = $items->andWhere(['ppmp_ppmp_item.activity_id' => $postData['activity_id']]);
        }

        $items = $items
        ->groupBy([
            'ppmp_ppmp.office_id',
            'ppmp_ppmp_item.fund_source_id',
            'ppmp_ppmp_item.obj_id',
        ])
        ->asArray()
        ->all();

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $temp[$item['obj_id']]['ppmp'][$item['office_id']][$item['fundSource']] = $item['total'];
            }
        }

        if(!empty($objects))
        {
            foreach($objects as $idx => $object)
            {
                $objects[$idx]['ppmp'] = isset($temp[$object['id']]['ppmp']) ? $temp[$object['id']]['ppmp'] : [];
            }
        }

        $data = $this->finalRecursive($this->recursive($objects, null));

        $total = [];

        if(!empty($data))
        {
            foreach($data as $datum)
            {
                if(isset($datum['source']))
                {
                    foreach($datum['source'] as $fundSource => $source)
                    {
                        $total['source'][$fundSource] = 0;
                    }
                }

                if(isset($datum['ppmp']))
                {
                    foreach($datum['ppmp'] as $office => $ppmp)
                    {
                        if(!empty($ppmp))
                        {
                            foreach($ppmp as $fundSource => $value)
                            {
                                $total['ppmp'][$office][$fundSource] = 0;
                            }
                        }
                    }
                }
            }
        }

        if(!empty($data))
        {
            foreach($data as $datum)
            {
                if(isset($datum['source']))
                {
                    foreach($datum['source'] as $fundSource => $source)
                    {
                        $total['source'][$fundSource] += isset($datum['source'][$fundSource]) ? $datum['source'][$fundSource] : 0;
                    }
                }

                if(isset($datum['ppmp']))
                {
                    foreach($datum['ppmp'] as $office => $ppmp)
                    {
                        if(!empty($ppmp))
                        {
                            foreach($ppmp as $fundSource => $value)
                            {
                                $total['ppmp'][$office][$fundSource] += isset($datum['ppmp'][$office][$fundSource]) ? $datum['ppmp'][$office][$fundSource] : 0;
                            }
                        }
                    }
                }
            }
        }

        $fundSources = FundSource::find()->all();
        $offices = Office::find()->orderBy(['abbreviation' => SORT_ASC])->all();

        $filename = 'Budget Monitoring';

        if($type == 'excel')
        {
            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=".$filename.".xls");
            return $this->renderPartial('file', [
                'data' => $data,
                'total' => $total,
                'activity' => $activity,
                'fundSources' => $fundSources,
                'offices' => $offices,
                'year' => $postData['year'],
                'stage' =>$postData['stage'],
                'type' => $type,
            ]);
        }else if($type == 'pdf')
        {
            $content = $this->renderPartial('file', [
                'data' => $data,
                'total' => $total,
                'activity' => $activity,
                'fundSources' => $fundSources,
                'offices' => $offices,
                'year' => $postData['year'],
                'stage' => $postData['stage'],
                'type' => $type,
            ]);

            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'format' => Pdf::FORMAT_LEGAL, 
                'orientation' => Pdf::ORIENT_LANDSCAPE, 
                'destination' => Pdf::DEST_DOWNLOAD, 
                'filename' => $filename.'.pdf', 
                'content' => $content,  
                'marginLeft' => 11.4,
                'marginRight' => 11.4,
                'cssInline' => 'table{
                                    border-collapse: collapse;
                                }
                                thead{
                                    font-size: 12px;
                                    text-align: center;
                                }
                            
                                td{
                                    font-size: 12px;
                                    border: 1px solid black;
                                }
                            
                                th{
                                    text-align: center;
                                    border: 1px solid black;
                                }', 
                ]);
        
                $response = Yii::$app->response;
                $response->format = \yii\web\Response::FORMAT_RAW;
                $headers = Yii::$app->response->headers;
                $headers->add('Content-Type', 'application/pdf');
                return $pdf->render();
        }
    }
}
