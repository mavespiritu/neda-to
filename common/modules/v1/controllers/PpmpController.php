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
use common\modules\v1\models\Transaction;
use common\modules\v1\models\Ris;
use common\modules\v1\models\RisItem;
use common\modules\v1\models\Pr;
use common\modules\v1\models\PrItem;
use common\modules\v1\models\RisSource;
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

/**
 * PpmpController implements the CRUD actions for Ppmp model.
 */
class PpmpController extends Controller
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
                        'actions' => ['index', 'create', 'update', 'view', 'delete', 'copy'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    function lastnodes($index, array $elements, $parentId = null) {
        $branch = array();
    
        foreach ($elements as $element) {
            if ($element[$index] == $parentId) {
                $children = $this->lastnodes($index, $elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
            }
            if($element['active'] == 1)
            {
                $branch[] = $element;
            }
        }
    
        return $branch;
    }

    public function actionFundSourceList($id, $activity_id)
    {
        $activity = Activity::findOne($activity_id);

        $existingFundSources = AppropriationPap::find()->select(['fund_source_id'])->where(['appropriation_id' => $id, 'pap_id' => $activity->pap_id])->asArray()->all();
        $existingFundSources = ArrayHelper::map($existingFundSources, 'fund_source_id', 'fund_source_id');

        $fundSources = FundSource::find()->select([
            'id',
            'code'
        ])
        ->where(['in', 'id', $existingFundSources])
        ->asArray()
        ->all();

        $arr = [];
        $arr[] = ['id'=>'','text'=>''];
        foreach($fundSources as $fundSource){
            $arr[] = ['id' => $fundSource['id'] ,'text' => $fundSource['code']];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    public function actionActivityList($id)
    {
        $activities = Activity::find()->where(['pap_id' => $id])->all();

        $arr = [];
        $arr[] = ['id'=>'','text'=>''];
        foreach($activities as $activity){
            $arr[] = ['id' => $activity->id ,'text' => $activity->activityTtitle];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    public function actionItemList($id, $sub_activity_id, $obj_id, $item_id)
    {
        $existingItems = PpmpItem::find()
                    ->select(['item_id'])
                    ->where([
                        'ppmp_id' => $id,
                        'sub_activity_id' => $sub_activity_id,
                        'obj_id' => $obj_id,
                        'type' => 'Original'
                    ]);
        
        $existingItems = $item_id == 0 ? $existingItems : $existingItems->andWhere(['<>', 'item_id', $item_id]);
    
        $existingItems = $existingItems->asArray()->all();

        $existingItems = ArrayHelper::map($existingItems, 'item_id', 'item_id');

        $items = Item::find()
                ->select([
                    'ppmp_item.id as id',
                    'ppmp_item.title as text',
                ])
                ->leftJoin('ppmp_object_item', 'ppmp_object_item.item_id = ppmp_item.id')
                ->andWhere(['ppmp_object_item.obj_id' => $obj_id])
                //->andWhere(['not in', 'ppmp_item.id', $existingItems])
                ->orderBy(['ppmp_item.title' => SORT_ASC])
                ->asArray()
                ->all();
        
        $arr = [];
        $arr[] = ['id'=>'','text'=>''];
        foreach($items as $item){
            $arr[] = ['id' => $item['id'], 'text' => $item['text']];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    /**
     * Lists all Ppmp models.
     * @return mixed
     */
    public function actionIndex()
    {
        $session = Yii::$app->session;

        $session->set('PPMP_ReturnURL', Yii::$app->controller->module->getBackUrl(Url::to()));

        $searchModel = new PpmpSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $offices = Office::find()->all();
        $offices = ArrayHelper::map($offices, 'id', 'abbreviation');

        $years = Ppmp::find()->select(['distinct(year) as year'])->orderBy(['year' => SORT_DESC])->asArray()->all();
        $years = ArrayHelper::map($years, 'year', 'year');

        $stages = [
            'Indicative' => 'Indicative',
            'Adjusted' => 'Adjusted',
            'Final' => 'Final',
        ];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'offices' => $offices,
            'years' => $years,
            'stages' => $stages,
        ]);
    }

    /**
     * Displays a single Ppmp model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        if(!Yii::$app->user->can('Administrator') && Yii::$app->user->identity->userinfo->OFFICE_C != $model->office_id){ throw new NotFoundHttpException('Page not found'); }

        $refFilters = [];
        $refFilters['Indicative'] = ['ppmp_appropriation.type' => 'GAA', 'ppmp_appropriation.year' => $model->year - 1]; 
        $refFilters['Adjusted'] = ['ppmp_appropriation.type' => 'NEP', 'ppmp_appropriation.year' => $model->year]; 
        $refFilters['Final'] = ['ppmp_appropriation.type' => 'GAA', 'ppmp_appropriation.year' => $model->year]; 
        
        $appropriationItemModel = new AppropriationItem();
        $appropriationItemModel->scenario = 'loadItems';
        
        /*  $progs = AppropriationPap::find()
                ->leftJoin('ppmp_appropriation', 'ppmp_appropriation.id = ppmp_appropriation_pap.appropriation_id')
                ->andWhere($refFilters[$model->stage])
                ->orderBy(['arrangement' => SORT_ASC])
                ->distinct(['pap_id'])
                ->all();
        
        $progs = ArrayHelper::map($progs, 'pap_id', 'pap_id');
         */
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

        //$fundSources = [];

        $fundSources = FundSource::find()->all();
        $fundSources = ArrayHelper::map($fundSources, 'id', 'code');

        return $this->render('view', [
            'model' => $model,
            'appropriationItemModel' => $appropriationItemModel,
            'activities' => $activities,
            'fundSources' => $fundSources,
        ]);
    }

    public function actionSummary($id)
    {
        $model = $this->findModel($id);

        return $this->render('summary', [
            'model' => $model,
        ]);
    }

    public function actionItemActivity($id)
    {
        $model = $this->findModel($id);

        $quantity = ItemBreakdown::find()
                   ->select([
                       'ppmp_item_id',
                       'sum(quantity) as total'
                   ])
                    ->groupBy(['ppmp_item_id'])
                    ->createCommand()
                    ->getRawSql();
        
        $items = PpmpItem::find()
                    ->select([
                        'fundSource.id as fundSourceID',
                        'fundSource.code as fundSourceTitle',
                        'ppmp_activity.id as activityID',
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
                        ) as activityTitle',
                        'subActivity.id as subActivityID',
                        'subActivity.title as subActivityTitle',
                        'object.id as objectID',
                        'concat(object.code," - ",object.title) as objectTitle',
                        'sum(cost * quantity.total) as total',
    
                    ])
                    ->leftJoin(['quantity' => '('.$quantity.')'], 'quantity.ppmp_item_id = ppmp_ppmp_item.id')
                    ->leftJoin('ppmp_sub_activity subActivity', 'subActivity.id = ppmp_ppmp_item.sub_activity_id')
                    ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                    ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                    ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                    ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                    ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                    ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                    ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                    ->leftJoin('ppmp_fund_source fundSource', 'fundSource.id = ppmp_ppmp_item.fund_source_id')
                    ->leftJoin('ppmp_obj object', 'object.id = ppmp_ppmp_item.obj_id')
                    ->groupBy(['subActivity.id','object.id'])
                    ->where(['ppmp_id' => $model->id])
                    ->orderBy([
                        'fundSourceTitle' => SORT_ASC,
                        'ppmp_pap.id' => SORT_ASC,
                        'ppmp_activity.code' => SORT_ASC,
                        'subActivity.code' => SORT_ASC,
                        'object.code' => SORT_ASC,
                        ])
                    ->asArray()
                    ->all();
        
        $data = [];

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $data[$item['fundSourceID']]['title'] = $item['fundSourceTitle'];
                $data[$item['fundSourceID']]['contents'][$item['activityID']]['title'] = $item['activityTitle'];
            }
        }

        return $this->renderAjax('_item-activity', [
            'model' => $model,
            'data' => $data,
        ]);
    }

    public function actionLoadItems($id, $activity_id, $fund_source_id)
    {
        $model = $this->findModel($id);
        $activity = Activity::findOne($activity_id);
        $fundSource = FundSource::findOne($fund_source_id);
        $subActivities = SubActivity::find()-> where(['activity_id' => $activity_id])->orderBy(['code' => SORT_ASC])->all();
        $items = [];
        $total = 0;

        if($subActivities)
        {
            foreach($subActivities as $key => $subActivity)
            {
                $items[$key]['label'] = '<table style="width:100%;" id="item-table-'.$subActivity->id.'" onclick="loadItemsInSubActivity('.$model->id.','.$subActivity->id.','.$activity->id.','.$fundSource->id.')">'; 
                $items[$key]['label'] .= '<tr>'; 
                $items[$key]['label'] .= '<td>'.$subActivity->subActivityTitle.'</td>'; 
                $items[$key]['label'] .= '<td align=right>'.PpmpItem::getCountPerSubActivity($model->id, $activity->id, $subActivity->id, $fundSource->id).'</td>'; 
                $items[$key]['label'] .= '<td align=right style="width: 45%;">'.number_format(PpmpItem::getTotalPerSubActivity($model->id, $activity->id, $subActivity->id, $fundSource->id), 2).'</td>'; 
                $items[$key]['label'] .= '</tr>'; 
                $items[$key]['label'] .= '</table>';
                $items[$key]['content'] = '<div id="item-list-'.$subActivity->id.'"></div>';
                $items[$key]['options'] = ['class' => PpmpItem::getTotalPerSubActivity($model->id, $activity->id, $subActivity->id, $fundSource->id) > 0 ? 'panel panel-success' : 'panel panel-default'];

                $total += PpmpItem::getTotalPerSubActivity($model->id, $activity->id, $subActivity->id, $fundSource->id);
            }
        }

        return $this->renderAjax('_items', [
            'model' => $model,
            'items' => $items,
            'activity' => $activity,
            'activity_id' => $activity_id,
            'fundSource' => $fundSource,
            'fund_source_id' => $fund_source_id,
            'total' => $total,
        ]);
    }

    public function actionCreateItem($id, $activity_id, $fund_source_id)
    {
        $model = $this->findModel($id);
        $activity = Activity::findOne($activity_id);
        $fundSource = FundSource::findOne($fund_source_id);

        $subActivities = SubActivity::find()
                        ->select([
                            'ppmp_sub_activity.id as id',
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
                            ) as title',
                        ])
                        ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_sub_activity.activity_id')
                        ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                        ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                        ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                        ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                        ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                        ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                        ->where(['ppmp_sub_activity.activity_id' => $activity->id])
                        ->asArray()
                        ->all();

        $subActivities = ArrayHelper::map($subActivities, 'id', 'title');

        $itemModel = new PpmpItem();
        $itemModel->ppmp_id = $model->id;
        $itemModel->activity_id = $activity->id;
        $itemModel->type = 'Original';

        $objects = Obj::find()->select([
            'ppmp_obj.id', 
            'ppmp_obj.obj_id', 
            'concat(ppmp_obj.code," - ",ppmp_obj.title) as text',
            'p.title as groupTitle',
            'ppmp_obj.active'
            ])
            ->leftJoin(['p' => '(SELECT id, concat(code," - ",title) as title from ppmp_obj)'], 'p.id = ppmp_obj.obj_id')
            ->asArray()
            ->all();
        
        $objects = $this->lastnodes('obj_id', $objects);

        $objects = ArrayHelper::map($objects, 'id', 'text', 'groupTitle');
        
        $items = [];

        $months = Month::find()->all();
        $itemBreakdowns = [];

        if($months)
        {
            foreach($months as $month)
            {
                $breakdown = new ItemBreakdown();
                $breakdown->month_id = $month->id;

                $itemBreakdowns[$month->id] = $breakdown;
            }
        }

        /* if (Yii::$app->request->isAjax && $itemModel->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($itemModel);
        } */

        if($itemModel->load(Yii::$app->request->post()))
        {
            $cost = ItemCost::find()->where(['item_id' => $itemModel->item_id])->orderBy(['datetime' => SORT_DESC])->one();
            
            $itemModel->cost = $cost->cost;
            if($itemModel->save())
            {
                if(!$itemModel->itemBreakdowns)
                {
                    $breakdowns = Yii::$app->request->post('ItemBreakdown');
                    if(!empty($breakdowns))
                    {
                        foreach($breakdowns as $breakdown)
                        {
                            $breakdownModel = new ItemBreakdown();
                            $breakdownModel->ppmp_item_id = $itemModel->id;
                            $breakdownModel->month_id = $breakdown['month_id'];
                            $breakdownModel->quantity = empty($breakdown['quantity']) ? 0 : $breakdown['quantity'];
                            $breakdownModel->save();
                        }
                    }
                }
            }
            
        }

        return $this->renderAjax('_item-form', [
            'model' => $model,
            'activity' => $activity,
            'fundSource' => $fundSource,
            'itemModel' => $itemModel,
            'subActivities' => $subActivities,
            'items' => $items,
            'objects' => $objects,
            'months' => $months,
            'itemBreakdowns' => $itemBreakdowns,
        ]);
    }

    public function actionUpdateItem($id)
    {
        $itemModel = PpmpItem::findOne($id);

        $model = $itemModel->ppmp;
        $activity = $itemModel->activity;
        $fundSource = $itemModel->fundSource;

        $subActivities = SubActivity::find()
                        ->select([
                            'ppmp_sub_activity.id as id',
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
                            ) as title',
                        ])
                        ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_sub_activity.activity_id')
                        ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                        ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                        ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                        ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                        ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                        ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                        ->where(['ppmp_sub_activity.activity_id' => $activity->id])
                        ->asArray()
                        ->all();
        $subActivities = ArrayHelper::map($subActivities, 'id', 'title');

        /* $selectedObjs = AppropriationItem::find()
        ->select(['ppmp_appropriation_item.obj_id as id'])
        ->leftJoin('ppmp_appropriation', 'ppmp_appropriation.id = ppmp_appropriation_item.appropriation_id')
        ->andWhere(['>', 'amount', 0])
        ->andWhere([
            'ppmp_appropriation.id' => $model->reference ? $model->reference->id : null,
            'ppmp_appropriation_item.pap_id' => $activity->pap_id,
            'ppmp_appropriation_item.fund_source_id' => $fundSource->id,
        ])
        ->distinct(['ppmp_appropriation_item.obj_id'])
        ->all();

        $selectedObjs = ArrayHelper::map($selectedObjs, 'id', 'id'); */

        $objectItem = ObjectItem::findOne(['obj_id' => $itemModel->obj_id, 'item_id' => $itemModel->item_id]) ? 
        ObjectItem::findOne(['obj_id' => $itemModel->obj_id, 'item_id' => $itemModel->item_id]) : 
        new ObjectItem();
        $objectItem->obj_id = $itemModel->obj_id;
        $objectItem->item_id = $itemModel->item_id;
        $objectItem->save(false);

        $objects = Obj::find()->select([
            'ppmp_obj.id', 
            'ppmp_obj.obj_id', 
            'concat(ppmp_obj.code," - ",ppmp_obj.title) as text',
            'p.title as groupTitle',
            'ppmp_obj.active'
            ])
            ->leftJoin(['p' => '(SELECT id, concat(code," - ",title) as title from ppmp_obj)'], 'p.id = ppmp_obj.obj_id')
            //->andWhere(['in', 'ppmp_obj.id', $selectedObjs])
            ->asArray()
            ->all();
        
        $objects = $this->lastnodes('obj_id', $objects);

        $objects = ArrayHelper::map($objects, 'id', 'text', 'groupTitle');
        
        $existingItems = PpmpItem::find()
                    ->select(['item_id'])
                    ->where([
                        'ppmp_id' => $model->id,
                        'sub_activity_id' => $itemModel->sub_activity_id,
                        'obj_id' => $itemModel->obj_id,
                    ])
                    ->andWhere(['<>', 'item_id', $itemModel->item_id])
                    ->asArray()
                    ->all();

        $existingItems = ArrayHelper::map($existingItems, 'item_id', 'item_id');

        $items = Item::find()
                ->select([
                    'ppmp_item.id as id',
                    'ppmp_item.title as text',
                ])
                ->leftJoin('ppmp_object_item', 'ppmp_object_item.item_id = ppmp_item.id')
                ->andWhere(['ppmp_object_item.obj_id' => $itemModel->obj_id])
                //->andWhere(['not in', 'ppmp_item.id', $existingItems])
                ->asArray()
                ->all();

        $items = ArrayHelper::map($items, 'id', 'text');

        $months = Month::find()->all();
        $itemBreakdownModels = $itemModel->itemBreakdowns;
        $itemBreakdowns = [];

        if($months)
        {
            foreach($months as $month)
            {
                $breakdown = ItemBreakdown::findOne(['ppmp_item_id' => $itemModel->id, 'month_id' => $month->id]) ? 
                ItemBreakdown::findOne(['ppmp_item_id' => $itemModel->id, 'month_id' => $month->id])  : new ItemBreakdown();
                $breakdown->ppmp_item_id = $itemModel->id;
                $breakdown->month_id = $month->id;

                $itemBreakdowns[$month->id] = $breakdown;
            }
        }

        /* if (Yii::$app->request->isAjax && $itemModel->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($itemModel);
        } */

        if($itemModel->load(Yii::$app->request->post()))
        {
            $cost = ItemCost::find()->where(['item_id' => $itemModel->item_id])->orderBy(['datetime' => SORT_DESC])->one();
            
            $itemModel->cost = $cost->cost;

            if($itemModel->save())
            {
                $breakdowns = Yii::$app->request->post('ItemBreakdown');
                if(!empty($breakdowns))
                {
                    foreach($breakdowns as $breakdown)
                    {
                        $breakdownModel = ItemBreakdown::findOne(['ppmp_item_id' => $itemModel->id, 'month_id' => $breakdown['month_id']]) ? ItemBreakdown::findOne(['ppmp_item_id' => $itemModel->id, 'month_id' => $breakdown['month_id']]) : new ItemBreakdown();
                        $breakdownModel->ppmp_item_id = $itemModel->id;
                        $breakdownModel->month_id = $breakdown['month_id'];
                        $breakdownModel->quantity = empty($breakdown['quantity']) ? 0 : $breakdown['quantity'];
                        $breakdownModel->save();
                    }
                }
            }
            
        }

        return $this->renderAjax('_item-form', [
            'model' => $model,
            'activity' => $activity,
            'fundSource' => $fundSource,
            'itemModel' => $itemModel,
            'subActivities' => $subActivities,
            'items' => $items,
            'objects' => $objects,
            'months' => $months,
            'itemBreakdowns' => $itemBreakdowns,
        ]);
    }

    public function actionDeleteItem($id)
    {
        $model = PpmpItem::findOne($id);
        $model->delete();
    }

    public function actionLoadPpmpTotal($id)
    {
        $model = $this->findModel($id);

        return number_format($model->total, 2);
    }

    public function actionLoadOriginalTotal($id)
    {
        $model = $this->findModel($id);

        return number_format($model->originalTotal, 2);
    }

    public function actionLoadSupplementalTotal($id)
    {
        $model = $this->findModel($id);

        return number_format($model->supplementalTotal, 2);
    }

    public function actionLoadItemsInSubActivity($id, $sub_activity_id, $activity_id, $fund_source_id)
    {
        $model = $this->findModel($id);
        $activity = Activity::findOne($activity_id);
        $fundSource = FundSource::findOne($fund_source_id);
        $subActivity = SubActivity::findOne($sub_activity_id);

        $searchModel = new PpmpItemSearch();
        $searchModel->ppmp_id = $model->id;
        $searchModel->activity_id = $activity->id;
        $searchModel->fund_source_id = $fundSource->id;
        $searchModel->sub_activity_id = $subActivity->id;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'true');

        return $this->renderAjax('_items-in-sub-activity', [
            'model' => $model,
            'activity' => $activity,
            'fundSource' => $fundSource,
            'subActivity' => $subActivity,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionLoadItemSummary($id)
    {
        $model = $this->findModel($id);

        $quantity = ItemBreakdown::find()
                   ->select([
                       'ppmp_item_id',
                       'sum(quantity) as total'
                   ])
                    ->groupBy(['ppmp_item_id'])
                    ->createCommand()
                    ->getRawSql();
        
                    $items = PpmpItem::find()
                    ->select([
                        'fundSource.id as fundSourceID',
                        'fundSource.code as fundSourceTitle',
                        'ppmp_activity.id as activityID',
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
                        ) as activityTitle',
                        'ppmp_sub_activity.id as subActivityID',
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
                        ) as subActivityTitle',
                        'object.id as objectID',
                        'concat(object.code," - ",object.title) as objectTitle',
                        'sum(cost * quantity.total) as total',
    
                    ])
                    ->leftJoin(['quantity' => '('.$quantity.')'], 'quantity.ppmp_item_id = ppmp_ppmp_item.id')
                    ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                    ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                    ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                    ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                    ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                    ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                    ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                    ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                    ->leftJoin('ppmp_fund_source fundSource', 'fundSource.id = ppmp_ppmp_item.fund_source_id')
                    ->leftJoin('ppmp_obj object', 'object.id = ppmp_ppmp_item.obj_id')
                    ->groupBy(['ppmp_sub_activity.id','object.id'])
                    ->where(['ppmp_id' => $model->id])
                    ->orderBy([
                        'fundSourceTitle' => SORT_ASC,
                        'ppmp_pap.id' => SORT_ASC,
                        'ppmp_activity.code' => SORT_ASC,
                        'ppmp_sub_activity.code' => SORT_ASC,
                        'object.code' => SORT_ASC,
                        ])
                    ->asArray()
                    ->all();
        
        $data = [];

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $data[$item['fundSourceID']]['title'] = $item['fundSourceTitle'];
                $data[$item['fundSourceID']]['contents'][$item['activityID']]['title'] = $item['activityTitle'];
                $data[$item['fundSourceID']]['contents'][$item['activityID']]['contents'][$item['subActivityID']]['title'] = $item['subActivityTitle'];
                $data[$item['fundSourceID']]['contents'][$item['activityID']]['contents'][$item['subActivityID']]['total'] = $item['total'];
            }
        }

        return $this->renderAjax('_item-summary',[
            'model' => $model,
            'data' => $data
        ]);
    }

    public function actionUnitOfMeasure($id)
    {
        $model = Item::findOne($id);

        return $model->unit_of_measure;
    }

    public function actionCost($id)
    {
        $model = Item::findOne($id);
        $cost = $model->getItemCosts()->orderBy(['datetime' => SORT_DESC])->one();

        return $cost ? number_format($cost->cost, 2) : number_format($model->cost_per_unit, 2);
    }

    public function actionCostPerUnit($id)
    {
        $model = Item::findOne($id);

        return $model->cost_per_unit;
    }

    public function actionReference($id)
    {
        $model = Appropriation::findOne($id);

        $items = [];
        $objects = $model->getAppropriationObjs()->orderBy(['arrangement'=> SORT_ASC])->all();
        $programs = $model->getAppropriationPaps()->orderBy(['arrangement'=> SORT_ASC])->all();

        if($objects)
        {
            foreach($objects as $object)
            {   
                if($programs)
                {
                    foreach($programs as $program)
                    {
                        $item = AppropriationItem::findOne(['appropriation_id' => $model->id, 'obj_id' => $object->obj_id, 'pap_id' => $program->pap_id, 'fund_source_id' => $program->fund_source_id]) ? 
                        AppropriationItem::findOne(['appropriation_id' => $model->id, 'obj_id' => $object->obj_id, 'pap_id' => $program->pap_id, 'fund_source_id' => $program->fund_source_id]) : 
                        new AppropriationItem();

                        $item->appropriation_id = $model->id;
                        $item->obj_id = $object->obj_id;
                        $item->pap_id = $program->pap_id;
                        $item->fund_source_id = $program->fund_source_id;

                        $items[$object->obj_id][$program->id] = $item;
                    }
                }
            }
        }

        return $this->renderAjax('_reference',[
            'model' => $model,
            'items' => $items,
        ]);
    }

    /**
     * Creates a new Ppmp model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Ppmp();

        $model->scenario = Yii::$app->user->can('Administrator') ? 'isAdmin' : 'isUser';

        $offices = Office::find()->all();
        $offices = ArrayHelper::map($offices, 'id', 'abbreviation');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            $model->office_id = Yii::$app->user->can('Administrator') ? $model->office_id : Yii::$app->user->identity->userinfo->OFFICE_C;
            $model->created_by = Yii::$app->user->id;
        	$model->date_created = date("Y-m-d H:i:s");
            $model->save();

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('create', [
            'model' => $model,
            'offices' => $offices,
        ]);
    }

    public function actionCopy()
    {
        $model = new PPMP();
        $model->scenario = Yii::$app->user->can('Administrator') ? 'isAdminCopy' : 'isUserCopy';

        $offices = Office::find()->all();
        $offices = ArrayHelper::map($offices, 'id', 'abbreviation');

        $ppmps = Yii::$app->user->can('Administrator') ? Ppmp::find()
        ->joinWith('office')
        ->orderBy(['year' => SORT_DESC])
        ->all() : Ppmp::find()
        ->joinWith('office')
        ->andWhere(['office_id' => Yii::$app->user->identity->userinfo->OFFICE_C])
        ->orderBy(['year' => SORT_DESC])
        ->all();
        
        $ppmps = ArrayHelper::map($ppmps, 'id', 'title');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            $model->office_id = Yii::$app->user->can('Administrator') ? $model->office_id : Yii::$app->user->identity->userinfo->OFFICE_C;
            $model->created_by = Yii::$app->user->id;
        	$model->date_created = date("Y-m-d H:i:s");
            $model->save();

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('copy', [
            'model' => $model,
            'ppmps' => $ppmps,
            'offices' => $offices,
        ]);
    }

    /**
     * Updates an existing Ppmp model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->scenario = Yii::$app->user->can('Administrator') ? 'isAdmin' : 'isUser';

        $offices = Office::find()->all();
        $offices = ArrayHelper::map($offices, 'id', 'abbreviation');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            $model->updated_by = Yii::$app->user->id;
        	$model->date_updated = date("Y-m-d H:i:s");
            $model->save();

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('update', [
            'model' => $model,
            'offices' => $offices,
        ]);
    }

    /**
     * Deletes an existing Ppmp model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if($model->delete())
        {
            $statuses = Transaction::deleteAll(['model' => 'Ppmp', 'model_id' => $id]);

            \Yii::$app->getSession()->setFlash('success', 'Record Deleted');
            return $this->redirect(['index']);

        }
    }

    public function actionApprove($id)
    {
        $model = $this->findModel($id);

        $status = new Transaction();
        $status->actor = Yii::$app->user->identity->userinfo->EMP_N;
        $status->model = 'Ppmp';
        $status->model_id = $model->id;
        $status->status = 'Approved';
        if($status->save())
        {
            \Yii::$app->getSession()->setFlash('success', 'PPMP is approved successfully');
            return $this->redirect(['view', 'id' => $model->id]);
        }
    }

    public function actionDisapprove($id)
    {
        $model = $this->findModel($id);

        $status = new Transaction();
        $status->actor = Yii::$app->user->identity->userinfo->EMP_N;
        $status->model = 'Ppmp';
        $status->model_id = $model->id;
        $status->status = 'Disapproved';
        if($status->save())
        {
            \Yii::$app->getSession()->setFlash('danger', 'PPMP is disapproved');
            return $this->redirect(['view', 'id' => $model->id]);
        }
    }

    public function actionItemCheck($id)
    {
        $model = $this->findModel($id);

        $activities = Activity::find()
        ->select([
            'ppmp_activity.id as id',
            'ppmp_activity.pap_id as pap_id',
            'concat(
               ppmp_cost_structure.code,"",
               ppmp_organizational_outcome.code,"",
               ppmp_program.code,"",
               ppmp_sub_program.code,"",
               ppmp_identifier.code,"",
               ppmp_pap.code,"000-",
               ppmp_activity.code," - ",
               ppmp_activity.title
           ) as text',
            'p.title as groupTitle'
        ])
       ->leftJoin(['p' => '(
           SELECT 
           ppmp_pap.id as id, 
           ppmp_pap.code as code, 
           concat(
               ppmp_cost_structure.code,"",
               ppmp_organizational_outcome.code,"",
               ppmp_program.code,"",
               ppmp_sub_program.code,"",
               ppmp_identifier.code,"",
               ppmp_pap.code,"000 - ",
               ppmp_pap.title
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

        $params = Yii::$app->request->queryParams;

        $activities = ArrayHelper::map($activities, 'id', 'text', 'groupTitle');
        
        $subActivities = [];

        if(!empty($params))
        {
            $subActivities = isset($params['PpmpItemSearch']['activity_id']) ? SubActivity::find()->where(['activity_id' => $params['PpmpItemSearch']['activity_id']])->all() : [];
        }

        $subActivities = !empty($subActivities) ? ArrayHelper::map($subActivities, 'id', 'title') : [];

        $objects = Obj::find()->select([
            'ppmp_obj.id', 
            'ppmp_obj.obj_id', 
            'concat(ppmp_obj.code," - ",ppmp_obj.title) as text',
            'p.title as groupTitle',
            'ppmp_obj.active'
            ])
            ->leftJoin(['p' => '(SELECT id, concat(code," - ",title) as title from ppmp_obj)'], 'p.id = ppmp_obj.obj_id')
            ->asArray()
            ->all();
        
        $objects = $this->lastnodes('obj_id', $objects);

        $objects = ArrayHelper::map($objects, 'id', 'text', 'groupTitle');

        $searchModel = new PpmpItemSearch();
        $searchModel->ppmp_id = $model->id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, '');

        return $this->render('item-check', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'activities' => $activities,
            'subActivities' => $subActivities,
            'objects' => $objects,
        ]);
    }
    
    public function actionFixItem($id)
    {
        $model = PpmpItem::findOne($id);
        $months = Month::find()->all();

        return $this->renderAjax('_fix-item', [
            'model' => $model,
            'months' => $months,
        ]);   
    }

    public function actionViewItem($id)
    {
        $model = PpmpItem::findOne($id);
        $risIDs = RisSource::find()
                ->select(['ris_id'])
                ->andWhere(['ppmp_item_id' => $model->id])
                ->andWhere(['<>', 'type', 'Realigned'])
                ->asArray()
                ->all();
        $risIDs = ArrayHelper::map($risIDs, 'ris_id', 'ris_id');

        $rises = Ris::find()->where(['in', 'id', $risIDs])->all();

        $realignedRisIDs = RisSource::find()
                        ->select(['ris_id'])
                        ->andWhere(['ppmp_item_id' => $model->id])
                        ->andWhere(['type' => 'Realigned'])
                        ->asArray()
                        ->all();
        $realignedRisIDs = ArrayHelper::map($realignedRisIDs, 'ris_id', 'ris_id');

        $realignedRises = Ris::find()->where(['in', 'id', $realignedRisIDs])->all();

        $prIDs = PrItem::find()
        ->select(['pr_id'])
        ->andWhere(['ppmp_item_id' => $model->id])
        ->asArray()
        ->all();
        $prIDs = ArrayHelper::map($prIDs, 'pr_id', 'pr_id');

        $prs = Pr::find()->where(['in', 'id', $prIDs])->all();

        return $this->renderAjax('_item-info', [
            'model' => $model,
            'rises' => $rises,
            'realignedRises' => $realignedRises,
            'prs' => $prs,
        ]);
    }

    /**
     * Finds the Ppmp model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Ppmp the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ppmp::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
