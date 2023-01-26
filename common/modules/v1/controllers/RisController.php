<?php

namespace common\modules\v1\controllers;

use Yii;
use common\modules\v1\models\AppropriationItem;
use common\modules\v1\models\Activity;
use common\modules\v1\models\SubActivity;
use common\modules\v1\models\FUndSource;
use common\modules\v1\models\Ris;
use common\modules\v1\models\Month;
use common\modules\v1\models\Ppmp;
use common\modules\v1\models\Obj;
use common\modules\v1\models\Item;
use common\modules\v1\models\PpmpItem;
use common\modules\v1\models\ItemCost;
use common\modules\v1\models\ItemBreakdown;
use common\modules\v1\models\PpmpItemSearch;
use common\modules\v1\models\FundCluster;
use common\modules\v1\models\Signatory;
use common\modules\v1\models\RisItem;
use common\modules\v1\models\RisItemSpec;
use common\modules\v1\models\RisItemSpecValue;
use common\modules\v1\models\RisSource;
use common\modules\v1\models\RisSearch;
use common\modules\v1\models\ForContractItem;
use common\modules\v1\models\Settings;
use common\modules\v1\models\Model;
use common\modules\v1\models\MultipleModel;
use common\modules\v1\models\Transaction;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use markavespiritu\user\models\Office;
use yii\widgets\ActiveForm;
use yii\web\Response;
use kartik\mpdf\Pdf;
/**
 * RisController implements the CRUD actions for Ris model.
 */
class RisController extends Controller
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
                'only' => ['index', 'info', 'supplemental', 'original', 'realign'],
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'view', 'delete', 'info', 'supplemental', 'original', 'realign'],
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

    public function actionSignatoryList($id)
    {
        $office = Office::findOne(['abbreviation' => $id]);
        $signatories = Signatory::find()->where(['office_id' => $office->id])->all();

        $arr = [];
        $arr[] = ['id'=>'','text'=>''];
        foreach($signatories as $signatory){
            $arr[] = ['id' => $signatory->emp_id ,'text' => $signatory->name];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    public function actionPpmpList($id)
    {
        $office = Office::findOne(['abbreviation' => $id]);
        $ppmps = $office ? Ppmp::find()->where(['office_id' => $office->id, 'stage' => 'Final'])->all() : []; 

        $arr = [];
        $arr[] = ['id'=>'','text'=>''];
        foreach($ppmps as $ppmp){
            $arr[] = ['id' => $ppmp->id ,'text' => $ppmp->title];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    public function actionSubActivityList($id)
    {
        $subActivities = SubActivity::find()->where(['activity_id' => $id])->all();

        $arr = [];
        $arr[] = ['id'=>'','text'=>''];
        foreach($subActivities as $subActivity){
            $arr[] = ['id' => $subActivity->id ,'text' => $subActivity->title];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    public function actionItemList($id, $obj_id, $type)
    {
        $model = $this->findModel($id);

        $forContractItems = ForContractItem::find()->select(['item_id'])->asArray()->all();
        $forContractItems = ArrayHelper::map($forContractItems, 'item_id', 'item_id');

        $supplementalItems = RisItem::find()
                            ->select(['ppmp_item.id as id'])
                            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                            ->where([
                                'ppmp_ris_item.ris_id' => $model->id, 
                                'ppmp_ris_item.type' => 'Supplemental'
                                ])
                            ->groupBy(['ppmp_item.id'])
                            ->asArray()
                            ->all();
 
        $supplementalItems = ArrayHelper::map($supplementalItems, 'id', 'id');
        
        $items = Item::find()
        ->select([
            'ppmp_item.id as id',
            'ppmp_item.title as text',
        ])
        ->leftJoin('ppmp_object_item', 'ppmp_object_item.item_id = ppmp_item.id')
        ->andWhere(['ppmp_object_item.obj_id' => $obj_id]);

        $items = $model->type == 'Supply' ? $items->andWhere(['not in', 'ppmp_item.id', $forContractItems]) : $items->andWhere(['in', 'ppmp_item.id', $forContractItems]);
        if($type == 'Supplemental'){ $items = $items->andWhere(['not in', 'ppmp_item.id', $supplementalItems]); }

        $items = $items
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

    public function actionOriginalItemList($id, $activity_id, $sub_activity_id)
    {
        $model = $this->findModel($id);
        $forContractItems = ForContractItem::find()->select(['item_id'])->asArray()->all();
        $forContractItems = ArrayHelper::map($forContractItems, 'item_id', 'item_id');
        
        $items = Item::find()
        ->select([
            'ppmp_item.id as id',
            'ppmp_item.title as text',
        ])
        ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.item_id = ppmp_item.id')
        ->leftJoin('ppmp_object_item', 'ppmp_object_item.item_id = ppmp_item.id')
        ->andWhere(['ppmp_ppmp_item.ppmp_id' => $model->ppmp->id])
        ->andWhere(['ppmp_ppmp_item.activity_id' => $activity_id])
        ->andWhere(['ppmp_ppmp_item.sub_activity_id' => $sub_activity_id])
        ->andWhere(['ppmp_ppmp_item.fund_source_id' => $model->fund_source_id])
        ;

        $items = $model->type == 'Supply' ? $items->andWhere(['not in', 'ppmp_item.id', $forContractItems]) : $items->andWhere(['in', 'ppmp_item.id', $forContractItems]);

        $items = $items
                ->andWhere(['ppmp_ppmp_item.type' => 'Original'])
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

    public function actionRealignItemList($id, $activity_id, $sub_activity_id)
    {
        $model = $this->findModel($id);
        $forContractItems = ForContractItem::find()->select(['item_id'])->asArray()->all();
        $forContractItems = ArrayHelper::map($forContractItems, 'item_id', 'item_id');
        
        $items = Item::find()
        ->select([
            'ppmp_item.id as id',
            'ppmp_item.title as text',
        ])
        ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.item_id = ppmp_item.id')
        ->leftJoin('ppmp_object_item', 'ppmp_object_item.item_id = ppmp_item.id')
        ->andWhere(['ppmp_ppmp_item.ppmp_id' => $model->ppmp->id])
        ->andWhere(['ppmp_ppmp_item.activity_id' => $activity_id])
        ->andWhere(['ppmp_ppmp_item.sub_activity_id' => $sub_activity_id])
        ->andWhere(['ppmp_ppmp_item.fund_source_id' => $model->fund_source_id])
        ;

        $items = $items
                ->andWhere(['ppmp_ppmp_item.type' => 'Original'])
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

    public function actionMaxValue($id, $month_id)
    {
        $max = ItemBreakdown::findOne(['ppmp_item_id' => $id, 'month_id' => $month_id]);

        return $max ? $max->remaining : 0;
    }

    /**
     * Lists all Ris models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new RisSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $types = [
            'Supply' => 'Goods',
            'Service' => 'Service/Contract',
        ];

        $fundSources = FundSource::find()->all();
        $fundSources = ArrayHelper::map($fundSources, 'id', 'code');

        $offices = Office::find()->all();
        $offices = ArrayHelper::map($offices, 'abbreviation', 'abbreviation');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'offices' => $offices,
            'fundSources' => $fundSources,
            'types' => $types,
        ]);
    }

    /**
     * Displays a single Ris model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $appropriationItemModel = new AppropriationItem();
        $appropriationItemModel->scenario = 'loadItemsInRis';

        $fundedActivities = PpmpItem::find()->select(['distinct(activity_id) as id'])->where(['ppmp_id' => $model->ppmp->id])->all();
        $fundedActivities = ArrayHelper::map($fundedActivities, 'id', 'id');

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
                    ->andWhere(['in', 'ppmp_activity.id', $fundedActivities])
                    ->asArray()
                    ->all();
        
        $activities = ArrayHelper::map($activities, 'id', 'text', 'groupTitle');
                        
        $subActivities = [];

        $items = [];

        $months = Month::find()->all();
        $months = ArrayHelper::map($months, 'id', 'month');

        return $this->render('view', [
            'model' => $model,
            'appropriationItemModel' => $appropriationItemModel,
            'activities' => $activities,
            'subActivities' => $subActivities,
            'items' => $items,
            'months' => $months,
        ]);
    }

    public function actionLoadItems($id, $activity_id, $sub_activity_id, $item_id, $month_id)
    {
        $model = $this->findModel($id);
        $ppmp = $model->ppmp;
        $activity = Activity::findOne($activity_id);
        $subActivity = SubActivity::findOne($sub_activity_id);
        $selectedItems = json_decode($item_id, true);
        $selectedMonths = json_decode($month_id, true);
        
        $forContractItems = ForContractItem::find()->select(['item_id'])->asArray()->all();
        $forContractItems = ArrayHelper::map($forContractItems, 'item_id', 'item_id');

        $itemIDs = $model->type == 'Supply' ? ItemBreakdown::find()
                ->select(['ppmp_item_id'])
                ->leftJoin('ppmp_ppmp_item i', 'i.id = ppmp_ppmp_item_breakdown.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = i.item_id')
                ->andWhere([
                    'i.ppmp_id' => $ppmp->id,
                    'i.activity_id' => $activity->id,
                    'i.sub_activity_id' => $subActivity->id,
                    'i.fund_source_id' => $model->fund_source_id,
                    'i.type' => 'Original'
                ])
                ->andWhere(['>', 'quantity', 0])
                ->andWhere(['not in', 'i.item_id', $forContractItems])
                ->andWhere(['in', 'ppmp_item.id', $selectedItems])
                 : ItemBreakdown::find()
                ->select(['ppmp_item_id'])
                ->leftJoin('ppmp_ppmp_item i', 'i.id = ppmp_ppmp_item_breakdown.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = i.item_id')
                ->andWhere([
                    'i.ppmp_id' => $ppmp->id,
                    'i.activity_id' => $activity->id,
                    'i.sub_activity_id' => $subActivity->id,
                    'i.fund_source_id' => $model->fund_source_id,
                    'i.type' => 'Original'
                ])
                ->andWhere(['>', 'quantity', 0])
                ->andWhere(['in', 'i.item_id', $forContractItems])
                ->andWhere(['in', 'ppmp_item.id', $selectedItems])
                ;

        $itemIDs = !empty($selectedMonths) ? $itemIDs->andWhere(['in', 'ppmp_ppmp_item_breakdown.month_id', $selectedMonths]) : $itemIDs;

        $itemIDs = $itemIDs
                    ->asArray()
                    ->all();
        
        $itemIDs = ArrayHelper::map($itemIDs, 'ppmp_item_id', 'ppmp_item_id');

        $data = [];

        if(!empty($itemIDs))
        {
            foreach($itemIDs as $id)
            {
                $ppmpItem = PpmpItem::findOne(['id' => $id]);
                $quantity = $ppmpItem ? $ppmpItem->getRemainingQuantityPerMonths($selectedMonths) : 0;

                $item = new RisItem();
                $item->ppmp_item_id = $id;
                $item->ris_id = $model->id;
                $item->quantity = !empty($selectedMonths) ? $quantity : 0;
                $item->type = 'Original';

                $data[$id] = $item;
            }
        }
        
        $items = PpmpItem::find()->where(['in', 'id', $itemIDs])->all();

        $months = Month::find()->all();
        if (MultipleModel::loadMultiple($data, Yii::$app->request->post())) {
            foreach($data as $itemModel)
            {
                // QUERY ALL MONTHLY TARGET ABOVE 0
                $itemBreakdowns = ItemBreakdown::find()->where(['ppmp_item_id' => $itemModel->ppmp_item_id])->andWhere(['>', 'quantity', 0])->orderBy(['month_id' => SORT_ASC])->all();
                
                if($itemBreakdowns)
                {
                    // QUANTITY INPUT: 10
                    $quantity = $itemModel->quantity;

                    foreach($itemBreakdowns as $breakdown)
                    {
                        // REMAINING: 55
                        $remaining = $breakdown->ppmpItem->getRemainingQuantityPerMonth($breakdown->month_id);
                        // CHECK IF THERE IS REMAINING QUANTITY OR REMAINING = TRUE
                        if($quantity > 0 && $remaining > 0)
                        {
                            // ACTUAL: 10  55 > 10 ? 10 : 55;
                            $actual = $remaining >= $quantity ? $quantity : $remaining;
                            $ppmpItem = PpmpItem::findOne(['id' => $itemModel->ppmp_item_id]);

                            $risItemModel = RisItem::findOne(['ris_id' => $model->id, 'ppmp_item_id' => $itemModel->ppmp_item_id, 'month_id' => $breakdown->month_id, 'type' => $itemModel->type]) ? RisItem::findOne(['ris_id' => $model->id, 'ppmp_item_id' => $itemModel->ppmp_item_id, 'month_id' => $breakdown->month_id, 'type' => $itemModel->type]) : new RisItem();
                            $risItemModel->ris_id = $itemModel->ris_id;
                            $risItemModel->ppmp_item_id = $itemModel->ppmp_item_id;
                            $risItemModel->month_id = $breakdown->month_id;
                            $risItemModel->cost = $ppmpItem->cost;
                            $risItemModel->quantity = $risItemModel->isNewRecord ? $actual : $actual + $risItemModel->quantity;
                            $risItemModel->type = $itemModel->type;
                            if($risItemModel->save())
                            {
                                $risSourceModel = RisSource::findOne(['ris_id' => $model->id, 'ppmp_item_id' => $itemModel->ppmp_item_id, 'ris_item_id' => $risItemModel->id, 'month_id' => $breakdown->month_id, 'type' => $itemModel->type]) ? RisSource::findOne(['ris_id' => $model->id, 'ppmp_item_id' => $itemModel->ppmp_item_id, 'ris_item_id' => $risItemModel->id, 'month_id' => $breakdown->month_id, 'type' => $itemModel->type]) : new RisSource();
                                $risSourceModel->ris_id = $itemModel->ris_id;
                                $risSourceModel->ris_item_id = $risItemModel->id;
                                $risSourceModel->ppmp_item_id = $itemModel->ppmp_item_id;
                                $risSourceModel->month_id = $breakdown->month_id;
                                $risSourceModel->quantity = $risSourceModel->isNewRecord ? $actual : $actual + $risSourceModel->quantity;
                                $risSourceModel->type = $itemModel->type;
                                $risSourceModel->save();

                                $quantity -= $actual;
                            }
                        }
                    }
                }
            }
        }

        return $this->renderAjax('_ris-items', [
            'model' => $model,
            'activity' => $activity,
            'subActivity' => $subActivity,
            'items' => $items,
            'data' => $data,
            'months' => $months,
            'itemIDs' => $itemIDs,
            'selectedItems' => $selectedItems,
            'selectedMonths' => $selectedMonths,
        ]);
    }

    public function actionForProcurement($id)
    {
        $model = $this->findModel($id);

        $content = $this->renderPartial('_for-procurement',[
            'model' => $model
        ]);
        return Json::encode($content);
    }

    public function actionInfo($id)
    {
        $model = $this->findModel($id);

        $office = Office::findOne(Yii::$app->user->identity->userinfo->OFFICE_C);

        if(!Yii::$app->user->can('Administrator') && $office->abbreviation != $model->office_id){ throw new NotFoundHttpException('Page not found'); }

        $entityName = Settings::findOne(['title' => 'Entity Name']);
        $fundCluster = FundCluster::find()->one();

        $specifications = [];

        $items = RisItem::find()
                ->select([
                    'ppmp_ris_item.id as id',
                    'ppmp_ris_item.ris_id as ris_id',
                    'ppmp_ris_item.ppmp_item_id as ppmp_item_id',
                    'ppmp_item.id as stockNo',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code," - ",
                        ppmp_activity.title
                    ) as activity',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code,"-",
                        ppmp_sub_activity.code," - ",
                        ppmp_sub_activity.title
                    ) as prexc',
                    'ppmp_activity.id as activityId',
                    'ppmp_activity.title as activityTitle',
                    'ppmp_sub_activity.id as subActivityId',
                    'ppmp_sub_activity.title as subActivityTitle',
                    'ppmp_item.title as itemTitle',
                    'ppmp_item.unit_of_measure as unitOfMeasure',
                    'ppmp_ppmp_item.cost as cost',
                    'sum(quantity) as total',
                    'ppmp_ris_item.type'
                ])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->andWhere([
                    'ris_id' => $model->id,
                ])
                ->andWhere(['in', 'ppmp_ris_item.type', ['Original', 'Supplemental']])
                ->groupBy(['ppmp_item.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_ris_item.cost'])
                ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'itemTitle' => SORT_ASC])
                ->asArray()
                ->all();

        $risItems = [];

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $risItems[$item['activity']][$item['prexc']][] = $item;

                $spec = RisItemSpec::findOne([
                    'ris_id' => $item['ris_id'],
                    'activity_id' => $item['activityId'],
                    'sub_activity_id' => $item['subActivityId'],
                    'item_id' => $item['stockNo'],
                    'cost' => $item['cost'],
                    'type' => $item['type'],
                ]);

                if($spec)
                {
                    $specifications[$item['id']] = $spec;
                }
            }
        }
        
        $months = Month::find()->all();

        $raItems = RisItem::find()
                ->select([
                    'ppmp_ris_item.id as id',
                    'ppmp_ris_item.month_id as month_id',
                    'ppmp_item.id as stockNo',
                    'ppmp_ris_item.ppmp_item_id as ppmp_item_id',
                    'ppmp_activity.id as activityId',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code," - ",
                        ppmp_activity.title
                    ) as activity',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code,"-",
                        ppmp_sub_activity.code," - ",
                        ppmp_sub_activity.title
                    ) as prexc',
                    'ppmp_item.title as itemTitle',
                    'sum(quantity) as total'
                ])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->andWhere([
                    'ris_id' => $model->id,
                    'ppmp_ris_item.type' => 'Realigned',
                ])
                ->groupBy(['ppmp_item.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_ris_item.month_id'])
                ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'itemTitle' => SORT_ASC])
                ->asArray()
                ->all();
        
        $realignedItems = [];

        if(!empty($raItems))
        {
            foreach($raItems as $item)
            {
                $realignedItems[$item['activity']][$item['prexc']][$item['itemTitle']][$item['month_id']] = $item['total'];
            }
        }

        $types = RisItem::find()->select(['distinct(type) as type'])->andWhere(['ris_id' => $model->id])->asArray()->all();
        $types = ArrayHelper::map($types, 'type', 'type');

        $comment = '';

        if(!empty($types))
        {
            if(count($types) > 1)
            {
                $comment = 2;
            }else{
                if(in_array('Original', $types))
                {
                    $comment = 1;
                }else{
                    $comment = 2;
                }
            }
        }

        return $this->render('_info', [
            'model' => $model,
            'entityName' => $entityName['value'],
            'fundClusterName' => $fundCluster->title,
            'risItems' => $risItems,
            'realignedItems' => $realignedItems,
            'months' => $months,
            'comment' => $comment,
            'specifications' => $specifications,
        ]);
    }

    public function actionDownload($type, $id)
    {
        $model = $this->findModel($id);
        $entityName = Settings::findOne(['title' => 'Entity Name']);
        $fundCluster = FundCluster::findOne($model->fund_cluster_id);

        $specifications = [];

        $items = RisItem::find()
                ->select([
                    'ppmp_ris_item.id as id',
                    'ppmp_ris_item.ris_id as ris_id',
                    'ppmp_item.id as stockNo',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code," - ",
                        ppmp_activity.title
                    ) as activity',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code,"-",
                        ppmp_sub_activity.code," - ",
                        ppmp_sub_activity.title
                    ) as prexc',
                    'ppmp_activity.id as activityId',
                    'ppmp_activity.title as activityTitle',
                    'ppmp_sub_activity.id as subActivityId',
                    'ppmp_sub_activity.title as subActivityTitle',
                    'ppmp_item.title as itemTitle',
                    'ppmp_item.unit_of_measure as unitOfMeasure',
                    'ppmp_ppmp_item.cost as cost',
                    'sum(quantity) as total',
                    'ppmp_ris_item.type'
                ])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->andWhere([
                    'ris_id' => $model->id,
                ])
                ->andWhere(['in', 'ppmp_ris_item.type', ['Original', 'Supplemental']])
                ->groupBy(['ppmp_item.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_ris_item.cost'])
                ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'itemTitle' => SORT_ASC])
                ->asArray()
                ->all();

        $risItems = [];

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $risItems[$item['activity']][$item['prexc']][] = $item;

                $spec = RisItemSpec::findOne([
                    'ris_id' => $item['ris_id'],
                    'activity_id' => $item['activityId'],
                    'sub_activity_id' => $item['subActivityId'],
                    'item_id' => $item['stockNo'],
                    'cost' => $item['cost'],
                    'type' => $item['type'],
                ]);

                if($spec)
                {
                    $specifications[$item['id']] = $spec;
                }
            }
        }
        
        $months = Month::find()->all();

        $raItems = RisItem::find()
                ->select([
                    'ppmp_ris_item.id as id',
                    'ppmp_ris_item.month_id as month_id',
                    'ppmp_item.id as stockNo',
                    'ppmp_activity.id as activityId',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code," - ",
                        ppmp_activity.title
                    ) as activity',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code,"-",
                        ppmp_sub_activity.code," - ",
                        ppmp_sub_activity.title
                    ) as prexc',
                    'ppmp_item.title as itemTitle',
                    'sum(quantity) as total'
                ])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->andWhere([
                    'ris_id' => $model->id,
                    'ppmp_ris_item.type' => 'Realigned',
                ])
                ->groupBy(['ppmp_item.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_ris_item.month_id'])
                ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'itemTitle' => SORT_ASC])
                ->asArray()
                ->all();
        
        $realignedItems = [];

        if(!empty($raItems))
        {
            foreach($raItems as $item)
            {
                $realignedItems[$item['activity']][$item['prexc']][$item['itemTitle']][$item['month_id']] = $item['total'];
            }
        }

        $types = RisItem::find()->select(['distinct(type) as type'])->andWhere(['ris_id' => $model->id])->asArray()->all();
        $types = ArrayHelper::map($types, 'type', 'type');

        $comment = '';

        if(!empty($types))
        {
            if(count($types) > 1)
            {
                $comment = 2;
            }else{
                if(in_array('Original', $types))
                {
                    $comment = 1;
                }else{
                    $comment = 2;
                }
            }
        }

        $filename = 'RIS No. '.$model->ris_no;

        if($type == 'excel')
        {
            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=".$filename.".xls");
            return $this->renderPartial('file', [
                'action' => 'pdf',
                'model' => $model,
                'entityName' => $entityName['value'],
                'fundClusterName' => $fundCluster->title,
                'risItems' => $risItems,
                'realignedItems' => $realignedItems,
                'months' => $months,
                'comment' => $comment,
                'specifications' => $specifications
            ]);
        }
        else if($type == 'pdf')
        {
            $content = $this->renderPartial('file', [
                'action' => 'pdf',
                'model' => $model,
                'entityName' => $entityName['value'],
                'fundClusterName' => $fundCluster->title,
                'risItems' => $risItems,
                'realignedItems' => $realignedItems,
                'months' => $months,
                'comment' => $comment,
                'specifications' => $specifications
            ]);

            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE,
                'format' => Pdf::FORMAT_LEGAL, 
                'orientation' => Pdf::ORIENT_PORTRAIT, 
                'destination' => Pdf::DEST_DOWNLOAD, 
                'filename' => $filename.'.pdf', 
                'content' => $content,  
                'marginLeft' => 11.4,
                'marginRight' => 11.4,
                'cssInline' => '*{ font-family: "Tahoma"; }
                                p{ font-size: 10px; }
                                table{
                                    font-family: "Tahoma";
                                    border-collapse: collapse;
                                }
                                thead{
                                    font-size: 12px;
                                    text-align: center;
                                }
                            
                                td{
                                    font-size: 10px;
                                    border: 1px solid black;
                                    padding: 3px 3px;
                                }
                            
                                th{
                                    font-size: 10px;
                                    text-align: center;
                                    border: 1px solid black;
                                    padding: 3px 3px;
                                }
                                ', 
                ]);
        
                $response = Yii::$app->response;
                $response->format = \yii\web\Response::FORMAT_RAW;
                $headers = Yii::$app->response->headers;
                $headers->add('Content-Type', 'application/pdf');
                return $pdf->render();
        }

    }

    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $entityName = Settings::findOne(['title' => 'Entity Name']);
        $fundCluster = FundCluster::findOne($model->fund_cluster_id);

        $specifications = [];

        $items = RisItem::find()
                ->select([
                    'ppmp_ris_item.id as id',
                    'ppmp_ris_item.ris_id as ris_id',
                    'ppmp_item.id as stockNo',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code," - ",
                        ppmp_activity.title
                    ) as activity',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code,"-",
                        ppmp_sub_activity.code," - ",
                        ppmp_sub_activity.title
                    ) as prexc',
                    'ppmp_activity.id as activityId',
                    'ppmp_activity.title as activityTitle',
                    'ppmp_sub_activity.id as subActivityId',
                    'ppmp_sub_activity.title as subActivityTitle',
                    'ppmp_item.title as itemTitle',
                    'ppmp_item.unit_of_measure as unitOfMeasure',
                    'ppmp_ppmp_item.cost as cost',
                    'sum(quantity) as total',
                    'ppmp_ris_item.type'
                ])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->andWhere([
                    'ris_id' => $model->id,
                ])
                ->andWhere(['in', 'ppmp_ris_item.type', ['Original', 'Supplemental']])
                ->groupBy(['ppmp_item.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_ris_item.cost'])
                ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'itemTitle' => SORT_ASC])
                ->asArray()
                ->all();

        $risItems = [];

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $risItems[$item['activity']][$item['prexc']][] = $item;

                $spec = RisItemSpec::findOne([
                    'ris_id' => $item['ris_id'],
                    'activity_id' => $item['activityId'],
                    'sub_activity_id' => $item['subActivityId'],
                    'item_id' => $item['stockNo'],
                    'cost' => $item['cost'],
                    'type' => $item['type'],
                ]);

                if($spec)
                {
                    $specifications[$item['id']] = $spec;
                }
            }
        }
        
        $months = Month::find()->all();

        $raItems = RisItem::find()
                ->select([
                    'ppmp_ris_item.id as id',
                    'ppmp_ris_item.month_id as month_id',
                    'ppmp_item.id as stockNo',
                    'ppmp_activity.id as activityId',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code," - ",
                        ppmp_activity.title
                    ) as activity',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code,"-",
                        ppmp_sub_activity.code," - ",
                        ppmp_sub_activity.title
                    ) as prexc',
                    'ppmp_item.title as itemTitle',
                    'sum(quantity) as total'
                ])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->andWhere([
                    'ris_id' => $model->id,
                    'ppmp_ris_item.type' => 'Realigned',
                ])
                ->groupBy(['ppmp_item.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_ris_item.month_id'])
                ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'itemTitle' => SORT_ASC])
                ->asArray()
                ->all();
        
        $realignedItems = [];

        if(!empty($raItems))
        {
            foreach($raItems as $item)
            {
                $realignedItems[$item['activity']][$item['prexc']][$item['itemTitle']][$item['month_id']] = $item['total'];
            }
        }

        $types = RisItem::find()->select(['distinct(type) as type'])->andWhere(['ris_id' => $model->id])->asArray()->all();
        $types = ArrayHelper::map($types, 'type', 'type');

        $comment = '';

        if(!empty($types))
        {
            if(count($types) > 1)
            {
                $comment = 2;
            }else{
                if(in_array('Original', $types))
                {
                    $comment = 1;
                }else{
                    $comment = 2;
                }
            }
        }

        return $this->renderAjax('file', [
            'action' => 'print',
            'model' => $model,
            'entityName' => $entityName['value'],
            'fundClusterName' => $fundCluster->title,
            'risItems' => $risItems,
            'realignedItems' => $realignedItems,
            'months' => $months,
            'comment' => $comment,
            'specifications' => $specifications,
        ]);

    }

    public function actionLoadRisItemsTotal($id)
    {
        $model = $this->findModel($id);

        return $model->getRisItems()->count();
    }

    /**
     * Creates a new Ris model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Ris();

        $approver = Settings::findOne(['title' => 'RIS Approver']);
        $model->approved_by = $approver ? $approver->value : '';

        $model->scenario = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('ProcurementStaff')) ? 'isAdmin' : 'isUser';

        $ppmps = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('ProcurementStaff')) ? [] : Ppmp::find()
        ->joinWith('office')
        ->where(['stage' => 'Final'])
        ->andWhere(['office_id' => Yii::$app->user->identity->userinfo->OFFICE_C])
        ->orderBy(['year' => SORT_DESC])
        ->all();
        
        $ppmps = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('ProcurementStaff')) ? ArrayHelper::map($ppmps, 'id', 'title') : ArrayHelper::map($ppmps, 'id', 'year');

        $offices = Office::find()->all();
        $offices = ArrayHelper::map($offices, 'abbreviation', 'abbreviation');

        $fundSources = FundSource::find()->all();
        $fundSources = ArrayHelper::map($fundSources, 'id', 'code');

        $fundClusters = FundCluster::find()->all();
        $fundClusters = ArrayHelper::map($fundClusters, 'id', 'title');

        $signatories = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('ProcurementStaff')) ? Signatory::find()->all() : Signatory::find()->where(['office_id' => Yii::$app->user->identity->userinfo->OFFICE_C])->all();
        $signatories = ArrayHelper::map($signatories, 'emp_id', 'name');

        $types = [
            'Supply' => 'Goods',
            'Service' => 'Service/Contract',
        ];

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            $lastRis = Ris::find()->orderBy(['id' => SORT_DESC])->one();
            $userOffice = Office::findOne(['id' => Yii::$app->user->identity->userinfo->OFFICE_C]);
            $lastNumber = $lastRis ? intval(substr($lastRis->ris_no, -3)) : '001';
            $ris_no = $lastRis ? substr(date("Y"), -2).'-'.date("md").'-'.str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT) : substr(date("Y"), -2).'-'.date("md").$lastNumber;
            $model->ris_no = $ris_no;
            $model->office_id = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('ProcurementStaff')) ? $model->office_id : $userOffice->abbreviation;
            $model->date_requested = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('ProcurementStaff')) ? $model->date_requested : date("Y-m-d"); 
            $model->created_by = Yii::$app->user->identity->userinfo->EMP_N; 
            $model->date_created = date("Y-m-d"); 
            $model->save(false);

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('create', [
            'model' => $model,
            'offices' => $offices,
            'ppmps' => $ppmps,
            'fundSources' => $fundSources,
            'fundClusters' => $fundClusters,
            'signatories' => $signatories,
            'types' => $types
        ]);
    }

    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'Approve';

        $approver = Settings::findOne(['title' => 'RIS Approver']);
        
        if ($model->load(Yii::$app->request->post())) {
            $model->approved_by = $approver ? $approver->value : '';
            $model->disapproved_by = null;
            $model->date_disapproved = null;
            if($model->save(false))
            {
                $status = new Transaction();
                $status->actor = Yii::$app->user->identity->userinfo->EMP_N;
                $status->model = 'Ris';
                $status->model_id = $model->id;
                $status->status = 'Approved';
                if($status->save(false))
                {
                    \Yii::$app->getSession()->setFlash('success', 'RIS approved');
                    return $this->redirect(['info', 'id' => $model->id]);
                }
            }
        }

        return $this->renderAjax('_approve', [
            'model' => $model,
        ]);
    }

    public function actionDisapprove($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'Disapprove';

        $signatories = Signatory::find()->where(['<>', 'emp_id', $model->requested_by])->all();
        $signatories = ArrayHelper::map($signatories, 'emp_id', 'name');

        if ($model->load(Yii::$app->request->post())) {
            $model->approved_by = null;
            $model->date_approved = null;
            if($model->save(false))
            {
                $status = new Transaction();
                $status->actor = Yii::$app->user->identity->userinfo->EMP_N;
                $status->model = 'Ris';
                $status->model_id = $model->id;
                $status->status = 'Disapproved';
                if($status->save())
                {
                    \Yii::$app->getSession()->setFlash('danger', 'RIS disapproved');
                    return $this->redirect(['info', 'id' => $model->id]);
                }
            }
        }

        return $this->renderAjax('_disapprove', [
            'model' => $model,
            'signatories' => $signatories,
        ]);
    }

    /**
     * Updates an existing Ris model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->scenario = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('ProcurementStaff')) ? 'isAdmin' : 'isUser';

        $ppmps = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('ProcurementStaff')) ? Ppmp::find()
        ->joinWith('office')
        ->where(['stage' => 'Final'])
        ->orderBy(['year' => SORT_DESC])
        ->all() : Ppmp::find()
        ->joinWith('office')
        ->where(['stage' => 'Final'])
        ->andWhere(['office_id' => Yii::$app->user->identity->userinfo->OFFICE_C])
        ->orderBy(['year' => SORT_DESC])
        ->all();
        
        $ppmps = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('ProcurementStaff')) ? ArrayHelper::map($ppmps, 'id', 'title') : ArrayHelper::map($ppmps, 'id', 'year');

        $offices = Office::find()->all();
        $offices = ArrayHelper::map($offices, 'abbreviation', 'abbreviation');

        $fundSources = FundSource::find()->all();
        $fundSources = ArrayHelper::map($fundSources, 'id', 'code');

        $fundClusters = FundCluster::find()->all();
        $fundClusters = ArrayHelper::map($fundClusters, 'id', 'title');

        $signatories = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('ProcurementStaff')) ? Signatory::find()->all() : Signatory::find()->where(['office_id' => Yii::$app->user->identity->userinfo->OFFICE_C])->all();
        $signatories = ArrayHelper::map($signatories, 'emp_id', 'name');

        $types = [
            'Supply' => 'Goods',
            'Service' => 'Service/Contract',
        ];

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save(false)) {
            \Yii::$app->getSession()->setFlash('success', 'Record Updated');
            return $this->redirect(['info', 'id' => $model->id]);
        }

        return $this->renderAjax('update', [
            'model' => $model,
            'ppmps' => $ppmps,
            'offices' => $offices,
            'fundSources' => $fundSources,
            'fundClusters' => $fundClusters,
            'signatories' => $signatories,
            'types' => $types
        ]);
    }

    /**
     * Deletes an existing Ris model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $suppItems = RisItem::find()
                    ->select(['ppmp_item_id'])
                    ->where([
                        'ris_id' => $model->id,
                        'type' => 'Supplemental'
                    ])
                    ->asArray()
                    ->all();
        
        $suppItems = ArrayHelper::map($suppItems, 'ppmp_item_id', 'ppmp_item_id');

        $ppmpItems = PpmpItem::find()->where(['in', 'id', $suppItems])->all();
        
        if($model->delete())
        {
            $statuses = Transaction::deleteAll(['model' => 'Ris', 'model_id' => $id]);

            if($ppmpItems)
            {
                foreach($ppmpItems as $item)
                {
                    $item->delete();
                }
            }

            \Yii::$app->getSession()->setFlash('success', 'Record Deleted');
            return $this->redirect(['index']);

        }

    }

    public function actionUpdateItem($id, $activity_id, $sub_activity_id, $item_id, $cost, $type)
    {
        $model = $this->findModel($id);
        $activity = Activity::findOne($activity_id);
        $subActivity = SubActivity::findOne($sub_activity_id);
        $item = Item::findOne($item_id);

        $items = RisItem::find()
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->andWhere([
                    'ris_id' => $model->id,
                    'ppmp_activity.id' => $activity_id,
                    'ppmp_sub_activity.id' => $sub_activity_id,
                    'ppmp_item.id' => $item_id,
                    'ppmp_ris_item.cost' => $cost,
                    'ppmp_ris_item.type' => $type
                ])
                ->all();
        
        $data = [];

        if($items)
        {
            foreach($items as $i)
            {
                $data[$i->id] = $i;
            }
        }

        if (MultipleModel::loadMultiple($data, Yii::$app->request->post()) && MultipleModel::validateMultiple($data)) {
            foreach($data as $itemModel)
            {
                if($itemModel->save())
                {
                    $risSourceModel = RisSource::findOne(['ris_item_id' => $itemModel->id, 'month_id' => $itemModel->month_id]);
                    $risSourceModel->quantity = $itemModel->quantity;
                    $risSourceModel->save();

                    if($type == 'Supplemental')
                    {
                        $itemBreakdownModel = ItemBreakdown::find()
                                        ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ppmp_item_breakdown.ppmp_item_id')
                                        ->where([
                                            'ppmp_item_id' => $itemModel->ppmp_item_id, 
                                            'month_id' => $itemModel->month_id,
                                            'type' => 'Supplemental'
                                        ])
                                        ->one();
                        $itemBreakdownModel->quantity = $itemModel->quantity;
                        $itemBreakdownModel->save();
                    }
                }
            }

            \Yii::$app->getSession()->setFlash('success', 'Item Updated');
            switch($type)
            {
                case 'Original':
                    return $this->redirect(['view', 'id' => $model->id]);
                    break;
                case 'Supplemental':
                    return $this->redirect(['supplemental', 'id' => $model->id]);
                    break;
                case 'Realigned':
                    return $this->redirect(['realign', 'id' => $model->id]);
                    break;
            }
        }
        
        return $this->renderAjax('_update-item', [
            'model' => $model,
            'activity' => $activity,
            'subActivity' => $subActivity,
            'items' => $items,
            'item' => $item,
            'data' => $data,
        ]);
        
    }

    public function actionDeleteItem($id, $activity_id, $sub_activity_id, $item_id, $cost, $type)
    {
        $model = $this->findModel($id);

        $items = RisItem::find()
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->andWhere([
                    'ris_id' => $model->id,
                    'ppmp_activity.id' => $activity_id,
                    'ppmp_sub_activity.id' => $sub_activity_id,
                    'ppmp_item.id' => $item_id,
                    'ppmp_ris_item.cost' => $cost,
                    'ppmp_ris_item.type' => $type,
                ])
                ->all();
        
        if($items)
        {
            foreach($items as $item)
            {
                if($type == 'Supplemental')
                {
                    $itemBreakdownModel = ItemBreakdown::find()
                                        ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ppmp_item_breakdown.ppmp_item_id')
                                        ->where([
                                            'ppmp_item_id' => $item->ppmp_item_id, 
                                            'month_id' => $item->month_id,
                                            'type' => 'Supplemental'
                                        ])
                                        ->one();
                    $itemBreakdownModel->delete();

                    $ppmpItemModel = PpmpItem::findOne($item->ppmp_item_id);
                    if($ppmpItemModel)
                    {
                        if($ppmpItemModel->quantities == 0)
                        {
                            $ppmpItemModel->delete();
                        }
                    }
                }

                $item->delete();
            }
        }

        \Yii::$app->getSession()->setFlash('success', 'Item Removed');
        switch($type)
        {
            case 'Original':
                return $this->redirect(['view', 'id' => $model->id]);
                break;
            case 'Supplemental':
                return $this->redirect(['supplemental', 'id' => $model->id]);
                break;
            case 'Realigned':
                return $this->redirect(['realign', 'id' => $model->id]);
                break;
        }
    }

    public function actionForApproval($id)
    {
        $model = $this->findModel($id);

        $status = new Transaction();
        $status->actor = Yii::$app->user->identity->userinfo->EMP_N;
        $status->model = 'Ris';
        $status->model_id = $model->id;
        $status->status = 'For Approval';
        if($status->save())
        {
            \Yii::$app->getSession()->setFlash('success', 'RIS is sent for approval');
            return $this->redirect(['info', 'id' => $model->id]);
        }
    }

    public function actionForRevision($id)
    {
        $model = $this->findModel($id);
        $status = new Transaction();
        $status->scenario = 'RIS For Revision';
        $status->actor = Yii::$app->user->identity->userinfo->EMP_N;
        $status->model = 'Ris';
        $status->model_id = $model->id;
        $status->status = 'For Revision';

        if ($status->load(Yii::$app->request->post()) && $status->save()) {
            
            \Yii::$app->getSession()->setFlash('success', 'RIS is sent for revision');
            return $this->redirect(['info', 'id' => $model->id]);
        }

        return $this->renderAjax('_for-revision', [
            'status' => $status,
        ]);
    }

    public function actionOriginal($id)
    {
        $model = $this->findModel($id);

        $originalItems = [];
        $specifications = [];

        $origItems = RisItem::find()
                ->select([
                    'ppmp_ris_item.id as id',
                    'ppmp_ris_item.ris_id as ris_id',
                    'ppmp_item.id as stockNo',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code," - ",
                        ppmp_activity.title
                    ) as activity',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code,"-",
                        ppmp_sub_activity.code," - ",
                        ppmp_sub_activity.title
                    ) as prexc',
                    'ppmp_activity.id as activityId',
                    'ppmp_activity.title as activityTitle',
                    'ppmp_sub_activity.id as subActivityId',
                    'ppmp_sub_activity.title as subActivityTitle',
                    'ppmp_item.title as itemTitle',
                    'ppmp_item.unit_of_measure as unitOfMeasure',
                    'ppmp_ris_item.cost as cost',
                    'ppmp_ris_item.type as type',
                    'sum(quantity) as total'
                ])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_ris_item_spec', '
                    ppmp_ris_item_spec.ris_id = ppmp_ris_item.ris_id and
                    ppmp_ris_item_spec.activity_id = ppmp_ppmp_item.activity_id and
                    ppmp_ris_item_spec.sub_activity_id = ppmp_ppmp_item.sub_activity_id and
                    ppmp_ris_item_spec.item_id = ppmp_ppmp_item.item_id and
                    ppmp_ris_item_spec.cost = ppmp_ris_item.cost and
                    ppmp_ris_item_spec.type = ppmp_ris_item.type
                ')
                ->andWhere([
                    'ppmp_ris_item.ris_id' => $model->id,
                    'ppmp_ris_item.type' => 'Original'
                ])
                ->groupBy(['ppmp_item.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_ris_item.cost'])
                ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'itemTitle' => SORT_ASC])
                ->asArray()
                ->all();
        
        if(!empty($origItems))
        {
            foreach($origItems as $item)
            {
                $originalItems[$item['activity']][$item['prexc']][] = $item;
            }
        }

        if(!empty($originalItems))
        {
            foreach($originalItems as $activityItems)
            {
                if(!empty($activityItems))
                {
                    foreach($activityItems as $subActivityItems)
                    {
                        if(!empty($subActivityItems))
                        {
                            foreach($subActivityItems as $item)
                            {
                                $spec = RisItemSpec::findOne([
                                    'ris_id' => $item['ris_id'],
                                    'activity_id' => $item['activityId'],
                                    'sub_activity_id' => $item['subActivityId'],
                                    'item_id' => $item['stockNo'],
                                    'cost' => $item['cost'],
                                    'type' => $item['type'],
                                ]);

                                if($spec)
                                {
                                    $specifications[$item['id']] = $spec;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->renderAjax('_original', [
            'model' => $model,
            'originalItems' => $originalItems,
            'specifications' => $specifications,
        ]);
    }

    public function actionSupplemental($id)
    {
        $model = $this->findModel($id);

        $itemModel = new PpmpItem();
        $itemModel->ppmp_id = $model->ppmp->id;
        $itemModel->fund_source_id = $model->fund_source_id;
        $itemModel->type = 'Supplemental';
        $itemModel->scenario = 'Supplemental';

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

        $activities = ArrayHelper::map($activities, 'id', 'text', 'groupTitle');

        $subActivities = [];

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

        $supplementalItems = [];

        $specifications = [];

        $suppItems = RisItem::find()
                ->select([
                    'ppmp_ris_item.id as id',
                    'ppmp_ris_item.ris_id as ris_id',
                    'ppmp_item.id as stockNo',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code," - ",
                        ppmp_activity.title
                    ) as activity',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code,"-",
                        ppmp_sub_activity.code," - ",
                        ppmp_sub_activity.title
                    ) as prexc',
                    'ppmp_activity.id as activityId',
                    'ppmp_activity.title as activityTitle',
                    'ppmp_sub_activity.id as subActivityId',
                    'ppmp_sub_activity.title as subActivityTitle',
                    'ppmp_item.title as itemTitle',
                    'ppmp_item.unit_of_measure as unitOfMeasure',
                    'ppmp_ris_item.cost as cost',
                    'sum(quantity) as total',
                    'ppmp_ris_item.type'
                ])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->andWhere([
                    'ppmp_ris_item.ris_id' => $model->id,
                    'ppmp_ris_item.type' => 'Supplemental'
                ])
                ->groupBy(['ppmp_item.id', 'ppmp_activity.id', 'ppmp_sub_activity.id'])
                ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'itemTitle' => SORT_ASC])
                ->asArray()
                ->all();
        
        if(!empty($suppItems))
        {
            foreach($suppItems as $item)
            {
                $supplementalItems[$item['activity']][$item['prexc']][] = $item;
            }
        }

        if(!empty($supplementalItems))
        {
            foreach($supplementalItems as $activityItems)
            {
                if(!empty($activityItems))
                {
                    foreach($activityItems as $subActivityItems)
                    {
                        if(!empty($subActivityItems))
                        {
                            foreach($subActivityItems as $item)
                            {
                                $spec = RisItemSpec::findOne([
                                    'ris_id' => $item['ris_id'],
                                    'activity_id' => $item['activityId'],
                                    'sub_activity_id' => $item['subActivityId'],
                                    'item_id' => $item['stockNo'],
                                    'cost' => $item['cost'],
                                    'type' => $item['type'],
                                ]);

                                if($spec)
                                {
                                    $specifications[$item['id']] = $spec;
                                }
                            }
                        }
                    }
                }
            }
        }

        if($itemModel->load(Yii::$app->request->post()) && MultipleModel::loadMultiple($itemBreakdowns, Yii::$app->request->post()) && MultipleModel::validateMultiple($itemBreakdowns))
        {
            if($itemModel->save())
            {
                /* $cost = new ItemCost();
                $cost->item_id = $itemModel->item_id;
                $cost->cost = $itemModel->cost;
                $cost->datetime = date("Y-m-d H:i:s");
                $cost->source_model = 'RisItem';
                $cost->source_id = $itemModel->id;
                $cost->save(); */

                if($itemBreakdowns)
                {
                    foreach($itemBreakdowns as $itemBreakdown)
                    {
                        $itemBreakdown->ppmp_item_id = $itemModel->id;
                        $itemBreakdown->quantity = !empty($itemBreakdown->quantity) ? $itemBreakdown->quantity : 0;
                        if($itemBreakdown->quantity > 0 && $itemBreakdown->save())
                        {
                            $risItem = new RisItem();
                            $risItem->ris_id = $model->id;
                            $risItem->ppmp_item_id = $itemModel->id;
                            $risItem->month_id = $itemBreakdown->month_id;
                            $risItem->quantity = $itemBreakdown->quantity;
                            $risItem->cost = $itemModel->cost;
                            $risItem->type = 'Supplemental';
                            if($risItem->save())
                            {
                                $risSource = new RisSource();
                                $risSource->ris_id = $model->id;
                                $risSource->ris_item_id = $risItem->id;
                                $risSource->ppmp_item_id = $itemModel->id;
                                $risSource->month_id = $risItem->month_id;
                                $risSource->quantity = $risItem->quantity;
                                $risItem->cost = $itemModel->cost;
                                $risSource->type = 'Supplemental';
                                $risSource->save();
                            }
                        }
                    }
                }
            }

            \Yii::$app->getSession()->setFlash('success', 'Supplemental Item Saved');
            return $this->redirect(['supplemental', 'id' => $model->id]);
        }

        return $this->render('_supplemental', [
            'model' => $model,
            'itemModel' => $itemModel,
            'activities' => $activities,
            'subActivities' => $subActivities,
            'objects' => $objects,
            'items' => $items,
            'supplementalItems' => $supplementalItems,
            'months' => $months,
            'itemBreakdowns' => $itemBreakdowns,
            'specifications' => $specifications
        ]);
    }

    public function actionRealign($id)
    {
        $model = $this->findModel($id);

        $appropriationItemModel = new AppropriationItem();
        $appropriationItemModel->scenario = 'loadItemsInRis';

        $fundedActivities = PpmpItem::find()->select(['distinct(activity_id) as id'])->where(['ppmp_id' => $model->ppmp->id])->all();
        $fundedActivities = ArrayHelper::map($fundedActivities, 'id', 'id');

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
                    ->andWhere(['in', 'ppmp_activity.id', $fundedActivities])
                    ->asArray()
                    ->all();
        
        $activities = ArrayHelper::map($activities, 'id', 'text', 'groupTitle');
                        
        $subActivities = [];

        $items = [];

        return $this->render('_realign', [
            'model' => $model,
            'appropriationItemModel' => $appropriationItemModel,
            'activities' => $activities,
            'subActivities' => $subActivities,
            'items' => $items,
        ]);
    }

    public function actionRealignItems($id, $activity_id, $sub_activity_id, $item_id)
    {
        $model = $this->findModel($id);
        $ppmp = $model->ppmp;
        $activity = Activity::findOne($activity_id);
        $subActivity = SubActivity::findOne($sub_activity_id);
        $selectedItems = json_decode($item_id, true);

        $forContractItems = ForContractItem::find()->select(['item_id'])->asArray()->all();
        $forContractItems = ArrayHelper::map($forContractItems, 'item_id', 'item_id');

        $itemIDs = $model->type == 'Supply' ? ItemBreakdown::find()
                ->select(['ppmp_item_id'])
                ->leftJoin('ppmp_ppmp_item i', 'i.id = ppmp_ppmp_item_breakdown.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = i.item_id')
                ->andWhere([
                    'i.ppmp_id' => $ppmp->id,
                    'i.activity_id' => $activity->id,
                    'i.sub_activity_id' => $subActivity->id,
                    'i.fund_source_id' => $model->fund_source_id,
                    'i.type' => 'Original'
                ])
                ->andWhere(['>', 'quantity', 0])
                ->andWhere(['in', 'ppmp_item.id', $selectedItems])
                ->asArray()
                ->all() : ItemBreakdown::find()
                ->select(['ppmp_item_id'])
                ->leftJoin('ppmp_ppmp_item i', 'i.id = ppmp_ppmp_item_breakdown.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = i.item_id')
                ->andWhere([
                    'i.ppmp_id' => $ppmp->id,
                    'i.activity_id' => $activity->id,
                    'i.sub_activity_id' => $subActivity->id,
                    'i.fund_source_id' => $model->fund_source_id,
                    'i.type' => 'Original'
                ])
                ->andWhere(['>', 'quantity', 0])
                ->andWhere(['in', 'ppmp_item.id', $selectedItems])
                ->asArray()
                ->all();
        
        $itemIDs = ArrayHelper::map($itemIDs, 'ppmp_item_id', 'ppmp_item_id');

        $data = [];

        if(!empty($itemIDs))
        {
            foreach($itemIDs as $id)
            {
                $item = new RisItem();
                $item->ris_id = $model->id;
                $item->ppmp_item_id = $id;
                $item->type = 'Realigned';

                $data[$id] = $item;
            }
        }
        
        $items = PpmpItem::find()->where(['in', 'id', $itemIDs])->all();

        $months = Month::find()->all();

        if (MultipleModel::loadMultiple($data, Yii::$app->request->post())) {
            foreach($data as $itemModel)
            {
                if($itemModel->quantity >= 1)
                {
                    // QUERY ALL MONTHLY TARGET ABOVE 0
                    $itemBreakdowns = ItemBreakdown::find()->where(['ppmp_item_id' => $itemModel->ppmp_item_id])->andWhere(['>', 'quantity', 0])->orderBy(['month_id' => SORT_ASC])->all();
                    
                    if($itemBreakdowns)
                    {
                        // QUANTITY INPUT: 2
                        $quantity = $itemModel->quantity;

                        foreach($itemBreakdowns as $breakdown)
                        {
                            // REMAINING: 3
                            $remaining = $breakdown->ppmpItem->getRemainingQuantityPerMonth($breakdown->month_id);
                            // CHECK IF THERE IS REMAINING QUANTITY OR REMAINING: TRUE
                            if($quantity > 0 && $remaining > 0)
                            {
                                // 3 > 2 ? 2 : 3;
                                $actual = $remaining >= $quantity ? $quantity : $remaining;

                                $risItemModel = new RisItem();
                                $risItemModel->ris_id = $itemModel->ris_id;
                                $risItemModel->ppmp_item_id = $itemModel->ppmp_item_id;
                                $risItemModel->month_id = $breakdown->month_id;
                                $risItemModel->quantity = $actual;
                                $risItemModel->cost = $breakdown->ppmpItem->cost;
                                $risItemModel->type = $itemModel->type;
                                if($risItemModel->save())
                                {
                                    $risSourceModel =  new RisSource();
                                    $risSourceModel->ris_id = $itemModel->ris_id;
                                    $risSourceModel->ris_item_id = $risItemModel->id;
                                    $risSourceModel->ppmp_item_id = $itemModel->ppmp_item_id;
                                    $risSourceModel->month_id = $breakdown->month_id;
                                    $risSourceModel->quantity = $actual;
                                    $risSourceModel->type = $itemModel->type;
                                    $risSourceModel->save();

                                    $quantity -= $actual;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->renderAjax('_ris-realign-items', [
            'model' => $model,
            'activity' => $activity,
            'subActivity' => $subActivity,
            'items' => $items,
            'data' => $data,
            'months' => $months,
            'itemIDs' => $itemIDs,
            'selectedItems' => $selectedItems
        ]);
    }

    public function actionRealigned($id)
    {
        $model = $this->findModel($id);

        $realignedItems = [];
        $specifications = [];

        $raItems = RisItem::find()
                ->select([
                    'ppmp_ris_item.id as id',
                    'ppmp_item.id as stockNo',
                    'ppmp_activity.id as activityId',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code," - ",
                        ppmp_activity.title
                    ) as activity',
                    'concat(
                        ppmp_cost_structure.code,"",
                        ppmp_organizational_outcome.code,"",
                        ppmp_program.code,"",
                        ppmp_sub_program.code,"",
                        ppmp_identifier.code,"",
                        ppmp_pap.code,"000-",
                        ppmp_activity.code,"-",
                        ppmp_sub_activity.code," - ",
                        ppmp_sub_activity.title
                    ) as prexc',
                    'ppmp_sub_activity.id as subActivityId',
                    'ppmp_sub_activity.title as subActivityTitle',
                    'ppmp_item.title as itemTitle',
                    'ppmp_item.unit_of_measure as unitOfMeasure',
                    'ppmp_ris_item.cost as cost',
                    'sum(quantity) as total'
                ])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->andWhere([
                    'ppmp_ris_item.ris_id' => $model->id,
                    'ppmp_ris_item.type' => 'Realigned'
                ])
                ->groupBy(['ppmp_item.id', 'ppmp_activity.id', 'ppmp_ris_item.cost'])
                ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'itemTitle' => SORT_ASC])
                ->asArray()
                ->all();
        
        if(!empty($raItems))
        {
            foreach($raItems as $item)
            {
                $realignedItems[$item['activity']][$item['prexc']][] = $item;
            }
        }

        return $this->renderAjax('_realigned', [
            'model' => $model,
            'realignedItems' => $realignedItems,
            'specifications' => $specifications,
        ]);
    }

    public function actionCreateSpecification($id, $activity_id, $sub_activity_id, $item_id, $cost, $type)
    {
        $model = $this->findModel($id);
        $activity = Activity::findOne($activity_id);
        $subActivity = SubActivity::findOne($sub_activity_id);
        $item = Item::findOne($item_id);

        $spec = new RIsItemSpec();
        $spec->ris_id = $model->id;
        $spec->activity_id = $activity->id;
        $spec->sub_activity_id = $subActivity->id;
        $spec->item_id = $item->id;
        $spec->cost = $cost;
        $spec->type = $type;

        $specValues = [new RisItemSpecValue];

        if($spec->load(Yii::$app->request->post()))
        {
            $specValues = Model::createMultiple(RisItemSpecValue::className());
            Model::loadMultiple($specValues, Yii::$app->request->post());

            // validate all models
            $valid = $spec->validate();
            $valid = Model::validateMultiple($specValues) && $valid;

            if ($valid) {
                $transaction = \Yii::$app->db->beginTransaction();

                try {
                    if ($flag = $spec->save(false)) {
                        foreach ($specValues as $specValue) {
                            $specValue->ris_item_spec_id = $spec->id;
                            if (! ($flag = $specValue->save(false))) {
                                $transaction->rollBack();
                                break;
                            }
                        }
                    }

                    if ($flag) {
                        $transaction->commit();
                        \Yii::$app->getSession()->setFlash('success', 'Specification added successfully');
                        return $type == 'Original' ? $this->redirect(['view', 'id' => $model->id]) : $this->redirect(['supplemental', 'id' => $model->id]);
                    }
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
        }

        return $this->renderAjax('_form-specification', [
            'spec' => $spec,
            'specValues' => (empty($specValues)) ? [new RisItemSpecValue] : $specValues,
            'model' => $model,
            'activity' => $activity,
            'subActivity' => $subActivity,
            'item' => $item,
        ]);
    }

    public function actionAttachSpecification($id, $activity_id, $sub_activity_id, $item_id, $cost, $type)
    {
        $model = $this->findModel($id);
        $activity = Activity::findOne($activity_id);
        $subActivity = SubActivity::findOne($sub_activity_id);
        $item = Item::findOne($item_id);

        $spec = RisItemSpec::findOne([
            'ris_id' => $model->id,
            'activity_id' => $activity_id,
            'sub_activity_id' => $sub_activity_id,
            'item_id' => $item_id,
            'cost' => $cost,
            'type' => $type,
        ]) ? RisItemSpec::findOne([
            'ris_id' => $model->id,
            'activity_id' => $activity_id,
            'sub_activity_id' => $sub_activity_id,
            'item_id' => $item_id,
            'cost' => $cost,
            'type' => $type,
        ]) : new RIsItemSpec();

        $spec->ris_id = $model->id;
        $spec->activity_id = $activity->id;
        $spec->sub_activity_id = $subActivity->id;
        $spec->item_id = $item->id;
        $spec->cost = $cost;
        $spec->type = $type;

        if(Yii::$app->request->post())
        {
           if($spec->save())
           {
            \Yii::$app->getSession()->setFlash('success', 'Specification has been uploaded');
            return $type == 'Original' ? $this->redirect(['view', 'id' => $model->id]) : $this->redirect(['supplemental', 'id' => $model->id]);
           }
        }

        return $this->renderAjax('_form-attach_specification', [
            'spec' => $spec,
            'model' => $model,
            'activity' => $activity,
            'subActivity' => $subActivity,
            'item' => $item,
        ]);
    }

    public function actionUpdateSpecification($id, $activity_id, $sub_activity_id, $item_id, $cost, $type)
    {
        $model = $this->findModel($id);
        $activity = Activity::findOne($activity_id);
        $subActivity = SubActivity::findOne($sub_activity_id);
        $item = Item::findOne($item_id);

        $spec = RisItemSpec::findOne([
            'ris_id' => $model->id,
            'activity_id' => $activity->id,
            'sub_activity_id' => $subActivity->id,
            'item_id' => $item->id,
            'cost' => $cost,
            'type' => $type,
        ]);

        $specValues = $spec->risItemSpecValues;

        if ($spec->load(Yii::$app->request->post())) {

            $oldIDs = ArrayHelper::map($specValues, 'id', 'id');
            $specValues = Model::createMultiple(RisItemSpecValue::classname(), $specValues);
            Model::loadMultiple($specValues, Yii::$app->request->post());
            $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($specValues, 'id', 'id')));

            // ajax validation
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::merge(
                    ActiveForm::validateMultiple($specValues),
                    ActiveForm::validate($spec)
                );
            }

            // validate all models
            $valid = $spec->validate();
            $valid = Model::validateMultiple($specValues) && $valid;

            if ($valid) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    if ($flag = $spec->save(false)) {
                        if (! empty($deletedIDs)) {
                            RisItemSpecValue::deleteAll(['id' => $deletedIDs]);
                        }
                        foreach ($specValues as $specValue) {
                            $specValue->ris_item_spec_id = $spec->id;
                            if (! ($flag = $specValue->save(false))) {
                                $transaction->rollBack();
                                break;
                            }
                        }
                    }
                    if ($flag) {
                        $transaction->commit();
                        \Yii::$app->getSession()->setFlash('success', 'Specification has been updated');
                        return $type == 'Original' ? $this->redirect(['view', 'id' => $model->id]) : $this->redirect(['supplemental', 'id' => $model->id]);
                    }
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
        }

        return $this->renderAjax('_form-specification', [
            'spec' => $spec,
            'specValues' => (empty($specValues)) ? [new RisItemSpecValue] : $specValues,
            'model' => $model,
            'activity' => $activity,
            'subActivity' => $subActivity,
            'item' => $item,
        ]);
    }

    public function actionDeleteSpecification($id, $activity_id, $sub_activity_id, $item_id, $cost, $type)
    {
        $model = $this->findModel($id);
        $activity = Activity::findOne($activity_id);
        $subActivity = SubActivity::findOne($sub_activity_id);
        $item = Item::findOne($item_id);

        $spec = RisItemSpec::findOne([
            'ris_id' => $model->id,
            'activity_id' => $activity->id,
            'sub_activity_id' => $subActivity->id,
            'item_id' => $item->id,
            'cost' => $cost,
            'type' => $type,
        ]);

        if($spec->delete())
        {
            \Yii::$app->getSession()->setFlash('success', 'Specification has been deleted');
            return $type == 'Original' ? $this->redirect(['view', 'id' => $model->id]) : $this->redirect(['supplemental', 'id' => $model->id]);
        }
    }

    /**
     * Finds the Ris model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Ris the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ris::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
