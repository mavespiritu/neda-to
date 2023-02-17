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

class PpmpMonitoringController extends \yii\web\Controller
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

    public function actionIndex()
    {
        $model = new AppropriationItem();
        $model->scenario = Yii::$app->user->can('Administrator') ? 'loadPpmpMonitoringAdmin' : 'loadPpmpMonitoringUser';

        $stages = [
            'Indicative' => 'Indicative',
            'Adjusted' => 'Adjusted',
            'Final' => 'Final',
        ];

        $years = Ppmp::find()->select(['distinct(year) as year'])->asArray()->orderBy(['year' => SORT_DESC])->all();
        $years = ArrayHelper::map($years, 'year', 'year');

        $offices = Office::find()->all();
        $offices = ArrayHelper::map($offices, 'id', 'abbreviation');

        $fundSources = FundSource::find()->all();
        $fundSources = ArrayHelper::map($fundSources, 'id', 'code');

        $orders = [
            'prexc' => ['content' => 'PREXC'],
            'activity' => ['content' => 'PPA'],
            'objectTitle' => ['content' => 'Object'],
        ];

        if($model->load(Yii::$app->request->post()))
        {
            $postData = Yii::$app->request->post('AppropriationItem');

            $items = PpmpItem::find()
            ->select([
                'ppmp_ppmp_item.id as id',
                'tbloffice.abbreviation as division',
                'IF(ppmp_pap.short_code IS NULL,
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
                ) as prexc',
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
                ) as activity',
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
                ) as subactivity',
                'ppmp_procurement_mode.title as modeOfProcurement',
                'IF(originalObj.obj_id IS NOT NULL, concat(parentObj.title," - ",originalObj.title), originalObj.title) as objectTitle',
                'ppmp_item.title as itemTitle',
                'ppmp_ppmp_item.cost as costPerUnit',
                'ppmp_fund_source.code as fundSource',
                '
                (
                    COALESCE(janQuantity.quantity, 0) +
                    COALESCE(febQuantity.quantity, 0) +
                    COALESCE(marQuantity.quantity, 0) +
                    COALESCE(aprQuantity.quantity, 0) +
                    COALESCE(mayQuantity.quantity, 0) +
                    COALESCE(junQuantity.quantity, 0) +
                    COALESCE(julQuantity.quantity, 0) +
                    COALESCE(augQuantity.quantity, 0) +
                    COALESCE(sepQuantity.quantity, 0) +
                    COALESCE(octQuantity.quantity, 0) +
                    COALESCE(novQuantity.quantity, 0) +
                    COALESCE(decQuantity.quantity, 0)
                ) as totalQty',
                'janQuantity.quantity as janQty',
                'febQuantity.quantity as febQty',
                'marQuantity.quantity as marQty',
                'aprQuantity.quantity as aprQty',
                'mayQuantity.quantity as mayQty',
                'junQuantity.quantity as junQty',
                'julQuantity.quantity as julQty',
                'augQuantity.quantity as augQty',
                'sepQuantity.quantity as sepQty',
                'octQuantity.quantity as octQty',
                'novQuantity.quantity as novQty',
                'decQuantity.quantity as decQty',
                'IF(ppmp_item.cse = "Yes", "TRUE", "FALSE") as cse',
                'ppmp_ppmp_item.remarks as remarks',
            ])
            ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id')
            ->leftJoin('tbloffice', 'tbloffice.id = ppmp_ppmp.office_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_procurement_mode', 'ppmp_procurement_mode.id = ppmp_item.procurement_mode_id')
            ->leftJoin('ppmp_obj originalObj', 'originalObj.id = ppmp_ppmp_item.obj_id')
            ->leftJoin('ppmp_obj parentObj', 'parentObj.id = originalObj.obj_id')
            ->leftJoin('ppmp_fund_source', 'ppmp_fund_source.id = ppmp_ppmp_item.fund_source_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->leftJoin(['janQuantity' => '(SELECT * from ppmp_ppmp_item_breakdown where month_id = 1)'], 'janQuantity.ppmp_item_id = ppmp_ppmp_item.id')
            ->leftJoin(['febQuantity' => '(SELECT * from ppmp_ppmp_item_breakdown where month_id = 2)'], 'febQuantity.ppmp_item_id = ppmp_ppmp_item.id')
            ->leftJoin(['marQuantity' => '(SELECT * from ppmp_ppmp_item_breakdown where month_id = 3)'], 'marQuantity.ppmp_item_id = ppmp_ppmp_item.id')
            ->leftJoin(['aprQuantity' => '(SELECT * from ppmp_ppmp_item_breakdown where month_id = 4)'], 'aprQuantity.ppmp_item_id = ppmp_ppmp_item.id')
            ->leftJoin(['mayQuantity' => '(SELECT * from ppmp_ppmp_item_breakdown where month_id = 5)'], 'mayQuantity.ppmp_item_id = ppmp_ppmp_item.id')
            ->leftJoin(['junQuantity' => '(SELECT * from ppmp_ppmp_item_breakdown where month_id = 6)'], 'junQuantity.ppmp_item_id = ppmp_ppmp_item.id')
            ->leftJoin(['julQuantity' => '(SELECT * from ppmp_ppmp_item_breakdown where month_id = 7)'], 'julQuantity.ppmp_item_id = ppmp_ppmp_item.id')
            ->leftJoin(['augQuantity' => '(SELECT * from ppmp_ppmp_item_breakdown where month_id = 8)'], 'augQuantity.ppmp_item_id = ppmp_ppmp_item.id')
            ->leftJoin(['sepQuantity' => '(SELECT * from ppmp_ppmp_item_breakdown where month_id = 9)'], 'sepQuantity.ppmp_item_id = ppmp_ppmp_item.id')
            ->leftJoin(['octQuantity' => '(SELECT * from ppmp_ppmp_item_breakdown where month_id = 10)'], 'octQuantity.ppmp_item_id = ppmp_ppmp_item.id')
            ->leftJoin(['novQuantity' => '(SELECT * from ppmp_ppmp_item_breakdown where month_id = 11)'], 'novQuantity.ppmp_item_id = ppmp_ppmp_item.id')
            ->leftJoin(['decQuantity' => '(SELECT * from ppmp_ppmp_item_breakdown where month_id = 12)'], 'decQuantity.ppmp_item_id = ppmp_ppmp_item.id')
            ;

            //$items = Yii::$app->user->can('Administrator') ? $items->andWhere(['ppmp_ppmp.office_id' => $postData['office_id']]) : $items->andWhere(['ppmp_ppmp.office_id' => Yii::$app->user->identity->userinfo->OFFICE_C]);
            
            if(!Yii::$app->user->can('Administrator'))
            {
                $items = $items->andWhere(['ppmp_ppmp.office_id' => Yii::$app->user->identity->userinfo->OFFICE_C]);
            }else{
                if(!empty($postData['office_id']))
                {
                    $items = $items->andWhere(['ppmp_ppmp.office_id' => $postData['office_id']]);
                }
            }

            if(!empty($postData['stage']))
            {
                $items = $items->andWhere(['ppmp_ppmp.stage' => $postData['stage']]);
            }

            if(!empty($postData['year']))
            {
                $items = $items->andWhere(['ppmp_ppmp.year' => $postData['year']]);
            }

            if(!empty($postData['fund_source_id']))
            {
                $items = $items->andWhere(['ppmp_fund_source.id' => $postData['fund_source_id']]);
            }

            $items = $items->andWhere(['ppmp_ppmp_item.type' => 'Original']);

            $items = $items
            ->orderBy([
                'prexc' => SORT_ASC,
                'ppmp_activity.code' => SORT_ASC,
                'ppmp_sub_activity.code' => SORT_ASC,
                'originalObj.obj_id' => SORT_ASC,
                'originalObj.id' => SORT_ASC,
                'ppmp_item.title' => SORT_ASC,
                'tbloffice.abbreviation' => SORT_ASC,
                'ppmp_fund_source.code' => SORT_ASC,
            ])
            ->asArray()
            ->all();
            
            $data = [];

            $groups = [];
            
            $orders = !empty($postData['order']) ? explode(',',$postData['order']) : [];

            $groups = $orders;

            if(!empty($orders))
            {
                foreach($orders as $i => $order)
                {
                    if($order == 'activity')
                    {
                        array_splice($orders, $i+1, 0, 'subactivity');
                    }
                }
            }

            if(!empty($items))
            {
                foreach($items as $item)
                {
                    $data[$item[$orders[0]]][$item[$orders[1]]][$item[$orders[2]]][$item[$orders[3]]]['items'][] = $item;
                }
            }

            if(!empty($data))
            {
                foreach($data as $firstIdx => $firstOrders)
                {
                    $data[$firstIdx]['estimatedBudget'] = 0;
                    $data[$firstIdx]['janQty'] = 0;
                    $data[$firstIdx]['janCost'] = 0;
                    $data[$firstIdx]['febQty'] = 0;
                    $data[$firstIdx]['febCost'] = 0;
                    $data[$firstIdx]['marQty'] = 0;
                    $data[$firstIdx]['marCost'] = 0;
                    $data[$firstIdx]['aprQty'] = 0;
                    $data[$firstIdx]['aprCost'] = 0;
                    $data[$firstIdx]['mayQty'] = 0;
                    $data[$firstIdx]['mayCost'] = 0;
                    $data[$firstIdx]['junQty'] = 0;
                    $data[$firstIdx]['junCost'] = 0;
                    $data[$firstIdx]['julQty'] = 0;
                    $data[$firstIdx]['julCost'] = 0;
                    $data[$firstIdx]['augQty'] = 0;
                    $data[$firstIdx]['augCost'] = 0;
                    $data[$firstIdx]['sepQty'] = 0;
                    $data[$firstIdx]['sepCost'] = 0;
                    $data[$firstIdx]['octQty'] = 0;
                    $data[$firstIdx]['octCost'] = 0;
                    $data[$firstIdx]['novQty'] = 0;
                    $data[$firstIdx]['novCost'] = 0;
                    $data[$firstIdx]['decQty'] = 0;
                    $data[$firstIdx]['decCost'] = 0;
                    foreach($firstOrders as $secondIdx => $secondOrders)
                    {
                        $data[$firstIdx][$secondIdx]['estimatedBudget'] = 0;
                        $data[$firstIdx][$secondIdx]['janQty'] = 0;
                        $data[$firstIdx][$secondIdx]['janCost'] = 0;
                        $data[$firstIdx][$secondIdx]['febQty'] = 0;
                        $data[$firstIdx][$secondIdx]['febCost'] = 0;
                        $data[$firstIdx][$secondIdx]['marQty'] = 0;
                        $data[$firstIdx][$secondIdx]['marCost'] = 0;
                        $data[$firstIdx][$secondIdx]['aprQty'] = 0;
                        $data[$firstIdx][$secondIdx]['aprCost'] = 0;
                        $data[$firstIdx][$secondIdx]['mayQty'] = 0;
                        $data[$firstIdx][$secondIdx]['mayCost'] = 0;
                        $data[$firstIdx][$secondIdx]['junQty'] = 0;
                        $data[$firstIdx][$secondIdx]['junCost'] = 0;
                        $data[$firstIdx][$secondIdx]['julQty'] = 0;
                        $data[$firstIdx][$secondIdx]['julCost'] = 0;
                        $data[$firstIdx][$secondIdx]['augQty'] = 0;
                        $data[$firstIdx][$secondIdx]['augCost'] = 0;
                        $data[$firstIdx][$secondIdx]['sepQty'] = 0;
                        $data[$firstIdx][$secondIdx]['sepCost'] = 0;
                        $data[$firstIdx][$secondIdx]['octQty'] = 0;
                        $data[$firstIdx][$secondIdx]['octCost'] = 0;
                        $data[$firstIdx][$secondIdx]['novQty'] = 0;
                        $data[$firstIdx][$secondIdx]['novCost'] = 0;
                        $data[$firstIdx][$secondIdx]['decQty'] = 0;
                        $data[$firstIdx][$secondIdx]['decCost'] = 0;
                        foreach($secondOrders as $thirdIdx => $thirdOrders)
                        {
                            $data[$firstIdx][$secondIdx][$thirdIdx]['estimatedBudget'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['janQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['janCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['febQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['febCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['marQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['marCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['aprQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['aprCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['mayQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['mayCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['junQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['junCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['julQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['julCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['augQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['augCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['sepQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['sepCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['octQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['octCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['novQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['novCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['decQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx]['decCost'] = 0;
                            foreach($thirdOrders as $fourthIdx => $items)
                            {
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['estimatedBudget'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['janQty'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['janCost'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['febQty'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['febCost'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['marQty'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['marCost'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['aprQty'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['aprCost'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['mayQty'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['mayCost'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['junQty'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['junCost'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['julQty'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['julCost'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['augQty'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['augCost'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['sepQty'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['sepCost'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['octQty'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['octCost'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['novQty'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['novCost'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['decQty'] = 0;
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['decCost'] = 0;
                                foreach($items['items'] as $item)
                                {
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['estimatedBudget'] += ($item['totalQty'] * $item['costPerUnit']);
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['janQty'] += $item['janQty'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['janCost'] += $item['janQty'] * $item['costPerUnit'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['febQty'] += $item['febQty'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['febCost'] += $item['febQty'] * $item['costPerUnit'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['marQty'] += $item['marQty'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['marCost'] += $item['marQty'] * $item['costPerUnit'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['aprQty'] += $item['aprQty'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['aprCost'] += $item['aprQty'] * $item['costPerUnit'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['mayQty'] += $item['mayQty'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['mayCost'] += $item['mayQty'] * $item['costPerUnit'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['junQty'] += $item['junQty'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['junCost'] += $item['junQty'] * $item['costPerUnit'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['julQty'] += $item['julQty'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['julCost'] += $item['julQty'] * $item['costPerUnit'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['augQty'] += $item['augQty'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['augCost'] += $item['augQty'] * $item['costPerUnit'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['sepQty'] += $item['sepQty'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['sepCost'] += $item['sepQty'] * $item['costPerUnit'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['octQty'] += $item['octQty'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['octCost'] += $item['octQty'] * $item['costPerUnit'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['novQty'] += $item['novQty'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['novCost'] += $item['novQty'] * $item['costPerUnit'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['decQty'] += $item['decQty'];
                                    $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['decCost'] += $item['decQty'] * $item['costPerUnit'];
                                }
                                $data[$firstIdx][$secondIdx][$thirdIdx]['estimatedBudget'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['estimatedBudget'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['janQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['janQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['janCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['janCost'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['febQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['febQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['febCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['febCost'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['marQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['marQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['marCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['marCost'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['aprQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['aprQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['aprCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['aprCost'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['mayQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['mayQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['mayCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['mayCost'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['junQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['junQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['junCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['junCost'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['julQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['julQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['julCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['julCost'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['augQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['augQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['augCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['augCost'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['sepQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['sepQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['sepCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['sepCost'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['octQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['octQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['octCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['octCost'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['novQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['novQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['novCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['novCost'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['decQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['decQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx]['decCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['decCost'];
                            }
                            $data[$firstIdx][$secondIdx]['estimatedBudget'] += $data[$firstIdx][$secondIdx][$thirdIdx]['estimatedBudget'];
                            $data[$firstIdx][$secondIdx]['janQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['janQty'];
                            $data[$firstIdx][$secondIdx]['janCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['janCost'];
                            $data[$firstIdx][$secondIdx]['febQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['febQty'];
                            $data[$firstIdx][$secondIdx]['febCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['febCost'];
                            $data[$firstIdx][$secondIdx]['marQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['marQty'];
                            $data[$firstIdx][$secondIdx]['marCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['marCost'];
                            $data[$firstIdx][$secondIdx]['aprQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['aprQty'];
                            $data[$firstIdx][$secondIdx]['aprCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['aprCost'];
                            $data[$firstIdx][$secondIdx]['mayQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['mayQty'];
                            $data[$firstIdx][$secondIdx]['mayCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['mayCost'];
                            $data[$firstIdx][$secondIdx]['junQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['junQty'];
                            $data[$firstIdx][$secondIdx]['junCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['junCost'];
                            $data[$firstIdx][$secondIdx]['julQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['julQty'];
                            $data[$firstIdx][$secondIdx]['julCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['julCost'];
                            $data[$firstIdx][$secondIdx]['augQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['augQty'];
                            $data[$firstIdx][$secondIdx]['augCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['augCost'];
                            $data[$firstIdx][$secondIdx]['sepQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['sepQty'];
                            $data[$firstIdx][$secondIdx]['sepCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['sepCost'];
                            $data[$firstIdx][$secondIdx]['octQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['octQty'];
                            $data[$firstIdx][$secondIdx]['octCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['octCost'];
                            $data[$firstIdx][$secondIdx]['novQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['novQty'];
                            $data[$firstIdx][$secondIdx]['novCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['novCost'];
                            $data[$firstIdx][$secondIdx]['decQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['decQty'];
                            $data[$firstIdx][$secondIdx]['decCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['decCost'];
                        }
                        $data[$firstIdx]['estimatedBudget'] += $data[$firstIdx][$secondIdx]['estimatedBudget'];
                        $data[$firstIdx]['janQty'] += $data[$firstIdx][$secondIdx]['janQty'];
                        $data[$firstIdx]['janCost'] += $data[$firstIdx][$secondIdx]['janCost'];
                        $data[$firstIdx]['febQty'] += $data[$firstIdx][$secondIdx]['febQty'];
                        $data[$firstIdx]['febCost'] += $data[$firstIdx][$secondIdx]['febCost'];
                        $data[$firstIdx]['marQty'] += $data[$firstIdx][$secondIdx]['marQty'];
                        $data[$firstIdx]['marCost'] += $data[$firstIdx][$secondIdx]['marCost'];
                        $data[$firstIdx]['aprQty'] += $data[$firstIdx][$secondIdx]['aprQty'];
                        $data[$firstIdx]['aprCost'] += $data[$firstIdx][$secondIdx]['aprCost'];
                        $data[$firstIdx]['mayQty'] += $data[$firstIdx][$secondIdx]['mayQty'];
                        $data[$firstIdx]['mayCost'] += $data[$firstIdx][$secondIdx]['mayCost'];
                        $data[$firstIdx]['junQty'] += $data[$firstIdx][$secondIdx]['junQty'];
                        $data[$firstIdx]['junCost'] += $data[$firstIdx][$secondIdx]['junCost'];
                        $data[$firstIdx]['julQty'] += $data[$firstIdx][$secondIdx]['julQty'];
                        $data[$firstIdx]['julCost'] += $data[$firstIdx][$secondIdx]['julCost'];
                        $data[$firstIdx]['augQty'] += $data[$firstIdx][$secondIdx]['augQty'];
                        $data[$firstIdx]['augCost'] += $data[$firstIdx][$secondIdx]['augCost'];
                        $data[$firstIdx]['sepQty'] += $data[$firstIdx][$secondIdx]['sepQty'];
                        $data[$firstIdx]['sepCost'] += $data[$firstIdx][$secondIdx]['sepCost'];
                        $data[$firstIdx]['octQty'] += $data[$firstIdx][$secondIdx]['octQty'];
                        $data[$firstIdx]['octCost'] += $data[$firstIdx][$secondIdx]['octCost'];
                        $data[$firstIdx]['novQty'] += $data[$firstIdx][$secondIdx]['novQty'];
                        $data[$firstIdx]['novCost'] += $data[$firstIdx][$secondIdx]['novCost'];
                        $data[$firstIdx]['decQty'] += $data[$firstIdx][$secondIdx]['decQty'];
                        $data[$firstIdx]['decCost'] += $data[$firstIdx][$secondIdx]['decCost'];
                    }
                }
            }

            $months = Month::find()->all();
            $quarters = Month::find()->select(['distinct(quarter) as quarter'])->all();

            $unAllowedIndexes = [
                'estimatedBudget',
                'janQty',
                'febQty',
                'marQty',
                'aprQty',
                'mayQty',
                'junQty',
                'julQty',
                'augQty',
                'sepQty',
                'octQty',
                'novQty',
                'decQty',
                'janCost',
                'febCost',
                'marCost',
                'aprCost',
                'mayCost',
                'junCost',
                'julCost',
                'augCost',
                'sepCost',
                'octCost',
                'novCost',
                'decCost',
            ];

            $filename = 'PPMP Monitoring';

            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=".$filename.".xls");
            return $this->renderPartial('file', [
                'data' => $data,
                'orders' => $orders,
                'groups' => $groups,
                'months' => $months,
                'quarters' => $quarters,
                'postData' => $postData,
                'type' => 'excel',
                'unAllowedIndexes' => $unAllowedIndexes
            ]);

            /* return $this->renderAjax('view',[
                'model' => $model,
                'data' => $data,
                'postData' => $postData,
                'orders' => $orders,
                'groups' => $groups,
                'months' => $months,
                'quarters' => $quarters,
                'unAllowedIndexes' => $unAllowedIndexes,
            ]); */
        }

        return $this->render('index',[
            'model' => $model,
            'stages' => $stages,
            'years' => $years,
            'offices' => $offices,
            'fundSources' => $fundSources,
            'orders' => $orders,
        ]);
    }

    public function actionDownload($type, $post)
    {
        $postData = json_decode($post, true);
            
        $quantities = ItemBreakdown::find()
        ->select([
            'ppmp_item_id',
            'month_id',
            'quantity'
        ])
        ->createCommand()
        ->getRawSql();

        $items = PpmpItem::find()
        ->select([
            'tbloffice.abbreviation as division',
            'concat(
                ppmp_cost_structure.code,
                ppmp_organizational_outcome.code,
                ppmp_program.code,
                ppmp_sub_program.code,
                ppmp_identifier.code,
                ppmp_pap.code,
                " - ",
                ppmp_pap.title
            ) as prexc',
            'ppmp_activity.title as activity',
            'ppmp_sub_activity.title as subactivity',
            'IF(originalObj.obj_id IS NOT NULL, concat(parentObj.title," - ",originalObj.title), originalObj.title) as objectTitle',
            'ppmp_item.title as itemTitle',
            'ppmp_ppmp_item.cost as costPerUnit',
            'ppmp_fund_source.code as fundSource',
            '
            (
                janQuantity.quantity +
                febQuantity.quantity +
                marQuantity.quantity +
                aprQuantity.quantity +
                mayQuantity.quantity +
                junQuantity.quantity +
                julQuantity.quantity +
                augQuantity.quantity +
                sepQuantity.quantity +
                octQuantity.quantity +
                novQuantity.quantity +
                decQuantity.quantity
            ) as totalQty',
            'janQuantity.quantity as janQty',
            'febQuantity.quantity as febQty',
            'marQuantity.quantity as marQty',
            'aprQuantity.quantity as aprQty',
            'mayQuantity.quantity as mayQty',
            'junQuantity.quantity as junQty',
            'julQuantity.quantity as julQty',
            'augQuantity.quantity as augQty',
            'sepQuantity.quantity as sepQty',
            'octQuantity.quantity as octQty',
            'novQuantity.quantity as novQty',
            'decQuantity.quantity as decQty',
        ])
        ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ppmp_item.ppmp_id')
        ->leftJoin('tbloffice', 'tbloffice.id = ppmp_ppmp.office_id')
        ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
        ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
        ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
        ->leftJoin('ppmp_obj originalObj', 'originalObj.id = ppmp_ppmp_item.obj_id')
        ->leftJoin('ppmp_obj parentObj', 'parentObj.id = originalObj.obj_id')
        ->leftJoin('ppmp_fund_source', 'ppmp_fund_source.id = ppmp_ppmp_item.fund_source_id')
        ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
        ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
        ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
        ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
        ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
        ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
        ->leftJoin(['janQuantity' => '('.$quantities.')'], 'janQuantity.ppmp_item_id = ppmp_ppmp_item.id and janQuantity.month_id = "1"')
        ->leftJoin(['febQuantity' => '('.$quantities.')'], 'febQuantity.ppmp_item_id = ppmp_ppmp_item.id and febQuantity.month_id = "2"')
        ->leftJoin(['marQuantity' => '('.$quantities.')'], 'marQuantity.ppmp_item_id = ppmp_ppmp_item.id and marQuantity.month_id = "3"')
        ->leftJoin(['aprQuantity' => '('.$quantities.')'], 'aprQuantity.ppmp_item_id = ppmp_ppmp_item.id and aprQuantity.month_id = "4"')
        ->leftJoin(['mayQuantity' => '('.$quantities.')'], 'mayQuantity.ppmp_item_id = ppmp_ppmp_item.id and mayQuantity.month_id = "5"')
        ->leftJoin(['junQuantity' => '('.$quantities.')'], 'junQuantity.ppmp_item_id = ppmp_ppmp_item.id and junQuantity.month_id = "6"')
        ->leftJoin(['julQuantity' => '('.$quantities.')'], 'julQuantity.ppmp_item_id = ppmp_ppmp_item.id and julQuantity.month_id = "7"')
        ->leftJoin(['augQuantity' => '('.$quantities.')'], 'augQuantity.ppmp_item_id = ppmp_ppmp_item.id and augQuantity.month_id = "8"')
        ->leftJoin(['sepQuantity' => '('.$quantities.')'], 'sepQuantity.ppmp_item_id = ppmp_ppmp_item.id and sepQuantity.month_id = "9"')
        ->leftJoin(['octQuantity' => '('.$quantities.')'], 'octQuantity.ppmp_item_id = ppmp_ppmp_item.id and octQuantity.month_id = "10"')
        ->leftJoin(['novQuantity' => '('.$quantities.')'], 'novQuantity.ppmp_item_id = ppmp_ppmp_item.id and novQuantity.month_id = "11"')
        ->leftJoin(['decQuantity' => '('.$quantities.')'], 'decQuantity.ppmp_item_id = ppmp_ppmp_item.id and decQuantity.month_id = "12"');

        //$items = Yii::$app->user->can('Administrator') ? $items->andWhere(['ppmp_ppmp.office_id' => $postData['office_id']]) : $items->andWhere(['ppmp_ppmp.office_id' => Yii::$app->user->identity->userinfo->OFFICE_C]);
        if(!Yii::$app->user->can('Administrator'))
        {
            $items = $items->andWhere(['ppmp_ppmp.office_id' => Yii::$app->user->identity->userinfo->OFFICE_C]);
        }else{
            if(!empty($postData['office_id']))
            {
                $items = $items->andWhere(['ppmp_ppmp.office_id' => $postData['office_id']]);
            }
        }
        
        if(!empty($postData['stage']))
        {
            $items = $items->andWhere(['ppmp_ppmp.stage' => $postData['stage']]);
        }

        if(!empty($postData['year']))
        {
            $items = $items->andWhere(['ppmp_ppmp.year' => $postData['year']]);
        }

        if(!empty($postData['fund_source_id']))
        {
            $items = $items->andWhere(['ppmp_fund_source.id' => $postData['fund_source_id']]);
        }

        $items = $items->andWhere(['ppmp_ppmp_item.type' => 'Original']);

        $items = $items
        ->orderBy([
            'prexc' => SORT_ASC,
            'ppmp_activity.code' => SORT_ASC,
            'ppmp_sub_activity.code' => SORT_ASC,
            'originalObj.obj_id' => SORT_ASC,
            'originalObj.id' => SORT_ASC,
            'ppmp_item.title' => SORT_ASC,
            'tbloffice.abbreviation' => SORT_ASC,
            'ppmp_fund_source.code' => SORT_ASC,
        ])
        ->asArray()
        ->all();

        $data = [];

        $groups = [];
        
        $orders = !empty($postData['order']) ? explode(',',$postData['order']) : [];

        $groups = $orders;

        if(!empty($orders))
        {
            foreach($orders as $i => $order)
            {
                if($order == 'activity')
                {
                    array_splice($orders, $i+1, 0, 'subactivity');
                }
            }
        }

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $data[$item[$orders[0]]][$item[$orders[1]]][$item[$orders[2]]][$item[$orders[3]]]['items'][] = $item;
            }
        }

        if(!empty($data))
        {
            foreach($data as $firstIdx => $firstOrders)
            {
                $data[$firstIdx]['estimatedBudget'] = 0;
                $data[$firstIdx]['janQty'] = 0;
                $data[$firstIdx]['janCost'] = 0;
                $data[$firstIdx]['febQty'] = 0;
                $data[$firstIdx]['febCost'] = 0;
                $data[$firstIdx]['marQty'] = 0;
                $data[$firstIdx]['marCost'] = 0;
                $data[$firstIdx]['aprQty'] = 0;
                $data[$firstIdx]['aprCost'] = 0;
                $data[$firstIdx]['mayQty'] = 0;
                $data[$firstIdx]['mayCost'] = 0;
                $data[$firstIdx]['junQty'] = 0;
                $data[$firstIdx]['junCost'] = 0;
                $data[$firstIdx]['julQty'] = 0;
                $data[$firstIdx]['julCost'] = 0;
                $data[$firstIdx]['augQty'] = 0;
                $data[$firstIdx]['augCost'] = 0;
                $data[$firstIdx]['sepQty'] = 0;
                $data[$firstIdx]['sepCost'] = 0;
                $data[$firstIdx]['octQty'] = 0;
                $data[$firstIdx]['octCost'] = 0;
                $data[$firstIdx]['novQty'] = 0;
                $data[$firstIdx]['novCost'] = 0;
                $data[$firstIdx]['decQty'] = 0;
                $data[$firstIdx]['decCost'] = 0;
                foreach($firstOrders as $secondIdx => $secondOrders)
                {
                    $data[$firstIdx][$secondIdx]['estimatedBudget'] = 0;
                    $data[$firstIdx][$secondIdx]['janQty'] = 0;
                    $data[$firstIdx][$secondIdx]['janCost'] = 0;
                    $data[$firstIdx][$secondIdx]['febQty'] = 0;
                    $data[$firstIdx][$secondIdx]['febCost'] = 0;
                    $data[$firstIdx][$secondIdx]['marQty'] = 0;
                    $data[$firstIdx][$secondIdx]['marCost'] = 0;
                    $data[$firstIdx][$secondIdx]['aprQty'] = 0;
                    $data[$firstIdx][$secondIdx]['aprCost'] = 0;
                    $data[$firstIdx][$secondIdx]['mayQty'] = 0;
                    $data[$firstIdx][$secondIdx]['mayCost'] = 0;
                    $data[$firstIdx][$secondIdx]['junQty'] = 0;
                    $data[$firstIdx][$secondIdx]['junCost'] = 0;
                    $data[$firstIdx][$secondIdx]['julQty'] = 0;
                    $data[$firstIdx][$secondIdx]['julCost'] = 0;
                    $data[$firstIdx][$secondIdx]['augQty'] = 0;
                    $data[$firstIdx][$secondIdx]['augCost'] = 0;
                    $data[$firstIdx][$secondIdx]['sepQty'] = 0;
                    $data[$firstIdx][$secondIdx]['sepCost'] = 0;
                    $data[$firstIdx][$secondIdx]['octQty'] = 0;
                    $data[$firstIdx][$secondIdx]['octCost'] = 0;
                    $data[$firstIdx][$secondIdx]['novQty'] = 0;
                    $data[$firstIdx][$secondIdx]['novCost'] = 0;
                    $data[$firstIdx][$secondIdx]['decQty'] = 0;
                    $data[$firstIdx][$secondIdx]['decCost'] = 0;
                    foreach($secondOrders as $thirdIdx => $thirdOrders)
                    {
                        $data[$firstIdx][$secondIdx][$thirdIdx]['estimatedBudget'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['janQty'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['janCost'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['febQty'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['febCost'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['marQty'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['marCost'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['aprQty'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['aprCost'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['mayQty'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['mayCost'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['junQty'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['junCost'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['julQty'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['julCost'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['augQty'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['augCost'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['sepQty'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['sepCost'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['octQty'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['octCost'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['novQty'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['novCost'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['decQty'] = 0;
                        $data[$firstIdx][$secondIdx][$thirdIdx]['decCost'] = 0;
                        foreach($thirdOrders as $fourthIdx => $items)
                        {
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['estimatedBudget'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['janQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['janCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['febQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['febCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['marQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['marCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['aprQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['aprCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['mayQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['mayCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['junQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['junCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['julQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['julCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['augQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['augCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['sepQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['sepCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['octQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['octCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['novQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['novCost'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['decQty'] = 0;
                            $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['decCost'] = 0;
                            foreach($items['items'] as $item)
                            {
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['estimatedBudget'] += ($item['totalQty'] * $item['costPerUnit']);
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['janQty'] += $item['janQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['janCost'] += $item['janQty'] * $item['costPerUnit'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['febQty'] += $item['febQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['febCost'] += $item['febQty'] * $item['costPerUnit'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['marQty'] += $item['marQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['marCost'] += $item['marQty'] * $item['costPerUnit'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['aprQty'] += $item['aprQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['aprCost'] += $item['aprQty'] * $item['costPerUnit'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['mayQty'] += $item['mayQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['mayCost'] += $item['mayQty'] * $item['costPerUnit'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['junQty'] += $item['junQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['junCost'] += $item['junQty'] * $item['costPerUnit'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['julQty'] += $item['julQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['julCost'] += $item['julQty'] * $item['costPerUnit'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['augQty'] += $item['augQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['augCost'] += $item['augQty'] * $item['costPerUnit'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['sepQty'] += $item['sepQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['sepCost'] += $item['sepQty'] * $item['costPerUnit'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['octQty'] += $item['octQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['octCost'] += $item['octQty'] * $item['costPerUnit'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['novQty'] += $item['novQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['novCost'] += $item['novQty'] * $item['costPerUnit'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['decQty'] += $item['decQty'];
                                $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['decCost'] += $item['decQty'] * $item['costPerUnit'];
                            }
                            $data[$firstIdx][$secondIdx][$thirdIdx]['estimatedBudget'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['estimatedBudget'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['janQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['janQty'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['janCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['janCost'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['febQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['febQty'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['febCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['febCost'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['marQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['marQty'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['marCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['marCost'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['aprQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['aprQty'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['aprCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['aprCost'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['mayQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['mayQty'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['mayCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['mayCost'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['junQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['junQty'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['junCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['junCost'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['julQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['julQty'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['julCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['julCost'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['augQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['augQty'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['augCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['augCost'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['sepQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['sepQty'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['sepCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['sepCost'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['octQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['octQty'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['octCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['octCost'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['novQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['novQty'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['novCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['novCost'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['decQty'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['decQty'];
                            $data[$firstIdx][$secondIdx][$thirdIdx]['decCost'] += $data[$firstIdx][$secondIdx][$thirdIdx][$fourthIdx]['decCost'];
                        }
                        $data[$firstIdx][$secondIdx]['estimatedBudget'] += $data[$firstIdx][$secondIdx][$thirdIdx]['estimatedBudget'];
                        $data[$firstIdx][$secondIdx]['janQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['janQty'];
                        $data[$firstIdx][$secondIdx]['janCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['janCost'];
                        $data[$firstIdx][$secondIdx]['febQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['febQty'];
                        $data[$firstIdx][$secondIdx]['febCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['febCost'];
                        $data[$firstIdx][$secondIdx]['marQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['marQty'];
                        $data[$firstIdx][$secondIdx]['marCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['marCost'];
                        $data[$firstIdx][$secondIdx]['aprQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['aprQty'];
                        $data[$firstIdx][$secondIdx]['aprCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['aprCost'];
                        $data[$firstIdx][$secondIdx]['mayQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['mayQty'];
                        $data[$firstIdx][$secondIdx]['mayCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['mayCost'];
                        $data[$firstIdx][$secondIdx]['junQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['junQty'];
                        $data[$firstIdx][$secondIdx]['junCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['junCost'];
                        $data[$firstIdx][$secondIdx]['julQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['julQty'];
                        $data[$firstIdx][$secondIdx]['julCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['julCost'];
                        $data[$firstIdx][$secondIdx]['augQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['augQty'];
                        $data[$firstIdx][$secondIdx]['augCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['augCost'];
                        $data[$firstIdx][$secondIdx]['sepQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['sepQty'];
                        $data[$firstIdx][$secondIdx]['sepCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['sepCost'];
                        $data[$firstIdx][$secondIdx]['octQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['octQty'];
                        $data[$firstIdx][$secondIdx]['octCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['octCost'];
                        $data[$firstIdx][$secondIdx]['novQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['novQty'];
                        $data[$firstIdx][$secondIdx]['novCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['novCost'];
                        $data[$firstIdx][$secondIdx]['decQty'] += $data[$firstIdx][$secondIdx][$thirdIdx]['decQty'];
                        $data[$firstIdx][$secondIdx]['decCost'] += $data[$firstIdx][$secondIdx][$thirdIdx]['decCost'];
                    }
                    $data[$firstIdx]['estimatedBudget'] += $data[$firstIdx][$secondIdx]['estimatedBudget'];
                    $data[$firstIdx]['janQty'] += $data[$firstIdx][$secondIdx]['janQty'];
                    $data[$firstIdx]['janCost'] += $data[$firstIdx][$secondIdx]['janCost'];
                    $data[$firstIdx]['febQty'] += $data[$firstIdx][$secondIdx]['febQty'];
                    $data[$firstIdx]['febCost'] += $data[$firstIdx][$secondIdx]['febCost'];
                    $data[$firstIdx]['marQty'] += $data[$firstIdx][$secondIdx]['marQty'];
                    $data[$firstIdx]['marCost'] += $data[$firstIdx][$secondIdx]['marCost'];
                    $data[$firstIdx]['aprQty'] += $data[$firstIdx][$secondIdx]['aprQty'];
                    $data[$firstIdx]['aprCost'] += $data[$firstIdx][$secondIdx]['aprCost'];
                    $data[$firstIdx]['mayQty'] += $data[$firstIdx][$secondIdx]['mayQty'];
                    $data[$firstIdx]['mayCost'] += $data[$firstIdx][$secondIdx]['mayCost'];
                    $data[$firstIdx]['junQty'] += $data[$firstIdx][$secondIdx]['junQty'];
                    $data[$firstIdx]['junCost'] += $data[$firstIdx][$secondIdx]['junCost'];
                    $data[$firstIdx]['julQty'] += $data[$firstIdx][$secondIdx]['julQty'];
                    $data[$firstIdx]['julCost'] += $data[$firstIdx][$secondIdx]['julCost'];
                    $data[$firstIdx]['augQty'] += $data[$firstIdx][$secondIdx]['augQty'];
                    $data[$firstIdx]['augCost'] += $data[$firstIdx][$secondIdx]['augCost'];
                    $data[$firstIdx]['sepQty'] += $data[$firstIdx][$secondIdx]['sepQty'];
                    $data[$firstIdx]['sepCost'] += $data[$firstIdx][$secondIdx]['sepCost'];
                    $data[$firstIdx]['octQty'] += $data[$firstIdx][$secondIdx]['octQty'];
                    $data[$firstIdx]['octCost'] += $data[$firstIdx][$secondIdx]['octCost'];
                    $data[$firstIdx]['novQty'] += $data[$firstIdx][$secondIdx]['novQty'];
                    $data[$firstIdx]['novCost'] += $data[$firstIdx][$secondIdx]['novCost'];
                    $data[$firstIdx]['decQty'] += $data[$firstIdx][$secondIdx]['decQty'];
                    $data[$firstIdx]['decCost'] += $data[$firstIdx][$secondIdx]['decCost'];
                }
            }
        }

        $unAllowedIndexes = [
            'estimatedBudget',
            'janQty',
            'febQty',
            'marQty',
            'aprQty',
            'mayQty',
            'junQty',
            'julQty',
            'augQty',
            'sepQty',
            'octQty',
            'novQty',
            'decQty',
            'janCost',
            'febCost',
            'marCost',
            'aprCost',
            'mayCost',
            'junCost',
            'julCost',
            'augCost',
            'sepCost',
            'octCost',
            'novCost',
            'decCost',
        ];

        $months = Month::find()->all();
        $quarters = Month::find()->select(['distinct(quarter) as quarter'])->all();
            
        $filename = 'PPMP Monitoring';

        if($type == 'excel')
        {
            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=".$filename.".xls");
            return $this->renderPartial('file', [
                'data' => $data,
                'orders' => $orders,
                'groups' => $groups,
                'months' => $months,
                'quarters' => $quarters,
                'postData' => $postData,
                'type' => $type,
                'unAllowedIndexes' => $unAllowedIndexes
            ]);
        }else if($type == 'pdf')
        {
            $content = $this->renderPartial('file', [
                'data' => $data,
                'orders' => $orders,
                'groups' => $groups,
                'months' => $months,
                'quarters' => $quarters,
                'postData' => $postData,
                'type' => $type,
                'unAllowedIndexes' => $unAllowedIndexes
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
