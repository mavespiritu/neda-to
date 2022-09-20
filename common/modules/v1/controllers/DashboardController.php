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

class DashboardController extends \yii\web\Controller
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
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function filterized($params)
    {
    	$params = json_decode($params, true);

    	$data = [];

    	if(!empty($params))
        {
            foreach($params as $param)
            {
                $data[$param['name']] = $param['value'];
            }
        }

        return $data;
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

        return $this->render('index', [
            'model' => $model,
            'stages' => $stages,
            'years' => $years,
        ]);
    }

    public function actionAppropriation($params)
    {
        $filter = $this->filterized($params);

        $appropriation = Appropriation::find();

        $appPaps = [];
        $ppmpPaps = PpmpItem::find()
        ->select([
            'distinct(ppmp_pap.id) as pap_id'
        ])
        ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id')
        ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
        ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id');

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
            'ppmp_pap.id as pap_id',
            'ppmp_ppmp_item.fund_source_id as fund_source_id',
            'SUM(quantities.total * ppmp_ppmp_item.cost) as total'
        ])
        ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id')
        ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
        ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
        ->leftJoin(['quantities' => '('.$quantities.')'], 'quantities.ppmp_item_id = ppmp_ppmp_item.id');

        if($filter['AppropriationItem[stage]'] == 'Indicative')
        {
            $appropriation = $appropriation->andWhere(['type' => 'GAA', 'year' => $filter['AppropriationItem[year]'] - 1]);
        }
        else if($filter['AppropriationItem[stage]'] == 'Adjusted')
        {
            $appropriation = $appropriation->andWhere(['type' => 'NEP', 'year' => $filter['AppropriationItem[year]']]);
        }
        else if($filter['AppropriationItem[stage]'] == 'Final')
        {
            $appropriation = $appropriation->andWhere(['type' => 'GAA', 'year' => $filter['AppropriationItem[year]']]);
        }

        if($filter['AppropriationItem[year]'] != ''){
            $items = $items->andWhere(['ppmp_ppmp.year' => $filter['AppropriationItem[year]']]);
            $ppmpPaps = $ppmpPaps->andWhere(['ppmp_ppmp.year' => $filter['AppropriationItem[year]']]);
        }

        if($filter['AppropriationItem[stage]'] != ''){
            $items = $items->andWhere(['ppmp_ppmp.stage' => $filter['AppropriationItem[stage]']]);
            $ppmpPaps = $ppmpPaps->andWhere(['ppmp_ppmp.stage' => $filter['AppropriationItem[stage]']]);
        }
        
        $appropriation = $appropriation->one();
        $appropriationItems = [];

        if($appropriation)
        {
            $appropriationItems = AppropriationItem::find()
            ->select([
                'ppmp_appropriation_item.pap_id',
                'ppmp_appropriation_item.fund_source_id',
                'SUM(amount) as total'
            ]);

            $appropriationItems = $appropriationItems
            ->andWhere(['ppmp_appropriation_item.appropriation_id' => $appropriation->id])
            ->groupBy([
                'ppmp_appropriation_item.pap_id',
                'ppmp_appropriation_item.fund_source_id',
            ])
            ->asArray()
            ->all();

            $appPaps = AppropriationPap::find()->select(['distinct(pap_id) as pap_id'])->where(['appropriation_id' => $appropriation->id])->asArray()->all();
            $appPaps = ArrayHelper::map($appPaps, 'pap_id', 'pap_id');
        }

        $items = $items
        ->andWhere(['ppmp_ppmp_item.type' => 'Original'])
        ->groupBy([
            'ppmp_activity.pap_id',
            'ppmp_ppmp_item.fund_source_id',
        ])
        ->orderBy([
            'ppmp_pap.id' => SORT_ASC,
        ])
        ->asArray()
        ->all();

        $ppmpPaps = $ppmpPaps
        ->andWhere(['ppmp_ppmp_item.type' => 'Original'])
        ->asArray()
        ->all();

        $ppmpPaps = ArrayHelper::map($ppmpPaps, 'pap_id', 'pap_id');

        $data = [];

        if(!empty($appropriationItems))
        {
            foreach($appropriationItems as $item)
            {
                $data['source'][$item['pap_id']][$item['fund_source_id']] = $item;
            }
        }

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $data['ppmp'][$item['pap_id']][$item['fund_source_id']] = $item;
            }
        }

        $paps = Pap::find()->where(['in', 'id', array_unique(array_merge($appPaps, $ppmpPaps))])->orderBy(['id' => SORT_ASC])->all();

        $fundSources = FundSource::find()->all();

        return $this->renderAjax('_appropriation', [
            'data' => $data,
            'paps' => $paps,
            'fundSources' => $fundSources,
            'appropriation' => $appropriation,
            'postData' => $filter
         ]);
    }
    
    public function actionDownloadAppropriation($type, $post)
    {
        $filter = json_decode($post, true);

        $appropriation = Appropriation::find();

        $appPaps = [];
        $ppmpPaps = PpmpItem::find()
        ->select([
            'distinct(ppmp_pap.id) as pap_id'
        ])
        ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id')
        ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
        ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id');

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
            'ppmp_pap.id as pap_id',
            'ppmp_ppmp_item.fund_source_id as fund_source_id',
            'SUM(quantities.total * ppmp_ppmp_item.cost) as total'
        ])
        ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id')
        ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
        ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
        ->leftJoin(['quantities' => '('.$quantities.')'], 'quantities.ppmp_item_id = ppmp_ppmp_item.id');

        if($filter['AppropriationItem[stage]'] == 'Indicative')
        {
            $appropriation = $appropriation->andWhere(['type' => 'GAA', 'year' => $filter['AppropriationItem[year]'] - 1]);
        }
        else if($filter['AppropriationItem[stage]'] == 'Adjusted')
        {
            $appropriation = $appropriation->andWhere(['type' => 'NEP', 'year' => $filter['AppropriationItem[year]']]);
        }
        else if($filter['AppropriationItem[stage]'] == 'Final')
        {
            $appropriation = $appropriation->andWhere(['type' => 'GAA', 'year' => $filter['AppropriationItem[year]']]);
        }

        if($filter['AppropriationItem[year]'] != ''){
            $items = $items->andWhere(['ppmp_ppmp.year' => $filter['AppropriationItem[year]']]);
            $ppmpPaps = $ppmpPaps->andWhere(['ppmp_ppmp.year' => $filter['AppropriationItem[year]']]);
        }

        if($filter['AppropriationItem[stage]'] != ''){
            $items = $items->andWhere(['ppmp_ppmp.stage' => $filter['AppropriationItem[stage]']]);
            $ppmpPaps = $ppmpPaps->andWhere(['ppmp_ppmp.stage' => $filter['AppropriationItem[stage]']]);
        }
        
        $appropriation = $appropriation->one();
        $appropriationItems = [];

        if($appropriation)
        {
            $appropriationItems = AppropriationItem::find()
            ->select([
                'ppmp_appropriation_item.pap_id',
                'ppmp_appropriation_item.fund_source_id',
                'SUM(amount) as total'
            ]);

            $appropriationItems = $appropriationItems
            ->andWhere(['ppmp_appropriation_item.appropriation_id' => $appropriation->id])
            ->groupBy([
                'ppmp_appropriation_item.pap_id',
                'ppmp_appropriation_item.fund_source_id',
            ])
            ->asArray()
            ->all();

            $appPaps = AppropriationPap::find()->select(['distinct(pap_id) as pap_id'])->where(['appropriation_id' => $appropriation->id])->asArray()->all();
            $appPaps = ArrayHelper::map($appPaps, 'pap_id', 'pap_id');
        }

        $items = $items
        ->andWhere(['ppmp_ppmp_item.type' => 'Original'])
        ->groupBy([
            'ppmp_activity.pap_id',
            'ppmp_ppmp_item.fund_source_id',
        ])
        ->orderBy([
            'ppmp_pap.id' => SORT_ASC,
        ])
        ->asArray()
        ->all();

        $ppmpPaps = $ppmpPaps
        ->andWhere(['ppmp_ppmp_item.type' => 'Original'])
        ->asArray()
        ->all();

        $ppmpPaps = ArrayHelper::map($ppmpPaps, 'pap_id', 'pap_id');

        $data = [];

        if(!empty($appropriationItems))
        {
            foreach($appropriationItems as $item)
            {
                $data['source'][$item['pap_id']][$item['fund_source_id']] = $item;
            }
        }

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $data['ppmp'][$item['pap_id']][$item['fund_source_id']] = $item;
            }
        }

        $paps = Pap::find()->where(['in', 'id', array_unique(array_merge($appPaps, $ppmpPaps))])->orderBy(['id' => SORT_ASC])->all();

        $fundSources = FundSource::find()->all();

        $filename = $appropriation ? $appropriation->type.' '.$appropriation->year.' SUMMARY' : 'APPROPRIATION SUMMARY';

        if($type == 'excel')
        {
            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=".$filename.".xls");
            return $this->renderPartial('_appropriation-file', [
                'data' => $data,
                'paps' => $paps,
                'fundSources' => $fundSources,
                'appropriation' => $appropriation,
                'postData' => $filter,
                'type' => $type,
            ]);
        }else if($type == 'pdf')
        {
            $content = $this->renderPartial('_appropriation-file', [
                'data' => $data,
                'paps' => $paps,
                'fundSources' => $fundSources,
                'appropriation' => $appropriation,
                'postData' => $filter,
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

    public function actionPrexcSummary($params)
    {
        $filter = $this->filterized($params);

        $offices = Office::find();

        $ppmpPaps = PpmpItem::find()
        ->select([
            'distinct(ppmp_pap.id) as pap_id'
        ])
        ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id')
        ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
        ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id');

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
            'ppmp_cost_structure.abbreviation as csTitle',
            'ppmp_ppmp.office_id',
            'tbloffice.abbreviation as division',
            'ppmp_pap.id as pap_id',
            'ppmp_pap.short_code as short_code',
            'ppmp_pap.title as pap_title',
            'ppmp_ppmp_item.activity_id',
            'ppmp_activity.title as activity_title',
            'ppmp_ppmp_item.fund_source_id',
            'ppmp_fund_source.code as fundSource',
            'SUM(quantities.total * ppmp_ppmp_item.cost) as total'
        ])
        ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id')
        ->leftJoin('tbloffice', 'tbloffice.id = ppmp_ppmp.office_id')
        ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
        ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
        ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
        ->leftJoin('ppmp_fund_source', 'ppmp_fund_source.id = ppmp_ppmp_item.fund_source_id')
        ->leftJoin(['quantities' => '('.$quantities.')'], 'quantities.ppmp_item_id = ppmp_ppmp_item.id');

        if($filter['AppropriationItem[year]'] != ''){
            $items = $items->andWhere(['ppmp_ppmp.year' => $filter['AppropriationItem[year]']]);
            $ppmpPaps = $ppmpPaps->andWhere(['ppmp_ppmp.year' => $filter['AppropriationItem[year]']]);

        }

        if($filter['AppropriationItem[stage]'] != ''){
            $items = $items->andWhere(['ppmp_ppmp.stage' => $filter['AppropriationItem[stage]']]);
            $ppmpPaps = $ppmpPaps->andWhere(['ppmp_ppmp.stage' => $filter['AppropriationItem[stage]']]);
        }

        if(($filter['AppropriationItem[stage]'] == 'Adjusted' || $filter['AppropriationItem[stage]'] == 'Final') && $filter['AppropriationItem[year]'] == '2022'){
            $offices = $offices->andWhere(['<>', 'abbreviation', 'ORD']);
        }

        $items = $items
        ->andWhere(['ppmp_ppmp_item.type' => 'Original'])
        ->groupBy([
            'ppmp_activity.pap_id',
            'ppmp_ppmp.office_id',
            'ppmp_ppmp_item.activity_id',
            'ppmp_ppmp_item.fund_source_id',
        ])
        ->orderBy([
            'ppmp_pap.id' => SORT_ASC,
            'ppmp_activity.code' => SORT_ASC,
        ])
        ->asArray()
        ->all();

        $ppmpPaps = $ppmpPaps
        ->andWhere(['ppmp_ppmp_item.type' => 'Original'])
        ->asArray()
        ->all();

        $offices = $offices->all();

        $ppmpPaps = ArrayHelper::map($ppmpPaps, 'pap_id', 'pap_id');
        
        
        $paps = Pap::find()->where(['in', 'id', $ppmpPaps])->all();
        $fundSources = FundSource::find()->all();

        $data = [];
        $total = [];
        $headers = [];

        if($paps)
        {
            foreach($paps as $pap)
            {
                foreach($offices as $office)
                {
                    foreach($fundSources as $fundSource)
                    {
                        $headers[$pap->short_code][$office->abbreviation][$fundSource->code] = $fundSource->code;
                    }
                }
            }
        }

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $data[$item['activity_title']][$item['short_code']][$item['division']][$item['fundSource']] =  $item['total'];
            }
        }

        if($paps)
        {
            foreach($paps as $x)
            {
                foreach($paps as $y)
                {
                    if($offices)
                    {
                        foreach($offices as $office)
                        {
                            if($fundSources)
                            {
                                foreach($fundSources as $fundSource)
                                {
                                    $total[$x->short_code][$y->short_code][$office->abbreviation][$fundSource->code] = 0;
                                }
                            }
                        }
                    }
                }
            }
        }

        $tot = [];

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $tot[$item['short_code']][$item['short_code']][$item['division']][$item['fundSource']][] = $item['total'];
            }
        }

        if(!empty($tot))
        {
            foreach($tot as $x => $shortCodes)
            {
                if(!empty($shortCodes))
                {
                    foreach($shortCodes as $y => $divisions)
                    {
                        if(!empty($divisions))
                        {
                            foreach($divisions as $division => $fundSrcs)
                            {
                                if(!empty($fundSrcs))
                                {
                                    foreach($fundSrcs as $fundSrc => $totals)
                                    {
                                        if(!empty($totals))
                                        {
                                            foreach($totals as $idx => $value)
                                            {
                                                $total[$x][$y][$division][$fundSrc] += $value;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->renderAjax('_prexc-summary', [
           'headers' => $headers,
           'data' => $data,
           'total' => $total,
           'paps' => $paps,
           'offices' => $offices,
           'fundSources' => $fundSources,
           'postData' => $filter
        ]);
    }

    public function actionDownloadPrexcSummary($type, $post)
    {
        $filter = json_decode($post, true);

        $offices = Office::find();

        $ppmpPaps = PpmpItem::find()
        ->select([
            'distinct(ppmp_pap.id) as pap_id'
        ])
        ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id')
        ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
        ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id');

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
            'ppmp_cost_structure.abbreviation as csTitle',
            'ppmp_ppmp.office_id',
            'tbloffice.abbreviation as division',
            'ppmp_pap.id as pap_id',
            'ppmp_pap.short_code as short_code',
            'ppmp_pap.title as pap_title',
            'ppmp_ppmp_item.activity_id',
            'ppmp_activity.title as activity_title',
            'ppmp_ppmp_item.fund_source_id',
            'ppmp_fund_source.code as fundSource',
            'SUM(quantities.total * ppmp_ppmp_item.cost) as total'
        ])
        ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id')
        ->leftJoin('tbloffice', 'tbloffice.id = ppmp_ppmp.office_id')
        ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
        ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
        ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
        ->leftJoin('ppmp_fund_source', 'ppmp_fund_source.id = ppmp_ppmp_item.fund_source_id')
        ->leftJoin(['quantities' => '('.$quantities.')'], 'quantities.ppmp_item_id = ppmp_ppmp_item.id');

        if($filter['AppropriationItem[year]'] != ''){
            $items = $items->andWhere(['ppmp_ppmp.year' => $filter['AppropriationItem[year]']]);
            $ppmpPaps = $ppmpPaps->andWhere(['ppmp_ppmp.year' => $filter['AppropriationItem[year]']]);

        }

        if($filter['AppropriationItem[stage]'] != ''){
            $items = $items->andWhere(['ppmp_ppmp.stage' => $filter['AppropriationItem[stage]']]);
            $ppmpPaps = $ppmpPaps->andWhere(['ppmp_ppmp.stage' => $filter['AppropriationItem[stage]']]);
        }

        if(($filter['AppropriationItem[stage]'] == 'Adjusted' || $filter['AppropriationItem[stage]'] == 'Final') && $filter['AppropriationItem[year]'] == '2022'){
            $offices = $offices->andWhere(['<>', 'abbreviation', 'ORD']);
        }

        $items = $items
        ->andWhere(['ppmp_ppmp_item.type' => 'Original'])
        ->groupBy([
            'ppmp_activity.pap_id',
            'ppmp_ppmp.office_id',
            'ppmp_ppmp_item.activity_id',
            'ppmp_ppmp_item.fund_source_id',
        ])
        ->orderBy([
            'ppmp_pap.id' => SORT_ASC,
            'ppmp_activity.code' => SORT_ASC,
        ])
        ->asArray()
        ->all();

        $ppmpPaps = $ppmpPaps
        ->andWhere(['ppmp_ppmp_item.type' => 'Original'])
        ->asArray()
        ->all();

        $offices = $offices->all();

        $ppmpPaps = ArrayHelper::map($ppmpPaps, 'pap_id', 'pap_id');
        
        
        $paps = Pap::find()->where(['in', 'id', $ppmpPaps])->all();
        $fundSources = FundSource::find()->all();

        $data = [];
        $total = [];
        $headers = [];

        if($paps)
        {
            foreach($paps as $pap)
            {
                foreach($offices as $office)
                {
                    foreach($fundSources as $fundSource)
                    {
                        $headers[$pap->short_code][$office->abbreviation][$fundSource->code] = $fundSource->code;
                    }
                }
            }
        }

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $data[$item['activity_title']][$item['short_code']][$item['division']][$item['fundSource']] =  $item['total'];
            }
        }

        if($paps)
        {
            foreach($paps as $x)
            {
                foreach($paps as $y)
                {
                    if($offices)
                    {
                        foreach($offices as $office)
                        {
                            if($fundSources)
                            {
                                foreach($fundSources as $fundSource)
                                {
                                    $total[$x->short_code][$y->short_code][$office->abbreviation][$fundSource->code] = 0;
                                }
                            }
                        }
                    }
                }
            }
        }

        $tot = [];

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $tot[$item['short_code']][$item['short_code']][$item['division']][$item['fundSource']][] = $item['total'];
            }
        }

        if(!empty($tot))
        {
            foreach($tot as $x => $shortCodes)
            {
                if(!empty($shortCodes))
                {
                    foreach($shortCodes as $y => $divisions)
                    {
                        if(!empty($divisions))
                        {
                            foreach($divisions as $division => $fundSrcs)
                            {
                                if(!empty($fundSrcs))
                                {
                                    foreach($fundSrcs as $fundSrc => $totals)
                                    {
                                        if(!empty($totals))
                                        {
                                            foreach($totals as $idx => $value)
                                            {
                                                $total[$x][$y][$division][$fundSrc] += $value;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $filename = 'FINANCIAL PLAN';

        if($type == 'excel')
        {
            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=".$filename.".xls");
            return $this->renderPartial('_prexc-summary-file', [
                'headers' => $headers,
                'data' => $data,
                'total' => $total,
                'paps' => $paps,
                'offices' => $offices,
                'fundSources' => $fundSources,
                'postData' => $filter,
                'type' => $type,
            ]);
        }else if($type == 'pdf')
        {
            $content = $this->renderPartial('_prexc-summary-file', [
                'headers' => $headers,
                'data' => $data,
                'total' => $total,
                'paps' => $paps,
                'offices' => $offices,
                'fundSources' => $fundSources,
                'postData' => $filter,
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
