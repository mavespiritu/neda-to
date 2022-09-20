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

class AppropriationController extends \yii\web\Controller
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
                        'roles' => ['AccountingStaff', 'Administrator'],
                    ],
                ],
            ],
        ];
    }

    function sumPrevElement(array $elements)
    {
        $sum = 0;
        foreach($elements as $element){
            $sum += isset($element['prevSource']) ? $element['prevSource'] : 0;
            if (isset($element['children'])){
                $sum += $this->sumPrevElement($element['children']);
            }
        }
        return $sum;
    }

    function sumElement(array $elements, $type, $key, $fundSource)
    {
        $sum = 0;
        foreach($elements as $element){
            $sum += isset($element[$type][$key][$fundSource]) ? $element[$type][$key][$fundSource] : 0;
            if (isset($element['children'])){
                $sum += $this->sumElement($element['children'], $type, $key, $fundSource);
            }
        }
        return $sum;
    }

    function recursive(array $elements, $parentId = null, $paps, $fundSources, $prevAppropriation) {
        $branch = array();

        foreach ($elements as $element) {
            if ($element['obj_id'] == $parentId) {
                
                $children = $this->recursive($elements, $element['id'], $paps, $fundSources, $prevAppropriation);

                if($prevAppropriation)
                {
                    $element['prevSource'] = isset($element['prevSource']) ? $element['prevSource'] : 0;
                }
                
                if($paps)
                {
                    foreach($paps as $pap)
                    {
                        if($fundSources)
                        {
                            foreach($fundSources as $fundSource)
                            {
                                $element['source'][$pap->id][$fundSource->code] = isset($element['source'][$pap->id][$fundSource->code]) ? $element['source'][$pap->id][$fundSource->code] : 0;
                                $element['ppmp'][$pap->id][$fundSource->code] = isset($element['ppmp'][$pap->id][$fundSource->code]) ? $element['ppmp'][$pap->id][$fundSource->code] : 0;
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

    function finalRecursive(array $elements, $paps, $fundSources, $prevAppropriation)
    {
        $branch = array();

        foreach ($elements as $element) {
            if(isset($element['children']))
            {
                $children = $this->finalRecursive($element['children'], $paps, $fundSources, $prevAppropriation);

                if($prevAppropriation)
                {
                    $element['prevSource'] += $this->sumPrevElement($element['children']);
                }

                if($paps)
                {
                    foreach($paps as $pap)
                    {
                        if($fundSources)
                        {
                            foreach($fundSources as $fundSource)
                            {
                                $element['source'][$pap->id][$fundSource->code] += $this->sumElement($element['children'], 'source', $pap->id, $fundSource->code);
                                $element['ppmp'][$pap->id][$fundSource->code] += $this->sumElement($element['children'], 'ppmp', $pap->id, $fundSource->code);
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

    public function actionIndex()
    {
        $model = new AppropriationItem();
        $model->scenario = 'loadAppropriation';

        $stages = [
            'Indicative' => 'Indicative',
            'Adjusted' => 'Adjusted',
            'Final' => 'Final',
        ];

        $years = Ppmp::find()->select(['distinct(year) as year'])->asArray()->orderBy(['year' => SORT_DESC])->all();
        $years = ArrayHelper::map($years, 'year', 'year');

        if($model->load(Yii::$app->request->post()))
        {
            $temp = [];

            $appropriationItems = [];

            $prevAppropriationItems = [];

            $headers = [];

            $objects = Obj::find()->asArray()->all();

            $appropriation = Appropriation::find();
            $prevAppropriation = Appropriation::find();

            $appPaps = [];

            if($model->stage == 'Indicative')
            {
                $appropriation = $appropriation->andWhere(['type' => 'GAA', 'year' => $model->year - 1]);
                $prevAppropriation = $prevAppropriation->andWhere(['type' => 'NEP', 'year' => $model->year - 1]);
            }
            else if($model->stage == 'Adjusted')
            {
                $appropriation = $appropriation->andWhere(['type' => 'NEP', 'year' => $model->year]);
                $prevAppropriation = $prevAppropriation->andWhere(['type' => 'GAA', 'year' => $model->year - 1]);
            }
            else if($model->stage == 'Final')
            {
                $appropriation = $appropriation->andWhere(['type' => 'GAA', 'year' => $model->year]);
                $prevAppropriation = $prevAppropriation->andWhere(['type' => 'NEP', 'year' => $model->year]);
            }
    
            $appropriation = $appropriation->one();
            $prevAppropriation = $prevAppropriation->one();
    
            if($appropriation)
            {
                $appropriationItems = AppropriationItem::find()
                ->select([
                    'ppmp_appropriation_item.obj_id',
                    'ppmp_appropriation_item.pap_id',
                    'ppmp_appropriation_item.fund_source_id',
                    'ppmp_fund_source.code as fundSource',
                    'COALESCE(amount, 0) as total'
                ])
                ->leftJoin('ppmp_fund_source', 'ppmp_fund_source.id = ppmp_appropriation_item.fund_source_id');
    
                $appropriationItems = $appropriationItems
                ->andWhere(['ppmp_appropriation_item.appropriation_id' => $appropriation->id])
                ->groupBy([
                    'ppmp_appropriation_item.obj_id',
                    'ppmp_appropriation_item.pap_id',
                    'ppmp_appropriation_item.fund_source_id'
                ])
                ->asArray()
                ->all();
                
                if($appropriation->appropriationPaps)
                {
                    foreach($appropriation->getAppropriationPaps()->orderBy(['arrangement' => SORT_ASC])->all() as $pap)
                    {
                        $headers[$pap->pap->codeAndTitle][] = $pap->fundSource->code;
                        $headers[$pap->pap->codeAndTitle][] = 'PPMP';
                    }
                }

                $appPaps = AppropriationPap::find()->select(['distinct(pap_id) as pap_id'])->where(['appropriation_id' => $appropriation->id])->asArray()->all();
                $appPaps = ArrayHelper::map($appPaps, 'pap_id', 'pap_id');
            }

    
            if($prevAppropriation)
            {
                $prevAppropriationItems = AppropriationItem::find()
                ->select([
                    'ppmp_appropriation_item.obj_id',
                    'COALESCE(SUM(amount), 0) as total'
                ])
                ->leftJoin('ppmp_fund_source', 'ppmp_fund_source.id = ppmp_appropriation_item.fund_source_id');
    
                $prevAppropriationItems = $prevAppropriationItems
                ->andWhere(['ppmp_appropriation_item.appropriation_id' => $prevAppropriation->id])
                ->groupBy([
                    'ppmp_appropriation_item.obj_id',
                ])
                ->asArray()
                ->all();
            }
    
            if(!empty($appropriationItems))
            {
                foreach($appropriationItems as $item)
                {
                    $temp[$item['obj_id']]['source'][$item['pap_id']][$item['fundSource']] = $item['total'];
                }
            }
    
            if(!empty($prevAppropriationItems))
            {
                foreach($prevAppropriationItems as $item)
                {
                    $temp[$item['obj_id']]['prevSource'] = $item['total'];
                }
            }
    
            if(!empty($appropriationItems))
            {
                foreach($appropriationItems as $item)
                {
                    $temp[$item['obj_id']]['source'][$item['pap_id']][$item['fundSource']] = $item['total'];
                }
            }
    
            if(!empty($objects))
            {
                foreach($objects as $idx => $object)
                {
                    $objects[$idx]['source'] = isset($temp[$object['id']]['source']) ? $temp[$object['id']]['source'] : [];
                    $objects[$idx]['prevSource'] = isset($temp[$object['id']]['prevSource']) ? $temp[$object['id']]['prevSource'] : 0;
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
                'ppmp_activity.pap_id',
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

            $ppmpPaps = PpmpItem::find()
            ->select([
                'distinct(ppmp_pap.id) as pap_id'
            ])
            ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id');

            if($model->year != ''){
                $items = $items->andWhere(['ppmp_ppmp.year' => $model->year]);
                $ppmpPaps = $ppmpPaps->andWhere(['ppmp_ppmp.year' => $model->year]);
            }

            if($model->stage != ''){
                $items = $items->andWhere(['ppmp_ppmp.stage' => $model->stage]);
                $ppmpPaps = $ppmpPaps->andWhere(['ppmp_ppmp.stage' => $model->stage]);
            }

            $items = $items->andWhere(['ppmp_ppmp_item.type' => 'Original']);
            $ppmpPaps = $ppmpPaps->andWhere(['ppmp_ppmp_item.type' => 'Original']);

            $items = $items
            ->groupBy([
                'ppmp_activity.pap_id',
                'ppmp_ppmp_item.fund_source_id',
                'ppmp_ppmp_item.obj_id',
            ])
            ->asArray()
            ->all();

            $ppmpPaps = $ppmpPaps
            ->asArray()
            ->all();

            $ppmpPaps = ArrayHelper::map($ppmpPaps, 'pap_id', 'pap_id');

            if(!empty($items))
            {
                foreach($items as $item)
                {
                    $temp[$item['obj_id']]['ppmp'][$item['pap_id']][$item['fundSource']] = $item['total'];
                }
            }

            if(!empty($objects))
            {
                foreach($objects as $idx => $object)
                {
                    $objects[$idx]['ppmp'] = isset($temp[$object['id']]['ppmp']) ? $temp[$object['id']]['ppmp'] : [];
                }
            }

            $paps = Pap::find()->where(['in', 'id', array_unique(array_merge($appPaps, $ppmpPaps))])->orderBy(['id' => SORT_ASC])->all();

            $fundSources = FundSource::find()->all();

            $data = $this->finalRecursive($this->recursive($objects, null, $paps, $fundSources, $prevAppropriation), $paps, $fundSources, $prevAppropriation);

            $total = [];
            $total['prevSource'] = 0;
            
            if(!empty($data))
            {
                foreach($data as $datum)
                {
                    if(isset($datum['source']))
                    {
                        foreach($datum['source'] as $idx => $source)
                        {
                            foreach($source as $fundSource => $value)
                            {
                                $total['source'][$idx][$fundSource] = 0;
                            }
                        }
                    }

                    if(isset($datum['ppmp']))
                    {
                        foreach($datum['ppmp'] as $idx => $source)
                        {
                            foreach($source as $fundSource => $value)
                            {
                                $total['ppmp'][$idx][$fundSource] = 0;
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
                        foreach($datum['source'] as $idx => $source)
                        {
                            foreach($source as $fundSource => $value)
                            {
                                $total['source'][$idx][$fundSource] += $value;
                            }
                        }
                    }

                    if(isset($datum['ppmp']))
                    {
                        foreach($datum['ppmp'] as $idx => $ppmp)
                        {
                            foreach($ppmp as $fundSource => $value)
                            {
                                $total['ppmp'][$idx][$fundSource] += $value;
                            }
                        }
                    }

                    $total['prevSource'] += isset($datum['prevSource']) ? $datum['prevSource'] : 0;
                }
            }
            
            $postData = Yii::$app->request->post('AppropriationItem');
            
            return $this->renderAjax('view',[
                'headers' => $headers,
                'data' => $data,
                'total' => $total,
                'paps' => $paps,
                'fundSources' => $fundSources,
                'appropriation' => $appropriation,
                'prevAppropriation' => $prevAppropriation,
                'year' => $model->year,
                'stage' => $model->stage,
                'postData' => $postData,
            ]);
        }

        return $this->render('index',[
            'model' => $model,
            'stages' => $stages,
            'years' => $years,
        ]);
    }

    /* public function actionViewItems($office_id, $fund_source_id, $activity_id, $stage, $year, $obj_id)
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
                'ppmp_sub_activity.title as pap',
                'ppmp_item.title',
                'ppmp_item.unit_of_measure',
                'ppmp_ppmp_item.cost as cost_per_unit',
                'quantities.total as quantity',
                'ppmp_ppmp_item.remarks'
            ])
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
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
            ->andWhere(['ppmp_ppmp_item.type' => 'Original'])
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
    } */

    public function actionDownload($type, $post)
    {
        $postData = json_decode($post, true);

        $temp = [];

        $appropriationItems = [];

        $prevAppropriationItems = [];

        $headers = [];

        $objects = Obj::find()->asArray()->all();

        $appropriation = Appropriation::find();
        $prevAppropriation = Appropriation::find();

        $appPaps = [];

        if($postData['stage'] == 'Indicative')
        {
            $appropriation = $appropriation->andWhere(['type' => 'GAA', 'year' => $postData['year'] - 1]);
            $prevAppropriation = $prevAppropriation->andWhere(['type' => 'NEP', 'year' => $postData['year'] - 1]);
        }
        else if($postData['stage'] == 'Adjusted')
        {
            $appropriation = $appropriation->andWhere(['type' => 'NEP', 'year' => $postData['year']]);
            $prevAppropriation = $prevAppropriation->andWhere(['type' => 'GAA', 'year' => $postData['year'] - 1]);
        }
        else if($postData['stage'] == 'Final')
        {
            $appropriation = $appropriation->andWhere(['type' => 'GAA', 'year' => $postData['year']]);
            $prevAppropriation = $prevAppropriation->andWhere(['type' => 'NEP', 'year' => $postData['year']]);
        }

        $appropriation = $appropriation->one();
        $prevAppropriation = $prevAppropriation->one();

        if($appropriation)
        {
            $appropriationItems = AppropriationItem::find()
            ->select([
                'ppmp_appropriation_item.obj_id',
                'ppmp_appropriation_item.pap_id',
                'ppmp_appropriation_item.fund_source_id',
                'ppmp_fund_source.code as fundSource',
                'COALESCE(amount, 0) as total'
            ])
            ->leftJoin('ppmp_fund_source', 'ppmp_fund_source.id = ppmp_appropriation_item.fund_source_id');

            $appropriationItems = $appropriationItems
            ->andWhere(['ppmp_appropriation_item.appropriation_id' => $appropriation->id])
            ->groupBy([
                'ppmp_appropriation_item.obj_id',
                'ppmp_appropriation_item.pap_id',
                'ppmp_appropriation_item.fund_source_id'
            ])
            ->asArray()
            ->all();
            
            if($appropriation->appropriationPaps)
            {
                foreach($appropriation->getAppropriationPaps()->orderBy(['arrangement' => SORT_ASC])->all() as $pap)
                {
                    $headers[$pap->pap->codeAndTitle][] = $pap->fundSource->code;
                    $headers[$pap->pap->codeAndTitle][] = 'PPMP';
                }
            }

            $appPaps = AppropriationPap::find()->select(['distinct(pap_id) as pap_id'])->where(['appropriation_id' => $appropriation->id])->asArray()->all();
            $appPaps = ArrayHelper::map($appPaps, 'pap_id', 'pap_id');
        }


        if($prevAppropriation)
        {
            $prevAppropriationItems = AppropriationItem::find()
            ->select([
                'ppmp_appropriation_item.obj_id',
                'COALESCE(SUM(amount), 0) as total'
            ])
            ->leftJoin('ppmp_fund_source', 'ppmp_fund_source.id = ppmp_appropriation_item.fund_source_id');

            $prevAppropriationItems = $prevAppropriationItems
            ->andWhere(['ppmp_appropriation_item.appropriation_id' => $prevAppropriation->id])
            ->groupBy([
                'ppmp_appropriation_item.obj_id',
            ])
            ->asArray()
            ->all();
        }

        if(!empty($appropriationItems))
        {
            foreach($appropriationItems as $item)
            {
                $temp[$item['obj_id']]['source'][$item['pap_id']][$item['fundSource']] = $item['total'];
            }
        }

        if(!empty($prevAppropriationItems))
        {
            foreach($prevAppropriationItems as $item)
            {
                $temp[$item['obj_id']]['prevSource'] = $item['total'];
            }
        }

        if(!empty($appropriationItems))
        {
            foreach($appropriationItems as $item)
            {
                $temp[$item['obj_id']]['source'][$item['pap_id']][$item['fundSource']] = $item['total'];
            }
        }

        if(!empty($objects))
        {
            foreach($objects as $idx => $object)
            {
                $objects[$idx]['source'] = isset($temp[$object['id']]['source']) ? $temp[$object['id']]['source'] : [];
                $objects[$idx]['prevSource'] = isset($temp[$object['id']]['prevSource']) ? $temp[$object['id']]['prevSource'] : 0;
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
            'ppmp_activity.pap_id',
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

        $ppmpPaps = PpmpItem::find()
        ->select([
            'distinct(ppmp_pap.id) as pap_id'
        ])
        ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id')
        ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
        ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id');

        if($postData['year'] != ''){
            $items = $items->andWhere(['ppmp_ppmp.year' => $postData['year']]);
            $ppmpPaps = $ppmpPaps->andWhere(['ppmp_ppmp.year' => $postData['year']]);
        }

        if($postData['stage'] != ''){
            $items = $items->andWhere(['ppmp_ppmp.stage' => $postData['stage']]);
            $ppmpPaps = $ppmpPaps->andWhere(['ppmp_ppmp.stage' => $postData['stage']]);
        }

        $items = $items->andWhere(['ppmp_ppmp_item.type' => 'Original']);
        $ppmpPaps = $ppmpPaps->andWhere(['ppmp_ppmp_item.type' => 'Original']);

        $items = $items
        ->groupBy([
            'ppmp_activity.pap_id',
            'ppmp_ppmp_item.fund_source_id',
            'ppmp_ppmp_item.obj_id',
        ])
        ->asArray()
        ->all();

        $ppmpPaps = $ppmpPaps
        ->asArray()
        ->all();

        $ppmpPaps = ArrayHelper::map($ppmpPaps, 'pap_id', 'pap_id');

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $temp[$item['obj_id']]['ppmp'][$item['pap_id']][$item['fundSource']] = $item['total'];
            }
        }

        if(!empty($objects))
        {
            foreach($objects as $idx => $object)
            {
                $objects[$idx]['ppmp'] = isset($temp[$object['id']]['ppmp']) ? $temp[$object['id']]['ppmp'] : [];
            }
        }

        $paps = Pap::find()->where(['in', 'id', array_unique(array_merge($appPaps, $ppmpPaps))])->orderBy(['id' => SORT_ASC])->all();

        $fundSources = FundSource::find()->all();

        $data = $this->finalRecursive($this->recursive($objects, null, $paps, $fundSources, $prevAppropriation), $paps, $fundSources, $prevAppropriation);

        $total = [];
        $total['prevSource'] = 0;
        
        if(!empty($data))
        {
            foreach($data as $datum)
            {
                if(isset($datum['source']))
                {
                    foreach($datum['source'] as $idx => $source)
                    {
                        foreach($source as $fundSource => $value)
                        {
                            $total['source'][$idx][$fundSource] = 0;
                        }
                    }
                }

                if(isset($datum['ppmp']))
                {
                    foreach($datum['ppmp'] as $idx => $source)
                    {
                        foreach($source as $fundSource => $value)
                        {
                            $total['ppmp'][$idx][$fundSource] = 0;
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
                    foreach($datum['source'] as $idx => $source)
                    {
                        foreach($source as $fundSource => $value)
                        {
                            $total['source'][$idx][$fundSource] += $value;
                        }
                    }
                }

                if(isset($datum['ppmp']))
                {
                    foreach($datum['ppmp'] as $idx => $ppmp)
                    {
                        foreach($ppmp as $fundSource => $value)
                        {
                            $total['ppmp'][$idx][$fundSource] += $value;
                        }
                    }
                }

                $total['prevSource'] += isset($datum['prevSource']) ? $datum['prevSource'] : 0;
            }
        }
            
        $filename = 'Appropriation Monitoring';

        if($type == 'excel')
        {
            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=".$filename.".xls");
            return $this->renderPartial('file', [
                'headers' => $headers,
                'data' => $data,
                'total' => $total,
                'paps' => $paps,
                'fundSources' => $fundSources,
                'appropriation' => $appropriation,
                'prevAppropriation' => $prevAppropriation,
                'year' => $postData['year'],
                'stage' => $postData['stage'],
                'postData' => $postData,
                'type' => $type,
            ]);
        }else if($type == 'pdf')
        {
            $content = $this->renderPartial('file', [
                'headers' => $headers,
                'data' => $data,
                'total' => $total,
                'paps' => $paps,
                'fundSources' => $fundSources,
                'appropriation' => $appropriation,
                'prevAppropriation' => $prevAppropriation,
                'year' => $postData['year'],
                'stage' => $postData['stage'],
                'postData' => $postData,
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
