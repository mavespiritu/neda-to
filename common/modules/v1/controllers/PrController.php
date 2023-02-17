<?php

namespace common\modules\v1\controllers;

use Yii;
use common\modules\v1\models\Pr;
use common\modules\v1\models\Lot;
use common\modules\v1\models\LotItem;
use common\modules\v1\models\Po;
use common\modules\v1\models\Ntp;
use common\modules\v1\models\Noa;
use common\modules\v1\models\Ors;
use common\modules\v1\models\Iar;
use common\modules\v1\models\IarItem;
use common\modules\v1\models\PaymentTerm;
use common\modules\v1\models\Apr;
use common\modules\v1\models\Rfq;
use common\modules\v1\models\Bid;
use common\modules\v1\models\BidMember;
use common\modules\v1\models\BidWinner;
use common\modules\v1\models\BacMember;
use common\modules\v1\models\RfqInfo;
use common\modules\v1\models\AprItem;
use common\modules\v1\models\OrsItem;
use common\modules\v1\models\NonProcurableItem;
use common\modules\v1\models\PrSearch;
use common\modules\v1\models\PrItem;
use common\modules\v1\models\PrItemSpec;
use common\modules\v1\models\PrItemCost;
use common\modules\v1\models\Supplier;
use common\modules\v1\models\PrItemSpecValue;
use common\modules\v1\models\PrItemSearch;
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
use common\modules\v1\models\ProcurementMode;
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
use yii\helpers\Html;
use yii\helpers\Url;
use markavespiritu\user\models\Office;
use yii\widgets\ActiveForm;
use yii\web\Response;
use kartik\mpdf\Pdf;

/**
 * PrController implements the CRUD actions for Pr model.
 */
class PrController extends Controller
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
                'only' => ['index', 'view'],
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'view', 'delete'],
                        'allow' => true,
                        'roles' => ['ProcurementStaff', 'Administrator'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Pr models.
     * @return mixed
     */
    public function actionIndex()
    {
        $session = Yii::$app->session;

        $session->set('PR_ReturnURL', Yii::$app->controller->module->getBackUrl(Url::to()));

        $searchModel = new PrSearch();
        $searchModel->year = date("Y");
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $types = [
            'Supply' => 'Goods',
            'Service' => 'Service/Contract',
        ];

        $fundSources = FundSource::find()->all();
        $fundSources = ArrayHelper::map($fundSources, 'id', 'code');

        $offices = Office::find()->all();
        $offices = ArrayHelper::map($offices, 'abbreviation', 'abbreviation');

        $procurementModes = ProcurementMode::find()->all();
        $procurementModes = ArrayHelper::map($procurementModes, 'id', 'title');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'types' => $types,
            'fundSources' => $fundSources,
            'offices' => $offices,
            'procurementModes' => $procurementModes,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionMenu($id)
    {
        return $this->renderAjax('\menu\menu', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionSubMenu($id, $step)
    {
        $model = $this->findModel($id);
        if($step == 'manageItems')
        {
            return $this->renderAjax('\menu\submenu\manage-items', [
                'model' => $model,
            ]);
        }else if($step == 'groupItems')
        {
            return $this->renderAjax('\menu\submenu\group-items', [
                'model' => $model,
            ]);
        }else if($step == 'setQuotations')
        {
            return $this->renderAjax('\menu\submenu\request-quotations', [
                'model' => $model,
            ]);
        }else if($step == 'retrieveQuotations')
        {
            $rfqs = Rfq::findAll(['pr_id' => $model->id]);
            $rfqs = ArrayHelper::map($rfqs, 'id', 'id');

            $rfqInfosCount = RfqInfo::find()->where(['rfq_id' => $rfqs])->count();
            $aprInfoCount = PrItemCost::find()->where(['pr_id' => $model->id, 'supplier_id' => 1])->count();

            return $this->renderAjax('\menu\submenu\retrieve-quotations', [
                'model' => $model,
                'aprInfoCount' => $aprInfoCount,
                'rfqInfosCount' => $rfqInfosCount,
            ]);
        }else if($step == 'bidItems')
        {
            $rfqs = Rfq::find()->where(['pr_id' => $model->id])->all();

            return $this->renderAjax('\menu\submenu\bid-items', [
                'model' => $model,
                'rfqs' => $rfqs,
            ]);
        }else if($step == 'createPurchaseOrderOrContract')
        {
            $apr = Apr::findOne(['pr_id' => $model->id]);
            $bids = Bid::find()->where(['pr_id' => $model->id])->all();
            
            $data = [];

            if($apr)
            {
                $supplier = Supplier::findOne(1);
                $aprBid = new Bid();
                $aprBid->id = 0;
                $aprBid->bid_no = 'Agency Procurement';

                $data[$aprBid->id]['suppliers'][0] = $supplier;
                $data[$aprBid->id]['bid'] = $aprBid;
            }

            if($bids)
            {
                foreach($bids as $bid)
                {
                    $winners = BidWinner::find()->select(['supplier_id'])
                            ->andWhere(['bid_id' => $bid->id])
                            ->andWhere(['status' => 'Awarded'])
                            ->asArray()
                            ->all();
                    
                    $suppliers = ArrayHelper::map($winners, 'supplier_id', 'supplier_id');
                    $suppliers = Supplier::findAll($suppliers);

                    $data[$bid->id]['suppliers'] = $suppliers;
                    $data[$bid->id]['bid'] = $bid;
                }
            }
        
            return $this->renderAjax('\menu\submenu\create-purchase-order-or-contract', [
                'model' => $model,
                'data' => $data,
            ]);
        }else if($step == 'proceed')
        {
            $pos = Po::findAll(['pr_id' => $model->id]);
        
            return $this->renderAjax('\menu\submenu\proceed-items', [
                'model' => $model,
                'pos' => $pos,
            ]);
        }else if($step == 'award')
        {
            $pos = Po::findAll(['pr_id' => $model->id]);
        
            return $this->renderAjax('\menu\submenu\award-items', [
                'model' => $model,
                'pos' => $pos,
            ]);
        }else if($step == 'obligateItems')
        {
            $pos = Po::findAll(['pr_id' => $model->id]);
        
            return $this->renderAjax('\menu\submenu\obligate-items', [
                'model' => $model,
                'pos' => $pos,
            ]);
        }else if($step == 'inspectItems')
        {
            $pos = Po::findAll(['pr_id' => $model->id]);
        
            return $this->renderAjax('\menu\submenu\inspect-items', [
                'model' => $model,
                'pos' => $pos,
            ]);
        }else if($step == 'generateReports')
        {
            return $this->renderAjax('\menu\submenu\generate-reports', [
                'model' => $model,
            ]);
        }
    }

    public function actionPrMenu($id, $j, $menu)
    {
        $model = $this->findModel($id);

        if($menu == 'selectItem')
        {
            return $this->renderAjax('\menu\submenu\select-items', [
                'model' => $model,
                'j' => $j,
            ]); 
        }else if($menu == 'groupItem')
        {
            return $this->renderAjax('\menu\submenu\group-items', [
                'model' => $model,
                'j' => $j,
            ]); 
        }else if($menu == 'apr')
        {
            return $this->renderAjax('\menu\submenu\apr', [
                'model' => $model,
                'j' => $j,
            ]); 
        }else if($menu == 'rfq')
        {
            return $this->renderAjax('\menu\submenu\rfq', [
                'model' => $model,
                'j' => $j,
            ]); 
        }else if($menu == 'aoq')
        {
            $rfqs = Rfq::find()->where(['pr_id' => $model->id])->all();

            return $this->renderAjax('\menu\submenu\aoq', [
                'model' => $model,
                'rfqs' => $rfqs,
                'j' => $j,
            ]); 
        }else if($menu == 'noa')
        {
            $bids = Bid::findAll(['pr_id' => $model->id]);

            return $this->renderAjax('\menu\submenu\noa', [
                'model' => $model,
                'bids' => $bids,
                'j' => $j,
            ]); 
        }else if($menu == 'po')
        {
            $bids = Bid::findAll(['pr_id' => $model->id]);
            $menus = [];

            $apr = PrItemCost::findAll(['pr_id' => $model->id, 'supplier_id' => 1]);
            $supplier = Supplier::findOne(['id' => 1]);

            return $this->renderAjax('\menu\submenu\po', [
                'model' => $model,
                'bids' => $bids,
                'apr' => $apr,
                'supplier' => $supplier,
                'j' => $j,
            ]); 
        }else if($menu == 'ntp')
        {
            $bids = Bid::findAll(['pr_id' => $model->id]);
            $menus = [];

            $apr = PrItemCost::findAll(['pr_id' => $model->id, 'supplier_id' => 1]);
            $supplier = Supplier::findOne(['id' => 1]);

            return $this->renderAjax('\menu\submenu\ntp', [
                'model' => $model,
                'bids' => $bids,
                'apr' => $apr,
                'supplier' => $supplier,
                'j' => $j,
            ]); 
        }else if($menu == 'ors')
        {
            $bids = Bid::findAll(['pr_id' => $model->id]);
            $menus = [];

            $apr = PrItemCost::findAll(['pr_id' => $model->id, 'supplier_id' => 1]);
            $supplier = Supplier::findOne(['id' => 1]);

            return $this->renderAjax('\menu\submenu\ors', [
                'model' => $model,
                'bids' => $bids,
                'apr' => $apr,
                'supplier' => $supplier,
                'j' => $j,
            ]); 
        }
    }

    // View Details
    public function actionHome($id)
    {
        $model = $this->findModel($id);

        $rfqs = Rfq::findAll(['pr_id' => $model->id]);
        $bids = Bid::findAll(['pr_id' => $model->id]);
        $pos = Po::findAll(['pr_id' => $model->id]);
        $noas = Noa::findAll(['pr_id' => $model->id]);
        $ntps = Ntp::findAll(['pr_id' => $model->id]);
        $iars = Iar::findAll(['pr_id' => $model->id]);

        return $this->renderAjax('\steps\home\index', [
            'model' => $model,
            'rfqs' => $rfqs,
            'bids' => $bids,
            'pos' => $pos,
            'noas' => $noas,
            'ntps' => $ntps,
            'iars' => $iars,
        ]);
    }

    // Manage Lot
    public function actionLot($id)
    {
        $model = $this->findModel($id);

        $lots = $model->getLots()->orderBy(['lot_no' => SORT_ASC])->all();

        return $this->renderAjax('\steps\select-items\lot', [
            'model' => $model,
            'lots' => $lots
        ]);
    }

    // Manage Lot -> Create Lot
    public function actionCreateLot($id)
    {
        $model = $this->findModel($id);

        $lotModel = new Lot();
        $lotModel->pr_id = $model->id;

        if($lotModel->load(Yii::$app->request->post()))
        {
            $lotModel->save();
        }

        return $this->renderAjax('\steps\select-items\lot-form', [
            'model' => $model,
            'lotModel' => $lotModel,
        ]);
    }

    
    // Manage Lot -> Update Lot
    public function actionUpdateLot($id)
    {
        $lotModel = Lot::findOne($id);
        $model = $lotModel->pr;

        if($lotModel->load(Yii::$app->request->post()))
        {
            $lotModel->save();
        }

        return $this->renderAjax('\steps\select-items\lot-form', [
            'model' => $model,
            'lotModel' => $lotModel,
        ]);
    }

    // Manage Lot -> Delete Lot
    public function actionDeleteLot($id, $lot_id)
    {
        $model = $this->findModel($id);

        $lot = Lot::findOne(['id' => $lot_id]);

        $lot->delete();
    }

    // Manage Lot -> View Lot
    public function actionViewLot($id)
    {
        $lot = Lot::findOne($id);
        $model = $lot->pr;

        $lotItemIDs = $lot->lotItems;
        $lotItemIDs = ArrayHelper::map($lotItemIDs, 'pr_item_id', 'pr_item_id');

        $prItems = [];
        $risItems = [];
        $specifications = [];
        $items = PrItem::find()
                ->select([
                    'ppmp_pr_item.id as id',
                    's.id as ris_item_spec_id',
                    'ppmp_ris.ris_no as ris_no',
                    'ppmp_item.id as item_id',
                    'ppmp_item.title as item',
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
                    ) as prexc',
                    'ppmp_activity.id as activityId',
                    'ppmp_activity.title as activityTitle',
                    'ppmp_sub_activity.id as subActivityId',
                    'ppmp_sub_activity.title as subActivityTitle',
                    'ppmp_item.unit_of_measure as unit',
                    'ppmp_pr_item.cost as cost',
                    'sum(ppmp_pr_item.quantity) as total',
                ])
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_pr_item.ris_id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_pr_item.cost and 
                                                    s.type = ppmp_pr_item.type')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $model->id,
                ])
                ->andWhere(['ppmp_pr_item.id' => $lotItemIDs])
                ->groupBy(['ppmp_item.id', 'ppmp_ris.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
                ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'item' => SORT_ASC])
                ->asArray()
                ->all();

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $risItems[$item['activity']][$item['prexc']][] = $item;
                $prItem = PrItem::findOne($item['id']);
                $prItems[$item['id']] = $prItem;

                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }
            }
        }

        if(MultipleModel::loadMultiple($prItems, Yii::$app->request->post()))
        {
            $prExistingItems = Yii::$app->request->post('PrItem');

            $ids = [];
             if(!empty($prExistingItems))
            {
                foreach($prExistingItems as $prItem)
                {
                    if($prItem['id'] != 0)
                    {
                        $item = PrItem::findOne($prItem['id']);
  
                        $includedItems = !is_null($item) ? PrItem::find()
                            ->select(['ppmp_pr_item.id as id'])
                            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                            ->andWhere([
                                'ppmp_pr_item.pr_id' => $item->pr_id,
                                'ppmp_item.id' => $item->ppmpItem->item_id,
                                'ppmp_activity.id' => $item->ppmpItem->activity_id,
                                'ppmp_sub_activity.id' => $item->ppmpItem->sub_activity_id,
                                'ppmp_ris.id' => $item->ris_id,
                                'ppmp_pr_item.cost' => $item->cost,
                            ])
                            ->all() : [];
                        
                        $includedItems = ArrayHelper::map($includedItems, 'id', 'id');
                        
                        // Delete PR Items in lot items
                        LotItem::deleteAll(['in', 'pr_item_id', $includedItems]);

                    }
                }
            }
        }

        return $this->renderAjax('\steps\select-items\view-lot', [
            'model' => $model,
            'lot' => $lot,
            'risItems' => $risItems,
            'prItems' => $prItems,
            'specifications' => $specifications,
        ]);
    }

    // Manage Lot - Include Lot Items
    public function actionIncludeLotItem($id)
    {
        $lot = Lot::findOne($id);
        $model = $lot->pr;

        $lots = $model->lots;
        $lots = ArrayHelper::map($lots, 'id', 'id');

        $lotItemIDs = LotItem::find()->select(['pr_item_id'])->where(['lot_id' => $lots])->asArray()->all();
        $lotItemIDs = ArrayHelper::map($lotItemIDs, 'pr_item_id', 'pr_item_id');

        $prItems = [];
        $risItems = [];
        $specifications = [];
        $items = PrItem::find()
                ->select([
                    'ppmp_pr_item.id as id',
                    's.id as ris_item_spec_id',
                    'ppmp_ris.ris_no as ris_no',
                    'ppmp_item.id as item_id',
                    'ppmp_item.title as item',
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
                    ) as prexc',
                    'ppmp_activity.id as activityId',
                    'ppmp_activity.title as activityTitle',
                    'ppmp_sub_activity.id as subActivityId',
                    'ppmp_sub_activity.title as subActivityTitle',
                    'ppmp_item.unit_of_measure as unit',
                    'ppmp_pr_item.cost as cost',
                    'sum(ppmp_pr_item.quantity) as total',
                ])
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_pr_item.ris_id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_pr_item.cost and 
                                                    s.type = ppmp_pr_item.type')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $model->id,
                ])
                ->andWhere(['not in', 'ppmp_pr_item.id', $lotItemIDs])
                ->groupBy(['ppmp_item.id', 'ppmp_ris.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
                ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'ris_no' => SORT_ASC, 'item' => SORT_ASC])
                ->asArray()
                ->all();

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $risItems[$item['activity']][$item['prexc']][] = $item;
                $prItem = PrItem::findOne($item['id']);
                $prItems[$item['id']] = $prItem;

                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }
            }
        }

        if(MultipleModel::loadMultiple($prItems, Yii::$app->request->post()))
        {
            $prExistingItems = Yii::$app->request->post('PrItem');

            $ids = [];
             if(!empty($prExistingItems))
            {
                foreach($prExistingItems as $prItem)
                {
                    if($prItem['id'] != 0)
                    {
                        $item = PrItem::findOne($prItem['id']);
  
                        $includedItems = !is_null($item) ? PrItem::find()
                            ->select(['ppmp_pr_item.id as id'])
                            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                            ->andWhere([
                                'ppmp_pr_item.pr_id' => $item->pr_id,
                                'ppmp_item.id' => $item->ppmpItem->item_id,
                                'ppmp_activity.id' => $item->ppmpItem->activity_id,
                                'ppmp_sub_activity.id' => $item->ppmpItem->sub_activity_id,
                                'ppmp_ris.id' => $item->ris_id,
                                'ppmp_pr_item.cost' => $item->cost,
                            ])
                            ->all() : [];
                        
                        $includedItems = ArrayHelper::map($includedItems, 'id', 'id');
                        
                        // Include items to lot
                        if($includedItems)
                        {
                            foreach($includedItems as $item)
                            {
                                $lotItemModel = new LotItem();
                                $lotItemModel->lot_id = $lot->id;
                                $lotItemModel->pr_item_id = $item;
                                $lotItemModel->save();
                            }
                        }

                    }
                }
            }
        }

        return $this->renderAjax('\steps\select-items\lot-item-form', [
            'model' => $model,
            'lot' => $lot,
            'risItems' => $risItems,
            'prItems' => $prItems,
            'specifications' => $specifications,
        ]);
    }

    // Select Items
    public function actionItems($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'selectRis';

        $statusIDs = Transaction::find()->select(['max(id) as id'])->where(['model' => 'Ris'])->groupBy(['model_id'])->asArray()->all();
        $statusIDs = ArrayHelper::map($statusIDs, 'id', 'id');
        $status = Transaction::find()->where(['in', 'id', $statusIDs])->createCommand()->getRawSql();

        $rises = Ris::find()
                ->select([
                    'ppmp_ris.id as id',
                    'concat("RIS No. ",ppmp_ris.ris_no," (",ppmp_ris.purpose,")") as title',
                    'tbloffice.abbreviation as groupTitle'
                ])
                ->leftJoin(['status' => '('.$status.')'], 'status.model_id = ppmp_ris.id')
                ->leftJoin('tbloffice', 'tbloffice.abbreviation = ppmp_ris.office_id')
                ->leftJoin('ppmp_ppmp', 'ppmp_ppmp.id = ppmp_ris.ppmp_id')
                ->andWhere(['status.status' => 'Approved'])
                ->andWhere(['ppmp_ppmp.year' => $model->year])
                //->andWhere(['SUBSTRING(ppmp_ris.ris_no, 1, 2)' => substr($model->pr_no, 0, 2)])
                ->andWhere(['ppmp_ris.fund_source_id' => $model->fund_source_id])
                ->asArray()
                ->all();
        
        $rises = ArrayHelper::map($rises, 'id', 'title', 'groupTitle');

        return $this->renderAjax('\steps\select-items\items', [
            'model' => $model,
            'rises' => $rises
        ]);
    }

    // Preview PR
    public function actionPr($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'printPr';
        $lotItems = [];
        $specifications = [];
        $entityName = Settings::findOne(['title' => 'Entity Name']);
        $fundCluster = FundCluster::findOne($model->fund_cluster_id);
        $rccs = Pritem::find()
                ->select(['IF(ppmp_pap.short_code IS NULL,
                            concat(
                                ppmp_cost_structure.code,"",
                                ppmp_organizational_outcome.code,"",
                                ppmp_program.code,"",
                                ppmp_sub_program.code,"",
                                ppmp_identifier.code,"",
                                ppmp_pap.code,"000-",
                                ppmp_activity.code
                            )
                            ,
                            concat(
                                ppmp_pap.short_code,"-",
                                ppmp_activity.code
                            )
                        ) as prexc',])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $model->id,
                ])
                ->groupBy(['ppmp_activity.id'])
                ->asArray()
                ->all();
        
        $rccs = ArrayHelper::map($rccs, 'prexc', 'prexc');
        
        $items = PrItem::find()
                ->select([
                    'ppmp_pr_item.id as id',
                    's.id as ris_item_spec_id',
                    'ppmp_item.id as item_id',
                    'ppmp_item.title as item',
                    'IF(ppmp_lot.title IS NOT NULL, concat("Lot No. ",ppmp_lot.lot_no," - ",ppmp_lot.title), 0) as lotTitle',
                    'ppmp_item.unit_of_measure as unit',
                    'ppmp_pr_item.cost as cost',
                    'sum(ppmp_pr_item.quantity) as total'
                ])
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
                ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_pr_item.cost and 
                                                    s.type = ppmp_pr_item.type')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $model->id,
                ])
                ->groupBy(['ppmp_item.id', 's.id', 'ppmp_pr_item.cost', 'lotTitle'])
                ->orderBy(['lotTitle' => SORT_ASC, 'item' => SORT_ASC])
                ->asArray()
                ->all();
        
        if(!empty($items))
        {
            foreach($items as $item)
            {
                $lotItems[$item['lotTitle']][] = $item;

                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }
            }
        }

        if($model->load(Yii::$app->request->post()))
        {
            $model->save();
        }

        return $this->renderAjax('\steps\home\pr', [
            'model' => $model,
            'entityName' => $entityName,
            'fundCluster' => $fundCluster,
            'rccs' => $rccs,
            'items' => $items,
            'lotItems' => $lotItems,
            'specifications' => $specifications,
        ]);
    }

    // Select Items -> Select RIS Items
    public function actionSelectRisItems($id, $ris_id)
    {
        $model = $this->findModel($id);

        $existingItems = PrItem::find()->asArray()->all();
        $existingItems = ArrayHelper::map($existingItems, 'ris_item_id', 'ris_item_id');

        $awardedItems = PrItem::find()
                        ->select(['ris_item_id'])
                        ->leftJoin('ppmp_bid_winner', 'ppmp_bid_winner.pr_item_id = ppmp_pr_item.id')
                        ->andWhere(['ppmp_bid_winner.status' => 'Awarded'])
                        ->asArray()
                        ->all();
        $awardedItems = ArrayHelper::map($awardedItems, 'ris_item_id', 'ris_item_id');

        $obligatedItems = OrsItem::find()
                        ->select(['ris_item_id'])
                        ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_ors_item.pr_item_id')
                        ->asArray()
                        ->all();

        $obligatedItems = ArrayHelper::map($obligatedItems, 'ris_item_id', 'ris_item_id');

        $ris = Ris::findOne($ris_id);
        
        $specifications = [];

        $items = RisItem::find()
                ->select([
                    'ppmp_ris_item.id as id',
                    'ppmp_ris_item.ris_id as ris_id',
                    'ppmp_item.id as stockNo',
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
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_ris_item.ris_id')
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
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris_item.ris_id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_ris_item.cost and 
                                                    s.type = ppmp_ris_item.type')
                ->andWhere(['ppmp_ris.id' => $ris->id])
                ->andWhere(['in', 'ppmp_ris_item.type', ['Original', 'Supplemental']])
                ->andWhere(['not in', 'ppmp_ris_item.id', $awardedItems])
                ->andWhere(['not in', 'ppmp_ris_item.id', $obligatedItems])
                ->andWhere(['not in', 'ppmp_ris_item.id', $existingItems])
                ->groupBy(['ppmp_item.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_ris_item.cost'])
                ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'itemTitle' => SORT_ASC])
                ->asArray()
                ->all();

        $risItems = [];
        $prItems = [];

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $risItems[$item['activity']][$item['prexc']][] = $item;
                $prItem = new PrItem();
                $prItem->pr_id = $model->id;
                $prItem->ris_id = $ris->id;
                $prItem->item_id = $item['stockNo'];
                $prItem->activity_id = $item['activityId'];
                $prItem->cost = $item['cost'];
                $prItem->type = $item['type'];
                
                $prItems[$item['id']] = $prItem;

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

        if(MultipleModel::loadMultiple($prItems, Yii::$app->request->post()))
        {
            $risExistingItems = Yii::$app->request->post('PrItem');

            $aprModel = Apr::findOne(['pr_id' => $model->id]) ? Apr::findOne(['pr_id' => $model->id]) : new Apr();
            $aprModel->pr_id = $model->id;
            $aprModel->save(false);

            if(!empty($risExistingItems))
            {
                foreach($risExistingItems as $prItem)
                {
                    if($prItem['ris_item_id'] != 0)
                    {
                        $risItem = RisItem::findOne($prItem['ris_item_id']);

                        $includedItems = RisItem::find()
                            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_ris_item.ppmp_item_id')
                            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                            ->andWhere([
                                'ppmp_ris_item.ris_id' => $risItem->ris_id,
                                'ppmp_item.id' => $risItem->ppmpItem->item_id,
                                'ppmp_activity.id' => $risItem->ppmpItem->activity_id,
                                'ppmp_sub_activity.id' => $risItem->ppmpItem->sub_activity_id,
                                'ppmp_ris_item.cost' => $risItem->cost,
                            ])
                            ->andWhere(['in', 'ppmp_ris_item.type', ['Original', 'Supplemental']])
                            ->all();

                        if($includedItems)
                        {
                            foreach($includedItems as $item)
                            {
                                $prItemModel = new PrItem();
                                $prItemModel->pr_id = $model->id;
                                $prItemModel->ris_id = $ris->id;
                                $prItemModel->ris_item_id = $item->id;
                                $prItemModel->ppmp_item_id = $item->ppmp_item_id;
                                $prItemModel->month_id = $item->month_id;
                                $prItemModel->cost = $item->cost;
                                $prItemModel->quantity = $item->quantity;
                                $prItemModel->type = $item->type;

                                if($prItemModel->save())
                                {
                                    // Include in APR upon saving of item in PR.
                                    $aprItemModel = new AprItem();
                                    $aprItemModel->apr_id = $aprModel->id;
                                    $aprItemModel->pr_item_id = $prItemModel->id;
                                    $aprItemModel->save();
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->renderAjax('\steps\select-items\ris_items', [
            'model' => $model,
            'risItems' => $risItems,
            'prItems' => $prItems,
            'specifications' => $specifications,
            'ris' => $ris
        ]);
    }

    // Select Items -> Select PR Items
    public function actionSelectPrItems($id)
    {
        $model = $this->findModel($id);
        $prItems = [];
        $risItems = [];
        $specifications = [];
        $items = PrItem::find()
                ->select([
                    'ppmp_pr_item.id as id',
                    's.id as ris_item_spec_id',
                    'ppmp_ris.ris_no as ris_no',
                    'ppmp_item.id as item_id',
                    'ppmp_item.title as item',
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
                    ) as prexc',
                    'ppmp_activity.id as activityId',
                    'ppmp_activity.title as activityTitle',
                    'ppmp_sub_activity.id as subActivityId',
                    'ppmp_sub_activity.title as subActivityTitle',
                    'ppmp_item.unit_of_measure as unit',
                    'ppmp_pr_item.cost as cost',
                    'sum(ppmp_pr_item.quantity) as total',
                ])
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_pr_item.ris_id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_pr_item.cost and 
                                                    s.type = ppmp_pr_item.type')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $model->id,
                ])
                ->groupBy(['ppmp_item.id', 'ppmp_ris.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
                ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'item' => SORT_ASC])
                ->asArray()
                ->all();

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $risItems[$item['activity']][$item['prexc']][] = $item;
                $prItem = PrItem::findOne($item['id']);
                $prItems[$item['id']] = $prItem;

                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }
            }
        }

        if(MultipleModel::loadMultiple($prItems, Yii::$app->request->post()))
        {
            $prExistingItems = Yii::$app->request->post('PrItem');

            $ids = [];
             if(!empty($prExistingItems))
            {
                foreach($prExistingItems as $prItem)
                {
                    if($prItem['id'] != 0)
                    {
                        $item = PrItem::findOne($prItem['id']);
  
                        $includedItems = !is_null($item) ? PrItem::find()
                            ->select(['ppmp_pr_item.id as id'])
                            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                            ->andWhere([
                                'ppmp_pr_item.pr_id' => $item->pr_id,
                                'ppmp_item.id' => $item->ppmpItem->item_id,
                                'ppmp_activity.id' => $item->ppmpItem->activity_id,
                                'ppmp_sub_activity.id' => $item->ppmpItem->sub_activity_id,
                                'ppmp_ris.id' => $item->ris_id,
                                'ppmp_pr_item.cost' => $item->cost,
                            ])
                            ->all() : [];
                        
                        $includedItems = ArrayHelper::map($includedItems, 'id', 'id');
                        
                        // Delete PR Items in all PR steps
                        PrItem::deleteAll(['in', 'id', $includedItems]);
                        AprItem::deleteAll(['in', 'pr_item_id', $includedItems]);
                        PrItemCost::deleteAll(['in', 'pr_item_id', $includedItems]);
                        NonProcurableItem::deleteAll(['in', 'pr_item_id', $includedItems]);
                        BidWinner::deleteAll(['in', 'pr_item_id', $includedItems]);
                        OrsItem::deleteAll(['in', 'pr_item_id', $includedItems]);
                        IarItem::deleteAll(['in', 'pr_item_id', $includedItems]);

                    }
                }
            }
        }

        return $this->renderAjax('\steps\home\pr_items', [
            'model' => $model,
            'risItems' => $risItems,
            'prItems' => $prItems,
            'specifications' => $specifications,
        ]);
    }

    // Group Items -> Group APR Items
    public function actionGroupAprItems($id)
    {
        $model = $this->findModel($id);

        $prItems = [];
        $aprItems = [];
        $specifications = [];

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $forAprs = PrItem::find()
                ->select([
                    'ppmp_pr_item.id as id',
                    's.id as ris_item_spec_id',
                    'ppmp_ris.ris_no as ris_no',
                    'ppmp_item.id as item_id',
                    'ppmp_item.title as item',
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
                    ) as prexc',
                    'ppmp_activity.id as activityId',
                    'ppmp_activity.title as activityTitle',
                    'ppmp_sub_activity.id as subActivityId',
                    'ppmp_sub_activity.title as subActivityTitle',
                    'ppmp_item.unit_of_measure as unit',
                    'ppmp_pr_item.cost as cost',
                    'sum(ppmp_pr_item.quantity) as total',
                ])
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_pr_item.ris_id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_pr_item.cost and 
                                                    s.type = ppmp_pr_item.type')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $model->id,
                ])
                ->andWhere(['in', 'ppmp_pr_item.id', $aprItemIDs])
                ->groupBy(['ppmp_item.id', 'ppmp_ris.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
                ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'item' => SORT_ASC])
                ->asArray()
                ->all();

        if(!empty($forAprs))
        {
            foreach($forAprs as $item)
            {
                $prItems[$item['activity']][$item['prexc']][] = $item;
                $aprItem = new AprItem();
                $aprItem->id = $item['id'];
                $aprItems[$item['id']] = $aprItem;

                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }
            }
        }

        return $this->renderAjax('\steps\group-items\apr-items', [
            'model' => $model,
            'forAprs' => $forAprs,
            'prItems' => $prItems,
            'aprItems' => $aprItems,
            'specifications' => $specifications,
        ]);
    }

    // Group Items -> Group RFQ Items
    public function actionGroupRfqItems($id)
    {
        $model = $this->findModel($id);

        $prItems = [];
        $rfqItems = [];
        $specifications = [];

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $nonProcurableItemIDs = NonProcurableItem::find()
                    ->select(['pr_item_id'])
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $forRfqs = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_ris.ris_no as ris_no',
                's.id as ris_item_spec_id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
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
                    ) as prexc',
                'ppmp_activity.id as activityId',
                'ppmp_activity.title as activityTitle',
                'ppmp_sub_activity.id as subActivityId',
                'ppmp_sub_activity.title as subActivityTitle',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                s.activity_id = ppmp_ppmp_item.activity_id and 
                                                s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                s.item_id = ppmp_ppmp_item.item_id and 
                                                s.cost = ppmp_pr_item.cost and 
                                                s.type = ppmp_pr_item.type')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
            ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 'ppmp_ris.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
            ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'item' => SORT_ASC])
            ->asArray()
            ->all();
        
        if(!empty($forRfqs))
        {
            foreach($forRfqs as $item)
            {

                $prItems[$item['activity']][$item['prexc']][] = $item;
                $rfqItem = new AprItem();
                $rfqItem->id = $item['id'];
                $rfqItems[$item['id']] = $rfqItem;

                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }
            }
        }

        return $this->renderAjax('\steps\group-items\rfq-items', [
            'model' => $model,
            'forRfqs' => $forRfqs,
            'prItems' => $prItems,
            'rfqItems' => $rfqItems,
            'specifications' => $specifications,
        ]);
    }

    // Group Items -> Group Non-Procurable Items
    public function actionGroupNonProcurableItems($id)
    {
        $model = $this->findModel($id);

        $prItems = [];
        $nonProcurableItems = [];
        $specifications = [];

        $nonProcurableItemIDs = NonProcurableItem::find()
                    ->select(['pr_item_id'])
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $forNonProcurables = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_ris.ris_no as ris_no',
                's.id as ris_item_spec_id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
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
                    ) as prexc',
                'ppmp_activity.id as activityId',
                'ppmp_activity.title as activityTitle',
                'ppmp_sub_activity.id as subActivityId',
                'ppmp_sub_activity.title as subActivityTitle',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                s.activity_id = ppmp_ppmp_item.activity_id and 
                                                s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                s.item_id = ppmp_ppmp_item.item_id and 
                                                s.cost = ppmp_pr_item.cost and 
                                                s.type = ppmp_pr_item.type')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 'ppmp_ris.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
            ->orderBy(['activity' => SORT_ASC, 'prexc' => SORT_ASC, 'item' => SORT_ASC])
            ->asArray()
            ->all();
        
        if(!empty($forNonProcurables))
        {
            foreach($forNonProcurables as $item)
            {

                $prItems[$item['activity']][$item['prexc']][] = $item;
                $nonProcurableItem = new NonProcurableItem();
                $nonProcurableItem->id = $item['id'];
                $nonProcurableItems[$item['id']] = $nonProcurableItem;

                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }
            }
        }

        return $this->renderAjax('\steps\group-items\non-procurable-items', [
            'model' => $model,
            'forNonProcurables' => $forNonProcurables,
            'prItems' => $prItems,
            'nonProcurableItems' => $nonProcurableItems,
            'specifications' => $specifications,
        ]);
    }

    // Group Items -> Save Grouped Items
    public function actionSaveGroupItems($id, $from, $to)
    {
        $model = $this->findModel($id);
        if(Yii::$app->request->post())
        {
            if($from == 'APR' && $to == 'RFQ')
            {
                $aprSupplyOfficer = Settings::findOne(['title' => 'APR Supply Officer']);
                $aprFundsCertifier = Settings::findOne(['title' => 'APR Funds Certifier']);
                $aprApprover = Settings::findOne(['title' => 'APR Approver']);

                $apr = Apr::findOne(['pr_id' => $model->id]) ? Apr::findOne(['pr_id' => $model->id]) : new Apr();
                $apr->pr_id = $model->id;
                $apr->stock_certified_by = $aprSupplyOfficer->value;
                $apr->fund_certified_by = $aprFundsCertifier->value;
                $apr->approved_by = $aprApprover->value;
                $apr->save();

                $selectedPrItems = Yii::$app->request->post('AprItem');
            
                if(!empty($selectedPrItems))
                {
                    foreach($selectedPrItems as $prItem)
                    {
                        if($prItem['id'] != 0)
                        {
                            $item = PrItem::findOne($prItem['id']);
                            
                            $includedItems = PrItem::find()
                            ->select(['ppmp_pr_item.id as id'])
                            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                            ->andWhere([
                                'ppmp_pr_item.pr_id' => $item->pr_id,
                                'ppmp_pr_item.ris_id' => $item->ris_id,
                                'ppmp_item.id' => $item->ppmpItem->item_id,
                                'ppmp_activity.id' => $item->ppmpItem->activity_id,
                                'ppmp_sub_activity.id' => $item->ppmpItem->sub_activity_id,
                                'ppmp_pr_item.cost' => $item->cost,
                                'ppmp_pr_item.type' => $item->type,
                            ])
                            ->andWhere(['in', 'ppmp_ris_item.type', ['Original', 'Supplemental']])
                            ->all();
                            
                            $includedItems = ArrayHelper::map($includedItems, 'id', 'id');

                            if(!empty($includedItems))
                            {
                                foreach($includedItems as $includedItem)
                                {
                                    $aprItem = AprItem::findOne(['apr_id' => $apr->id, 'pr_item_id' => $includedItem]);
                                    if($aprItem){
                                        $aprItem->delete();
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else if($from == 'APR' && $to == 'NP')
            {
                $aprSupplyOfficer = Settings::findOne(['title' => 'APR Supply Officer']);
                $aprFundsCertifier = Settings::findOne(['title' => 'APR Funds Certifier']);
                $aprApprover = Settings::findOne(['title' => 'APR Approver']);

                $apr = Apr::findOne(['pr_id' => $model->id]) ? Apr::findOne(['pr_id' => $model->id]) : new Apr();
                $apr->pr_id = $model->id;
                $apr->stock_certified_by = $aprSupplyOfficer->value;
                $apr->fund_certified_by = $aprFundsCertifier->value;
                $apr->approved_by = $aprApprover->value;
                $apr->save();

                $selectedPrItems = Yii::$app->request->post('AprItem');
            
                if(!empty($selectedPrItems))
                {
                    foreach($selectedPrItems as $prItem)
                    {
                        if($prItem['id'] != 0)
                        {
                            $item = PrItem::findOne($prItem['id']);
                            
                            $includedItems = PrItem::find()
                            ->select(['ppmp_pr_item.id as id'])
                            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                            ->andWhere([
                                'ppmp_pr_item.pr_id' => $item->pr_id,
                                'ppmp_pr_item.ris_id' => $item->ris_id,
                                'ppmp_item.id' => $item->ppmpItem->item_id,
                                'ppmp_activity.id' => $item->ppmpItem->activity_id,
                                'ppmp_sub_activity.id' => $item->ppmpItem->sub_activity_id,
                                'ppmp_pr_item.cost' => $item->cost,
                                'ppmp_pr_item.type' => $item->type,
                            ])
                            ->andWhere(['in', 'ppmp_ris_item.type', ['Original', 'Supplemental']])
                            ->all();
                            
                            $includedItems = ArrayHelper::map($includedItems, 'id', 'id');

                            if(!empty($includedItems))
                            {
                                foreach($includedItems as $includedItem)
                                {
                                    $aprItem = AprItem::findOne(['apr_id' => $apr->id, 'pr_item_id' => $includedItem]);
                                    if($aprItem){
                                        $aprItem->delete();
                                    }

                                    $nonProcurableItem = new NonProcurableItem();
                                    $nonProcurableItem->pr_id = $model->id;
                                    $nonProcurableItem->pr_item_id = $includedItem;
                                    $nonProcurableItem->save();
                                }
                            }
                        }
                    }
                }
            }
            else if($from == 'RFQ' && $to == 'APR')
            {
                $aprSupplyOfficer = Settings::findOne(['title' => 'APR Supply Officer']);
                $aprFundsCertifier = Settings::findOne(['title' => 'APR Funds Certifier']);
                $aprApprover = Settings::findOne(['title' => 'APR Approver']);

                $apr = Apr::findOne(['pr_id' => $model->id]) ? Apr::findOne(['pr_id' => $model->id]) : new Apr();
                $apr->pr_id = $model->id;
                $apr->stock_certified_by = $aprSupplyOfficer->value;
                $apr->fund_certified_by = $aprFundsCertifier->value;
                $apr->approved_by = $aprApprover->value;
                $apr->save();

                $selectedPrItems = Yii::$app->request->post('AprItem');
          
                if(!empty($selectedPrItems))
                {
                    foreach($selectedPrItems as $prItem)
                    {
                        if($prItem['id'] != 0)
                        {
                            $item = PrItem::findOne($prItem['id']);
    
                            $includedItems = PrItem::find()
                                ->select(['ppmp_pr_item.id as id'])
                                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                                ->andWhere([
                                    'ppmp_pr_item.pr_id' => $item->pr_id,
                                    'ppmp_pr_item.ris_id' => $item->ris_id,
                                    'ppmp_item.id' => $item->ppmpItem->item_id,
                                    'ppmp_activity.id' => $item->ppmpItem->activity_id,
                                    'ppmp_sub_activity.id' => $item->ppmpItem->sub_activity_id,
                                    'ppmp_pr_item.cost' => $item->cost,
                                    'ppmp_pr_item.type' => $item->type,
                                ])
                                ->andWhere(['in', 'ppmp_ris_item.type', ['Original', 'Supplemental']])
                                ->all();
                            
                            $includedItems = ArrayHelper::map($includedItems, 'id', 'id');

                            if(!empty($includedItems))
                            {
                                foreach($includedItems as $includedItem)
                                {
                                    $nonProcurableItem = NonProcurableItem::findOne(['pr_id' => $model->id, 'pr_item_id' => $includedItem]);
                                    if($nonProcurableItem){
                                        $nonProcurableItem->delete();
                                    }

                                    $aprItem = new AprItem();
                                    $aprItem->apr_id = $apr->id;
                                    $aprItem->pr_item_id = $includedItem;
                                    $aprItem->save();
                                }
                            }
                        }
                    }
                }
            }
            else if($from == 'RFQ' && $to == 'NP')
            {
                $selectedPrItems = Yii::$app->request->post('AprItem');
            
                if(!empty($selectedPrItems))
                {
                    foreach($selectedPrItems as $prItem)
                    {
                        if($prItem['id'] != 0)
                        {
                            $item = PrItem::findOne($prItem['id']);
                            
                            $includedItems = PrItem::find()
                            ->select(['ppmp_pr_item.id as id'])
                            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                            ->andWhere([
                                'ppmp_pr_item.pr_id' => $item->pr_id,
                                'ppmp_pr_item.ris_id' => $item->ris_id,
                                'ppmp_item.id' => $item->ppmpItem->item_id,
                                'ppmp_activity.id' => $item->ppmpItem->activity_id,
                                'ppmp_sub_activity.id' => $item->ppmpItem->sub_activity_id,
                                'ppmp_pr_item.cost' => $item->cost,
                                'ppmp_pr_item.type' => $item->type,
                            ])
                            ->andWhere(['in', 'ppmp_ris_item.type', ['Original', 'Supplemental']])
                            ->all();
                            
                            $includedItems = ArrayHelper::map($includedItems, 'id', 'id');

                            if(!empty($includedItems))
                            {
                                foreach($includedItems as $includedItem)
                                {
                                    $nonProcurableItem = new NonProcurableItem();
                                    $nonProcurableItem->pr_id = $model->id;
                                    $nonProcurableItem->pr_item_id = $includedItem;
                                    $nonProcurableItem->save();
                                }
                            }
                        }
                    }
                }
            }
            else if($from == 'NP' && $to == 'APR')
            {
                $aprSupplyOfficer = Settings::findOne(['title' => 'APR Supply Officer']);
                $aprFundsCertifier = Settings::findOne(['title' => 'APR Funds Certifier']);
                $aprApprover = Settings::findOne(['title' => 'APR Approver']);

                $apr = Apr::findOne(['pr_id' => $model->id]) ? Apr::findOne(['pr_id' => $model->id]) : new Apr();
                $apr->pr_id = $model->id;
                $apr->stock_certified_by = $aprSupplyOfficer->value;
                $apr->fund_certified_by = $aprFundsCertifier->value;
                $apr->approved_by = $aprApprover->value;
                $apr->save();

                $selectedPrItems = Yii::$app->request->post('NonProcurableItem');
            
                if(!empty($selectedPrItems))
                {
                    foreach($selectedPrItems as $prItem)
                    {
                        if($prItem['id'] != 0)
                        {
                            $item = PrItem::findOne($prItem['id']);
                            
                            $includedItems = PrItem::find()
                            ->select(['ppmp_pr_item.id as id'])
                            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                            ->andWhere([
                                'ppmp_pr_item.pr_id' => $item->pr_id,
                                'ppmp_pr_item.ris_id' => $item->ris_id,
                                'ppmp_item.id' => $item->ppmpItem->item_id,
                                'ppmp_activity.id' => $item->ppmpItem->activity_id,
                                'ppmp_sub_activity.id' => $item->ppmpItem->sub_activity_id,
                                'ppmp_pr_item.cost' => $item->cost,
                                'ppmp_pr_item.type' => $item->type,
                            ])
                            ->andWhere(['in', 'ppmp_ris_item.type', ['Original', 'Supplemental']])
                            ->all();
                            
                            $includedItems = ArrayHelper::map($includedItems, 'id', 'id');

                            if(!empty($includedItems))
                            {
                                foreach($includedItems as $includedItem)
                                {
                                    $nonProcurableItem = NonProcurableItem::findOne(['pr_id' => $model->id, 'pr_item_id' => $includedItem]);
                                    if($nonProcurableItem){
                                        $nonProcurableItem->delete();
                                    }

                                    $aprItem = new AprItem();
                                    $aprItem->apr_id = $apr->id;
                                    $aprItem->pr_item_id = $includedItem;
                                    $aprItem->save();
                                }
                            }
                        }
                    }
                }
            }
            else if($from == 'NP' && $to == 'RFQ')
            {
                $selectedPrItems = Yii::$app->request->post('NonProcurableItem');
            
                if(!empty($selectedPrItems))
                {
                    foreach($selectedPrItems as $prItem)
                    {
                        if($prItem['id'] != 0)
                        {
                            $item = PrItem::findOne($prItem['id']);
                            
                            $includedItems = PrItem::find()
                            ->select(['ppmp_pr_item.id as id'])
                            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                            ->andWhere([
                                'ppmp_pr_item.pr_id' => $item->pr_id,
                                'ppmp_pr_item.ris_id' => $item->ris_id,
                                'ppmp_item.id' => $item->ppmpItem->item_id,
                                'ppmp_activity.id' => $item->ppmpItem->activity_id,
                                'ppmp_sub_activity.id' => $item->ppmpItem->sub_activity_id,
                                'ppmp_pr_item.cost' => $item->cost,
                                'ppmp_pr_item.type' => $item->type,
                            ])
                            ->andWhere(['in', 'ppmp_ris_item.type', ['Original', 'Supplemental']])
                            ->all();
                            
                            $includedItems = ArrayHelper::map($includedItems, 'id', 'id');

                            if(!empty($includedItems))
                            {
                                foreach($includedItems as $includedItem)
                                {
                                    $nonProcurableItem = NonProcurableItem::findOne(['pr_id' => $model->id, 'pr_item_id' => $includedItem]);
                                    if($nonProcurableItem){
                                        $nonProcurableItem->delete();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // Request Quotation -> Request APR
    public function actionRequestApr($id)
    {
        $model = $this->findModel($id);
        $agency = Settings::findOne(['title' => 'Agency Name']);
        $regionalOffice = Settings::findOne(['title' => 'Regional Office']);
        $address = Settings::findOne(['title' => 'Address']);
        $shortName = Settings::findOne(['title' => 'Agency Short Name']);

        $aprModel = Apr::findOne(['pr_id' => $model->id]) ? Apr::findOne(['pr_id' => $model->id]) : new Apr();
        $aprModel->pr_id = $model->id; 

        $supplier = Supplier::findOne(['id' => 1]);
        $specifications = [];

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $unmergedItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                's.id as ris_item_spec_id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
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
                ) as prexc',
                'ppmp_activity.id as activityId',
                'ppmp_activity.title as activityTitle',
                'ppmp_sub_activity.id as subActivityId',
                'ppmp_sub_activity.title as subActivityTitle',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                s.activity_id = ppmp_ppmp_item.activity_id and 
                                                s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                s.item_id = ppmp_ppmp_item.item_id and 
                                                s.cost = ppmp_pr_item.cost and 
                                                s.type = ppmp_pr_item.type')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['in', 'ppmp_pr_item.id', $aprItemIDs])
            ->groupBy(['ppmp_item.id', 's.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        $aprItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['in', 'ppmp_pr_item.id', $aprItemIDs])
            ->groupBy(['ppmp_item.id'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        if(!empty($unmergedItems))
        {
            foreach($unmergedItems as $item)
            {
                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }
            }
        }

        if($aprModel->load(Yii::$app->request->post()))
        {
            $aprModel->save(false);
        }

        return $this->renderAjax('\steps\request-quotations\request-apr',[
            'model' => $model,
            'agency' => $agency,
            'regionalOffice' => $regionalOffice,
            'address' => $address,
            'aprModel' => $aprModel,
            'unmergedItems' => $unmergedItems,
            'aprItems' => $aprItems,
            'supplier' => $supplier,
            'specifications' => $specifications,
            'shortName' => $shortName,
        ]);
    }

    // Request Quotation -> Print APR
    public function actionPrintApr($id)
    {
        $model = $this->findModel($id);
        $agency = Settings::findOne(['title' => 'Agency Name']);
        $regionalOffice = Settings::findOne(['title' => 'Regional Office']);
        $address = Settings::findOne(['title' => 'Address']);
        $shortName = Settings::findOne(['title' => 'Agency Short Name']);
        $apr = Apr::findOne(['pr_id' => $model->id]);
        $supplier = Supplier::findOne(['id' => 1]);
        $specifications = [];

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $unmergedItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                's.id as ris_item_spec_id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
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
                ) as prexc',
                'ppmp_activity.id as activityId',
                'ppmp_activity.title as activityTitle',
                'ppmp_sub_activity.id as subActivityId',
                'ppmp_sub_activity.title as subActivityTitle',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                s.activity_id = ppmp_ppmp_item.activity_id and 
                                                s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                s.item_id = ppmp_ppmp_item.item_id and 
                                                s.cost = ppmp_pr_item.cost and 
                                                s.type = ppmp_pr_item.type')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['in', 'ppmp_pr_item.id', $aprItemIDs])
            ->groupBy(['ppmp_item.id', 's.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        $aprItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['in', 'ppmp_pr_item.id', $aprItemIDs])
            ->groupBy(['ppmp_item.id'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        if(!empty($unmergedItems))
        {
            foreach($unmergedItems as $item)
            {
                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }
            }
        }

        return $this->renderAjax('\reports\apr',[
            'model' => $model,
            'agency' => $agency,
            'regionalOffice' => $regionalOffice,
            'address' => $address,
            'apr' => $apr,
            'aprItems' => $aprItems,
            'supplier' => $supplier,
            'specifications' => $specifications,
            'shortName' => $shortName,
        ]);
    }

    // Request Quotation -> Request RFQ
    public function actionRequestRfq($id)
    {
        $model = $this->findModel($id);

        $rfqs = Rfq::findAll(['pr_id' => $model->id]);

        $items = [];

        /* if($rfqs)
        {
            foreach($rfqs as $key => $rfq)
            {
                $items[$key]['label'] = '<table style="width:100%;" id="rfq-table-'.$rfq->id.'">';
                $items[$key]['label'] .= '<tr>';
                $items[$key]['label'] .= '<td><a href="javascript:void(0);" onclick="loadRfq('.$rfq->id.')">RFQ No. '.$rfq->rfq_no.'</a></td>';
                $items[$key]['label'] .= '<td align=right>';
                $items[$key]['label'] .= '<a href="javascript:void(0);" onclick="printRfq('.$rfq->id.');"><i class="fa fa-print"></i></a>&nbsp;&nbsp;';
                $items[$key]['label'] .=  Html::button('<i class="fa fa-edit"></i>', ['value' => Url::to(['/v1/pr/update-rfq', 'id' => $rfq->id]), 'class' => 'update-rfq-button button-link']).'&nbsp;&nbsp;';
                $items[$key]['label'] .= '<a href="javascript:void(0);" onclick="deleteRfq('.$model->id.','.$rfq->id.');" data-confirm="Are you sure you want to delete this item?" data-method="post"><i class="fa fa-trash"></i></a>';
                $items[$key]['label'] .= '</td>';
                $items[$key]['label'] .= '</tr>';
                $items[$key]['label'] .= '</table>';
                $items[$key]['content'] = '<div id="rfq-content-'.$rfq->id.'"></div>';
                $items[$key]['options'] = ['class' => 'panel panel-info'];
            }
        } */

        return $this->renderAjax('\steps\request-quotations\request-rfq', [
            'model' => $model,
            'rfqs' => $rfqs,
            'items' => $items,
        ]);
    }

    // Request Quotation -> Create RFQ
    public function actionCreateRfq($id)
    {
        $model = $this->findModel($id);

        $rfqModel = new Rfq();
        $rfqModel->pr_id = $model->id;

        if($rfqModel->load(Yii::$app->request->post()))
        {
            $time = str_pad($rfqModel->deadline_time, 2, '0', STR_PAD_LEFT).':'.str_pad($rfqModel->minute, 2, '0', STR_PAD_LEFT).' '.$rfqModel->meridian;
            $lastRfq = Rfq::find()->where(['pr_id' => $model->id])->orderBy(['id' => SORT_DESC])->one();
            $lastNumber = $lastRfq ? intval(substr($lastRfq->rfq_no, -2)) : '00';
            $rfqModel->rfq_no = $lastRfq ? $model->pr_no.'-'.str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT) : $model->pr_no.'-'.$lastNumber;
            $rfqModel->deadline_time = $time;
            $rfqModel->save();
        }

        return $this->renderAjax('\steps\request-quotations\rfq-form', [
            'model' => $model,
            'rfqModel' => $rfqModel,
        ]);
    }

    // Request Quotation -> Update RFQ
    public function actionUpdateRfq($id)
    {
        $rfqModel = Rfq::findOne($id);

        $model = $rfqModel->pr;
        $timeArray = explode(" ",$rfqModel->deadline_time);
        $time = explode(":", $timeArray[0]);
        $rfqModel->deadline_time = $time[0];
        $rfqModel->minute = isset($time[1]) ? $time[1] : '00';
        $rfqModel->meridian = isset($timeArray[1]) ? $timeArray[1] : 'AM';

        if($rfqModel->load(Yii::$app->request->post()))
        {
            $time = str_pad($rfqModel->deadline_time, 2, '0', STR_PAD_LEFT).':'.str_pad($rfqModel->minute, 2, '0', STR_PAD_LEFT).' '.$rfqModel->meridian;
            $rfqModel->deadline_time = $time;
            $rfqModel->save();
        }

        return $this->renderAjax('\steps\request-quotations\rfq-form', [
            'model' => $model,
            'rfqModel' => $rfqModel,
        ]);
    }

    // Request Quotation -> View RFQ
    public function actionViewRfq($id)
    {
        $rfq = Rfq::findOne(['id' => $id]);

        $model = $rfq->pr;

        $bac = Settings::findOne(['title' => 'BAC Chairperson']);
        $agency = Settings::findOne(['title' => 'Agency Name']);
        $regionalOffice = Settings::findOne(['title' => 'Regional Office']);
        $address = Settings::findOne(['title' => 'Address']);
        $email = Settings::findOne(['title' => 'Email']);
        $telephoneNos = Settings::findOne(['title' => 'Telephone Nos.']);
        $bacChairperson = Signatory::findOne(['emp_id' => $bac->value]);
        
        $specifications = [];
        $forContractItems = [];

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
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $unmergedItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                's.id as ris_item_spec_id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
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
                    ) as prexc',
                'ppmp_activity.id as activityId',
                'ppmp_activity.title as activityTitle',
                'ppmp_sub_activity.id as subActivityId',
                'ppmp_sub_activity.title as subActivityTitle',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                s.activity_id = ppmp_ppmp_item.activity_id and 
                                                s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                s.item_id = ppmp_ppmp_item.item_id and 
                                                s.cost = ppmp_pr_item.cost and 
                                                s.type = ppmp_pr_item.type')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
            ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 's.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        $lotItems = [];
        $rfqItems = $model->rfqItemsWithAprItems;
        if(!empty($rfqItems))
        {
            foreach($rfqItems as $item)
            {
                $lotItems[$item['lotTitle']][] = $item;
            }
        }

        if(!empty($unmergedItems))
        {
            foreach($unmergedItems as $item)
            {
                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }

                $forContractItem = ForContractItem::findOne(['item_id' => $item['item_id']]);
                if($forContractItem){ $forContractItems[$item['id']] = $forContractItem; }
            }
        }

        return $this->renderAjax('\steps\request-quotations\rfq', [
            'model' => $model,
            'rfq' => $rfq,
            'rfqItems' => $rfqItems,
            'lotItems' => $lotItems,
            'specifications' => $specifications,
            'bacChairperson' => $bacChairperson,
            'agency' => $agency,
            'regionalOffice' => $regionalOffice,
            'address' => $address,
            'email' => $email,
            'telephoneNos' => $telephoneNos,
            'forContractItems' => $forContractItems,
        ]);
    }

    // Request Quotation -> Print RFQ
    public function actionPrintRfq($id)
    {
        $rfq = Rfq::findOne(['id' => $id]);

        $model = $rfq->pr;

        $bac = Settings::findOne(['title' => 'BAC Chairperson']);
        $agency = Settings::findOne(['title' => 'Agency Name']);
        $regionalOffice = Settings::findOne(['title' => 'Regional Office']);
        $address = Settings::findOne(['title' => 'Address']);
        $email = Settings::findOne(['title' => 'Email']);
        $telephoneNos = Settings::findOne(['title' => 'Telephone Nos.']);

        $bacChairperson = Signatory::findOne(['emp_id' => $bac->value]);

        $specifications = [];
        $forContractItems = [];

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
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $unmergedItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                's.id as ris_item_spec_id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
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
                    ) as prexc',
                'ppmp_activity.id as activityId',
                'ppmp_activity.title as activityTitle',
                'ppmp_sub_activity.id as subActivityId',
                'ppmp_sub_activity.title as subActivityTitle',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                s.activity_id = ppmp_ppmp_item.activity_id and 
                                                s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                s.item_id = ppmp_ppmp_item.item_id and 
                                                s.cost = ppmp_pr_item.cost and 
                                                s.type = ppmp_pr_item.type')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
            ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 's.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        $lotItems = [];
        $rfqItems = $model->rfqItemsWithAprItems;

        if(!empty($rfqItems))
        {
            foreach($rfqItems as $item)
            {
                $lotItems[$item['lotTitle']][] = $item;
            }
        }
        
        if(!empty($unmergedItems))
        {
            foreach($unmergedItems as $item)
            {
                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }

                $forContractItem = ForContractItem::findOne(['item_id' => $item['item_id']]);
                if($forContractItem){ $forContractItems[$item['id']] = $forContractItem; }
            }
        }

        return $this->renderAjax('\reports\rfq', [
            'model' => $model,
            'rfq' => $rfq,
            'rfqItems' => $rfqItems,
            'lotItems' => $lotItems,
            'specifications' => $specifications,
            'bacChairperson' => $bacChairperson,
            'agency' => $agency,
            'regionalOffice' => $regionalOffice,
            'address' => $address,
            'email' => $email,
            'telephoneNos' => $telephoneNos,
            'forContractItems' => $forContractItems,
        ]);
    }

    // Request Quotation -> View Retrieved RFQ
    public function actionViewRfqInfo($rfq_id, $supplier_id)
    {
        $rfqInfo = RfqInfo::findOne(['rfq_id' => $rfq_id, 'supplier_id' => $supplier_id]);
        $rfq = Rfq::findOne(['id' => $rfq_id]); 
        $supplier = Supplier::findOne(['id' => $supplier_id]); 

        $model = $rfq->pr;

        $bac = Settings::findOne(['title' => 'BAC Chairperson']);
        $agency = Settings::findOne(['title' => 'Agency Name']);
        $regionalOffice = Settings::findOne(['title' => 'Regional Office']);
        $address = Settings::findOne(['title' => 'Address']);
        $email = Settings::findOne(['title' => 'Email']);
        $telephoneNos = Settings::findOne(['title' => 'Telephone Nos.']);

        $bacChairperson = Signatory::findOne(['emp_id' => $bac->value]);

        $specifications = [];
        $forContractItems = [];

        $aprItemsWithValueIDs = PrItemCost::find()
                            ->select(['pr_item_id'])
                            ->andWhere(['pr_id' => $model->id])
                            ->andWhere(['supplier_id' => 1])
                            ->andWhere(['>', 'cost', 0])
                            ->asArray()
                            ->all();
        
        $aprItemsWithValueIDs = ArrayHelper::map($aprItemsWithValueIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->andWhere(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = array_intersect($aprItemIDs, $aprItemsWithValueIDs);

        $nonProcurableItemIDs = NonProcurableItem::find()
                    ->select(['pr_item_id'])
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $unmergedItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                's.id as ris_item_spec_id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
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
                    ) as prexc',
                'ppmp_activity.id as activityId',
                'ppmp_activity.title as activityTitle',
                'ppmp_sub_activity.id as subActivityId',
                'ppmp_sub_activity.title as subActivityTitle',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                s.activity_id = ppmp_ppmp_item.activity_id and 
                                                s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                s.item_id = ppmp_ppmp_item.item_id and 
                                                s.cost = ppmp_pr_item.cost and 
                                                s.type = ppmp_pr_item.type')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
            ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 's.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();
        
        $rfqItemCosts = PrItemCost::find()
            ->select(['pr_item_id', 'specification', 'ppmp_pr_item_cost.cost'])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->where([
                'ppmp_pr_item_cost.pr_id' => $model->id, 
                'rfq_info_id' => $rfqInfo->id,
                'supplier_id' => $supplier->id
            ])
            ->groupBy(['ppmp_pr_item.ppmp_item_id', 'ppmp_pr_item_cost.cost'])
            ->createCommand()
            ->getRawSql();

        $rfqItems = PrItem::find()
        ->select([
            'ppmp_pr_item.id as id',
            'ppmp_item.id as item_id',
            'ppmp_item.title as item',
            'IF(ppmp_lot.title IS NOT NULL, concat("Lot No. ",ppmp_lot.lot_no," - ",ppmp_lot.title), 0) as lotTitle',
            'ppmp_item.unit_of_measure as unit',
            'ppmp_pr_item.cost as cost',
            'rfqItemCosts.cost as offer',
            'rfqItemCosts.specification as specification',
            'sum(ppmp_pr_item.quantity) as total'
        ])
        ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
        ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
        ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
        ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
        ->leftJoin(['rfqItemCosts' => '('.$rfqItemCosts.')'], 'rfqItemCosts.pr_item_id = ppmp_pr_item.id')
        ->andWhere([
            'ppmp_pr_item.pr_id' => $model->id,
        ])
        ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
        ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
        ->groupBy(['ppmp_item.id', 'lotTitle'])
        ->orderBy(['lotTitle' => SORT_ASC, 'item' => SORT_ASC])
        ->asArray()
        ->all();

        $lotItems = [];
        if(!empty($rfqItems))
        {
            foreach($rfqItems as $item)
            {
                $lotItems[$item['lotTitle']][] = $item;
            }
        }
        
        if(!empty($unmergedItems))
        {
            foreach($unmergedItems as $item)
            {
                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }

                $forContractItem = ForContractItem::findOne(['item_id' => $item['item_id']]);
                if($forContractItem){ $forContractItems[$item['id']] = $forContractItem; }
            }
        }

        return $this->renderAjax('\steps\retrieve-quotations\rfq_info', [
            'model' => $model,
            'rfq' => $rfq,
            'supplier' => $supplier,
            'rfqItems' => $rfqItems,
            'lotItems' => $lotItems,
            'specifications' => $specifications,
            'bacChairperson' => $bacChairperson,
            'agency' => $agency,
            'regionalOffice' => $regionalOffice,
            'address' => $address,
            'email' => $email,
            'telephoneNos' => $telephoneNos,
            'forContractItems' => $forContractItems,
        ]);
    }
    // Request Quotation -> Print Retrieved RFQ
    public function actionPrintRfqInfo($rfq_id, $supplier_id)
    {
        $rfqInfo = RfqInfo::findOne(['rfq_id' => $rfq_id, 'supplier_id' => $supplier_id]);
        $rfq = Rfq::findOne(['id' => $rfq_id]); 
        $supplier = Supplier::findOne(['id' => $supplier_id]); 

        $model = $rfq->pr;

        $bac = Settings::findOne(['title' => 'BAC Chairperson']);
        $agency = Settings::findOne(['title' => 'Agency Name']);
        $regionalOffice = Settings::findOne(['title' => 'Regional Office']);
        $address = Settings::findOne(['title' => 'Address']);
        $email = Settings::findOne(['title' => 'Email']);
        $telephoneNos = Settings::findOne(['title' => 'Telephone Nos.']);

        $bacChairperson = Signatory::findOne(['emp_id' => $bac->value]);

        $specifications = [];
        $forContractItems = [];

        $aprItemsWithValueIDs = PrItemCost::find()
                            ->select(['pr_item_id'])
                            ->andWhere(['pr_id' => $model->id])
                            ->andWhere(['supplier_id' => 1])
                            ->andWhere(['>', 'cost', 0])
                            ->asArray()
                            ->all();
        
        $aprItemsWithValueIDs = ArrayHelper::map($aprItemsWithValueIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->andWhere(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = array_intersect($aprItemIDs, $aprItemsWithValueIDs);

        $nonProcurableItemIDs = NonProcurableItem::find()
                    ->select(['pr_item_id'])
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $unmergedItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                's.id as ris_item_spec_id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
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
                    ) as prexc',
                'ppmp_activity.id as activityId',
                'ppmp_activity.title as activityTitle',
                'ppmp_sub_activity.id as subActivityId',
                'ppmp_sub_activity.title as subActivityTitle',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                s.activity_id = ppmp_ppmp_item.activity_id and 
                                                s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                s.item_id = ppmp_ppmp_item.item_id and 
                                                s.cost = ppmp_pr_item.cost and 
                                                s.type = ppmp_pr_item.type')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
            ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 's.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        $rfqItemCosts = PrItemCost::find()
            ->select(['pr_item_id', 'specification', 'ppmp_pr_item_cost.cost'])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->where([
                'ppmp_pr_item_cost.pr_id' => $model->id, 
                'rfq_info_id' => $rfqInfo->id,
                'supplier_id' => $supplier->id
            ])
            ->groupBy(['ppmp_pr_item.ppmp_item_id', 'ppmp_pr_item_cost.cost'])
            ->createCommand()
            ->getRawSql();

        $rfqItems = PrItem::find()
        ->select([
            'ppmp_pr_item.id as id',
            'ppmp_item.id as item_id',
            'ppmp_item.title as item',
            'IF(ppmp_lot.title IS NOT NULL, concat("Lot No. ",ppmp_lot.lot_no," - ",ppmp_lot.title), 0) as lotTitle',
            'ppmp_item.unit_of_measure as unit',
            'ppmp_pr_item.cost as cost',
            'rfqItemCosts.cost as offer',
            'rfqItemCosts.specification as specification',
            'sum(ppmp_pr_item.quantity) as total'
        ])
        ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
        ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
        ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
        ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
        ->leftJoin(['rfqItemCosts' => '('.$rfqItemCosts.')'], 'rfqItemCosts.pr_item_id = ppmp_pr_item.id')
        ->andWhere([
            'ppmp_pr_item.pr_id' => $model->id,
        ])
        ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
        ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
        ->groupBy(['ppmp_item.id', 'lotTitle'])
        ->orderBy(['lotTitle' => SORT_ASC, 'item' => SORT_ASC])
        ->asArray()
        ->all();
        
        $lotItems = [];
        if(!empty($rfqItems))
        {
            foreach($rfqItems as $item)
            {
                $lotItems[$item['lotTitle']][] = $item;
            }
        }
        
        if(!empty($unmergedItems))
        {
            foreach($unmergedItems as $item)
            {
                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }

                $forContractItem = ForContractItem::findOne(['item_id' => $item['item_id']]);
                if($forContractItem){ $forContractItems[$item['id']] = $forContractItem; }
            }
        }

        return $this->renderAjax('\reports\rfq_info', [
            'model' => $model,
            'rfq' => $rfq,
            'supplier' => $supplier,
            'rfqItems' => $rfqItems,
            'lotItems' => $lotItems,
            'specifications' => $specifications,
            'bacChairperson' => $bacChairperson,
            'agency' => $agency,
            'regionalOffice' => $regionalOffice,
            'address' => $address,
            'email' => $email,
            'telephoneNos' => $telephoneNos,
            'forContractItems' => $forContractItems,
        ]);
    }

    // Request Quotation -> Delete RFQ
    public function actionDeleteRfq($id, $rfq_id)
    {
        $model = $this->findModel($id);

        $rfq = Rfq::findOne(['id' => $rfq_id]);

        if($rfq->delete())
        {
            Bid::deleteAll(['rfq_id' => $rfq_id]);
            RfqInfo::deleteAll(['rfq_id' => $rfq_id]);
            PrItemCost::deleteAll(['rfq_id' => $rfq_id]);
        }
    }

    // Retrieve Quotations -> Retrieve APR
    public function actionRetrieveApr($id)
    {
        $model = $this->findModel($id);

        return $this->renderAjax('\steps\retrieve-quotations\retrieve-apr', [
            'model' => $model,
        ]);
    }

    // Retrieve Quotations -> Input Quotation from APR 
    public function actionAprQuotation($id)
    {
        $model = $this->findModel($id);

        $aprItems = [];
        $specifications = [];
        $costModels = [];

        $supplier = Supplier::findOne(['id' => 1]);

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $aprItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['in', 'ppmp_pr_item.id', $aprItemIDs])
            ->groupBy(['ppmp_item.id'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        $itemIDs = ArrayHelper::map($aprItems, 'id', 'id');
        
        if(!empty($aprItems))
        {
            foreach($aprItems as $item)
            {
                $cost = PrItemCost::findOne(['pr_id' => $model->id, 'pr_item_id' => $item['id'], 'supplier_id' => $supplier->id]) ? PrItemCost::findOne(['pr_id' => $model->id, 'pr_item_id' => $item['id'], 'supplier_id' => $supplier->id]) : new PrItemCost();
                $cost->pr_id = $model->id;
                $cost->pr_item_id = $item['id'];
                $cost->supplier_id = $supplier->id;
                $cost->cost = $cost->isNewRecord ? 0 : $cost->cost;

                $costModels[$item['id']] = $cost; 
            }
        }

        if(MultipleModel::loadMultiple($costModels, Yii::$app->request->post()))
        {
            if(!empty($costModels))
            {
                foreach($costModels as $costModel)
                {
                    if($costModel->cost > 0)
                    {
                        $item = PrItem::findOne($costModel->pr_item_id);

                        $includedItems = PrItem::find()
                            ->select(['ppmp_pr_item.id as id'])
                            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                            ->andWhere([
                                'ppmp_pr_item.pr_id' => $costModel->pr_id,
                                'ppmp_item.id' => $item->ppmpItem->item_id,
                            ])
                            ->all();
                        
                        $includedItems = ArrayHelper::map($includedItems, 'id', 'id');

                        if(!empty($includedItems))
                        {
                            foreach($includedItems as $includedItem)
                            {
                                $cost = PrItemCost::findOne(['pr_id' => $model->id, 'pr_item_id' => $includedItem, 'supplier_id' => $supplier->id]) ? PrItemCost::findOne(['pr_id' => $model->id, 'pr_item_id' => $includedItem, 'supplier_id' => $supplier->id]) : new PrItemCost();
                                $cost->pr_id = $costModel->pr_id;
                                $cost->pr_item_id = $includedItem;
                                $cost->supplier_id = $supplier->id;
                                $cost->cost = $costModel->cost;
                                $cost->save();
                            }
                        }
                    }
                }
            }
        }

        return $this->renderAjax('\steps\retrieve-quotations\apr-quotation-form', [
            'model' => $model,
            'aprItems' => $aprItems,
            'costModels' => $costModels,
            //'specifications' => $specifications,
            'supplier' => $supplier,
            'itemIDs' => $itemIDs,
        ]);
    }

    // Retrieve Quotations -> Retrieve RFQ
    public function actionRetrieveRfq($id)
    {
        $model = $this->findModel($id);

        $rfqs = Rfq::findAll(['pr_id' => $model->id]);

        return $this->renderAjax('\steps\retrieve-quotations\retrieve-rfq', [
            'model' => $model,
            'rfqs' => $rfqs,
        ]);
    }

    // Retrieve Quotations -> Retrieve Quotation
    public function actionRetrieveRfqQuotation($id)
    {
        $model = $this->findModel($id);

        $rfqInfoModel = new RfqInfo();

        $existingSupplierIDs = PrItemCost::find()->select(['supplier_id'])->andWhere(['pr_id' => $model->id])->andWhere(['<>', 'supplier_id', 1])->groupBy(['supplier_id'])->asArray()->all();
        $existingSupplierIDs = ArrayHelper::map($existingSupplierIDs, 'supplier_id', 'supplier_id');

        $suppliers = Supplier::find()->select(['id', 'concat(business_name," (",business_address,")") as title'])->where(['not in', 'id', $existingSupplierIDs])->andWhere(['<>', 'id', 1])->asArray()->all();
        $suppliers = ArrayHelper::map($suppliers, 'id', 'title');

        $rfqs = Rfq::find()->select(['id', 'concat("RFQ No. ",rfq_no) as title'])->where(['pr_id' => $model->id])->asArray()->all();
        $rfqs = ArrayHelper::map($rfqs, 'id', 'title');

        $aprItemsWithValueIDs = PrItemCost::find()
                            ->select(['pr_item_id'])
                            ->andWhere(['pr_id' => $model->id])
                            ->andWhere(['supplier_id' => 1])
                            ->andWhere(['>', 'cost', 0])
                            ->asArray()
                            ->all();
        
        $aprItemsWithValueIDs = ArrayHelper::map($aprItemsWithValueIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->andWhere(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = array_intersect($aprItemIDs, $aprItemsWithValueIDs);

        $nonProcurableItemIDs = NonProcurableItem::find()
                    ->select(['pr_item_id'])
                    ->where(['pr_id' => $model->id])
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
        ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
        ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
        ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
        ->andWhere([
            'ppmp_pr_item.pr_id' => $model->id,
        ])
        ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
        ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
        ->groupBy(['ppmp_item.id', 'lotTitle'])
        ->orderBy(['lotTitle' => SORT_ASC, 'item' => SORT_ASC])
        ->asArray()
        ->all(); 

        $lotItems = [];
        if(!empty($rfqItems))
        {
            foreach($rfqItems as $item)
            {
                $lotItems[$item['lotTitle']][] = $item;
            }
        }

        $itemIDs = ArrayHelper::map($rfqItems, 'id', 'id');
        
        $costModels = [];
        $specifications = [];

        if(!empty($rfqItems))
        {
            foreach($rfqItems as $item)
            {
                $cost = new PrItemCost();
                $cost->pr_id = $model->id;
                $cost->pr_item_id = $item['id'];
                $cost->cost = 0;

                $costModels[$item['id']] = $cost;
            }
        }

        if($rfqInfoModel->load(Yii::$app->request->post()) && MultipleModel::loadMultiple($costModels, Yii::$app->request->post()))
        {
            if($rfqInfoModel->save())
            {
                if(!empty($costModels))
                {
                    foreach($costModels as $costModel)
                    {
                        if($costModel->cost > 0)
                        {
                            $item = PrItem::findOne($costModel->pr_item_id);
    
                            $includedItems = PrItem::find()
                                ->select(['ppmp_pr_item.id as id', 'IF(ppmp_lot.title IS NOT NULL, concat("Lot No. ",ppmp_lot.lot_no," - ",ppmp_lot.title), 0) as lotTitle'])
                                ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
                                ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
                                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                                ->andWhere([
                                    'ppmp_pr_item.pr_id' => $model->id,
                                    'ppmp_item.id' => $item->ppmpItem->item_id,
                                    'ppmp_pr_item.cost' => $item->cost,
                                ])
                                ->having(['lotTitle' => !is_null($item->lot) ? 'Lot No. '.$item->lot->lot_no.' - '.$item->lot->title : 0])
                                ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
                                ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                                ->all();
                            
                            $includedItems = ArrayHelper::map($includedItems, 'id', 'id');

                            if(!empty($includedItems))
                            {
                                foreach($includedItems as $includedItem)
                                {
                                    $cost = new PrItemCost();
                                    $cost->pr_id = $model->id;
                                    $cost->pr_item_id = $includedItem;
                                    $cost->supplier_id = $rfqInfoModel->supplier_id;
                                    $cost->rfq_id = $rfqInfoModel->rfq_id;
                                    $cost->rfq_info_id = $rfqInfoModel->id;
                                    $cost->specification = $costModel->specification;
                                    $cost->cost = $costModel->cost;
                                    $cost->save(false);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->renderAjax('\steps\retrieve-quotations\rfq-quotation-form', [
            'model' => $model,
            'rfqInfoModel' => $rfqInfoModel,
            'rfqs' => $rfqs,
            'rfqItems' => $rfqItems,
            'lotItems' => $lotItems,
            'suppliers' => $suppliers,
            'costModels' => $costModels,
            'specifications' => $specifications,
            'itemIDs' => $itemIDs,
            'action' => 'create'
        ]);
    }

    // Retrieve Quotations -> Update Retrieved Quotation
    public function actionUpdateRfqQuotation($id, $rfq_id, $supplier_id)
    {
        $model = $this->findModel($id);

        $rfqInfoModel = RfqInfo::findOne(['rfq_id' => $rfq_id, 'supplier_id' => $supplier_id]);

        $existingSupplierIDs = PrItemCost::find()->select(['supplier_id'])->andWhere(['pr_id' => $model->id])->andWhere(['<>', 'supplier_id', 1])->andWhere(['<>', 'supplier_id', $supplier_id])->groupBy(['supplier_id'])->asArray()->all();
        $existingSupplierIDs = ArrayHelper::map($existingSupplierIDs, 'supplier_id', 'supplier_id');

        $suppliers = Supplier::find()->select(['id', 'concat(business_name," (",business_address,")") as title'])->where(['not in', 'id', $existingSupplierIDs])->andWhere(['<>', 'id', 1])->asArray()->all();
        $suppliers = ArrayHelper::map($suppliers, 'id', 'title');

        $rfqs = Rfq::find()->select(['id', 'concat("RFQ No. ",rfq_no) as title'])->where(['pr_id' => $model->id])->asArray()->all();
        $rfqs = ArrayHelper::map($rfqs, 'id', 'title');

        $aprItemsWithValueIDs = PrItemCost::find()
                            ->select(['pr_item_id'])
                            ->andWhere(['pr_id' => $model->id])
                            ->andWhere(['supplier_id' => 1])
                            ->andWhere(['>', 'cost', 0])
                            ->asArray()
                            ->all();
        
        $aprItemsWithValueIDs = ArrayHelper::map($aprItemsWithValueIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->andWhere(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = array_intersect($aprItemIDs, $aprItemsWithValueIDs);

        $nonProcurableItemIDs = NonProcurableItem::find()
                    ->select(['pr_item_id'])
                    ->where(['pr_id' => $model->id])
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
            ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
            ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
            ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 'lotTitle'])
            ->orderBy(['lotTitle' => SORT_ASC, 'item' => SORT_ASC])
            ->asArray()
            ->all(); 
    
        $lotItems = [];
        if(!empty($rfqItems))
        {
            foreach($rfqItems as $item)
            {
                $lotItems[$item['lotTitle']][] = $item;
            }
        }   

        $itemIDs = ArrayHelper::map($rfqItems, 'id', 'id');
        
        $costModels = [];
        $specifications = [];

        if(!empty($rfqItems))
        {
            foreach($rfqItems as $item)
            {
                $cost = PrItemCost::findOne(['pr_id' => $model->id, 'rfq_id' => $rfq_id, 'rfq_info_id' => $rfqInfoModel->id, 'supplier_id' => $supplier_id, 'pr_item_id' => $item['id']]) ? PrItemCost::findOne(['pr_id' => $model->id, 'rfq_id' => $rfq_id, 'rfq_info_id' => $rfqInfoModel->id, 'supplier_id' => $supplier_id, 'pr_item_id' => $item['id']]) : new PrItemCost();

                $cost->pr_id = $model->id;
                $cost->pr_item_id = $item['id'];
                $cost->rfq_info_id = $rfqInfoModel->id;
                $cost->supplier_id = $supplier_id;
                $cost->cost = $cost->isNewRecord ? 0 : $cost->cost; 

                $costModels[$item['id']] = $cost;
            }
        }

        if($rfqInfoModel->load(Yii::$app->request->post()) && MultipleModel::loadMultiple($costModels, Yii::$app->request->post()))
        {
            if($rfqInfoModel->save(false))
            {
                if(!empty($costModels))
                {
                    foreach($costModels as $costModel)
                    {
                        if($costModel->cost > 0)
                        {
                            $item = PrItem::findOne($costModel->pr_item_id);

                            $includedItems = PrItem::find()
                                ->select(['ppmp_pr_item.id as id', 'IF(ppmp_lot.title IS NOT NULL, concat("Lot No. ",ppmp_lot.lot_no," - ",ppmp_lot.title), 0) as lotTitle'])
                                ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
                                ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
                                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                                ->andWhere([
                                    'ppmp_pr_item.pr_id' => $model->id,
                                    'ppmp_item.id' => $item->ppmpItem->item_id,
                                    'ppmp_pr_item.cost' => $item->cost,
                                ])
                                ->having(['lotTitle' => !is_null($item->lot) ? 'Lot No. '.$item->lot->lot_no.' - '.$item->lot->title : 0])
                                ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
                                ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                                ->all();
                            
                            $includedItems = ArrayHelper::map($includedItems, 'id', 'id');

                            if(!empty($includedItems))
                            {
                                foreach($includedItems as $includedItem)
                                {
                                    $cost = PrItemCost::findOne(['pr_item_id' => $includedItem, 'pr_id' => $model->id, 'supplier_id' => $rfqInfoModel->supplier_id, 'rfq_id' => $rfqInfoModel->rfq_id, 'rfq_info_id' => $rfqInfoModel->id]) ? PrItemCost::findOne(['pr_item_id' => $includedItem, 'pr_id' => $model->id, 'supplier_id' => $rfqInfoModel->supplier_id, 'rfq_id' => $rfqInfoModel->rfq_id, 'rfq_info_id' => $rfqInfoModel->id]) : new PrItemCost();
                                    $cost->pr_id = $model->id;
                                    $cost->pr_item_id = $includedItem;
                                    $cost->supplier_id = $rfqInfoModel->supplier_id;
                                    $cost->rfq_id = $rfqInfoModel->rfq_id;
                                    $cost->rfq_info_id = $rfqInfoModel->id;
                                    $cost->specification = $costModel->specification;
                                    $cost->cost = $costModel->cost;
                                    $cost->save(false);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->renderAjax('\steps\retrieve-quotations\rfq-quotation-form', [
            'model' => $model,
            'rfqInfoModel' => $rfqInfoModel,
            'rfqs' => $rfqs,
            'rfqItems' => $rfqItems,
            'lotItems' => $lotItems,
            'suppliers' => $suppliers,
            'costModels' => $costModels,
            'specifications' => $specifications,
            'itemIDs' => $itemIDs,
            'action' => 'update'
        ]);
    }

    // Retrieve Quotations -> Delete RFQ Info
    public function actionDeleteRfqInfo($rfq_id, $supplier_id)
    {
        $rfqInfo = RfqInfo::findOne(['rfq_id' => $rfq_id, 'supplier_id' => $supplier_id]);
        $rfq_info_id = $rfqInfo->id;

        if($rfqInfo->delete())
        {
            PrItemCost::deleteAll(['rfq_info_id' => $rfq_info_id]);
        }
    }

    // Canvass/Bid Items -> Bid Selected RFQ
    public function actionBidRfq($id, $rfq_id, $i)
    {
        $model = $this->findModel($id);
        $rfq = Rfq::findOne($rfq_id);
        $bid = Bid::findOne(['pr_id' => $model->id, 'rfq_id' => $rfq->id]);

        $aprItemsWithValueIDs = PrItemCost::find()
                            ->select(['pr_item_id'])
                            ->andWhere(['pr_id' => $model->id])
                            ->andWhere(['supplier_id' => 1])
                            ->andWhere(['>', 'cost', 0])
                            ->asArray()
                            ->all();
        
        $aprItemsWithValueIDs = ArrayHelper::map($aprItemsWithValueIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->andWhere(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = array_intersect($aprItemIDs, $aprItemsWithValueIDs);

        $nonProcurableItemIDs = NonProcurableItem::find()
                    ->select(['pr_item_id'])
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $suppliers = [];
        $winners = [];

        if($rfq)
        {
            $supplierIDs = PrItemCost::find()->select(['supplier_id'])->andWhere(['pr_id' => $model->id, 'rfq_id' => $rfq->id])->andWhere(['<>', 'supplier_id', 1])->groupBy(['supplier_id'])->asArray()->all();

            $supplierIDs = ArrayHelper::map($supplierIDs, 'supplier_id', 'supplier_id');

            $suppliers = Supplier::find()->where(['in', 'id', $supplierIDs])->all();

            $bidWinners = $bid ? BidWinner::find()->andWhere(['bid_id' => $bid->id])->asArray()->all() : [];

            if(!empty($bidWinners))
            {
                foreach($bidWinners as $bidWinner)
                {
                    if(!empty($bidWinner)){
                        $winners[$bidWinner['pr_item_id']][$bidWinner['supplier_id']] = $bidWinner; 
                        $winners[$bidWinner['pr_item_id']]['justification'] = $bidWinner['justification']; 
                    }
                    
                    $winners[$bidWinner['pr_item_id']]['winner'] = Supplier::findOne($bidWinner['supplier_id']) ? Supplier::findOne($bidWinner['supplier_id']) : []; 
                }
            }
        }

        $rfqItems = $model->rfqItemsWithAprItems;

        $prItemIDs = ArrayHelper::map($rfqItems, 'id', 'id');

        $rfqItemCosts = PrItemCost::find()
            ->select([
                'ppmp_pr_item_cost.pr_item_id', 
                'ppmp_pr_item_cost.rfq_id', 
                'ppmp_pr_item_cost.rfq_info_id', 
                'ppmp_pr_item_cost.supplier_id', 
                'ppmp_pr_item_cost.specification', 
                'ppmp_pr_item_cost.cost',
                'sum(ppmp_pr_item.quantity) as total'
                ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
            ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item_cost.pr_id' => $model->id, 
                'ppmp_pr_item_cost.rfq_id' => $rfq->id,
            ])
            ->andWhere(['ppmp_pr_item_cost.pr_item_id' => $prItemIDs])
            ->groupBy(['ppmp_pr_item_cost.supplier_id', 'ppmp_pr_item_cost.cost', 'ppmp_lot.id'])
            ->asArray()
            ->all();
        
        $costs = [];
        $lotItems = [];

        if(!empty($rfqItems))
        {
            foreach($rfqItems as $item)
            {
                $lotItems[$item['lotTitle']][] = $item;
            }
        }
                
        if(!empty($rfqItemCosts))
        {
            foreach($rfqItemCosts as $rfqItem)
            {
                $costs[$rfqItem['pr_item_id']][$rfqItem['supplier_id']] = $rfqItem;
            }
        }

        return $this->renderAjax('\steps\bid-items\bid', [
            'model' => $model,
            'bid' => $bid,
            'i' => $i,
            'rfq' => $rfq,
            'suppliers' => $suppliers,
            'winners' => $winners,
            'rfqItems' => $rfqItems,
            'lotItems' => $lotItems,
            'costs' => $costs,
        ]);
    }

    // Canvass/Bid Items -> Create Bid
    public function actionCreateBid($id, $rfq_id, $i)
    {
        $model = $this->findModel($id);

        $rfq = Rfq::findOne($rfq_id);

        $chair = Settings::findOne(['title' => 'BAC Chairperson']);
        $viceChair = Settings::findOne(['title' => 'BAC Vice-Chairperson']);
        $member = Settings::findOne(['title' => 'BAC Member']);
        $endUser = BacMember::findOne(['office_id' => $model->office_id, 'bac_group' => 'End User']);

        $signatories = Signatory::find()->all();
        $signatories = ArrayHelper::map($signatories, 'emp_id', 'name');

        $experts = BacMember::find()
                ->select(['ppmp_signatory.emp_id', 'ppmp_signatory.name'])
                ->leftJoin('ppmp_signatory','ppmp_signatory.emp_id = ppmp_bac_member.emp_id')
                ->where(['bac_group' => 'Technical Expert'])
                ->asArray()
                ->all();
        
        $experts = ArrayHelper::map($experts, 'emp_id', 'name');

        $endUsers = BacMember::find()
                ->select(['ppmp_signatory.emp_id', 'ppmp_signatory.name'])
                ->leftJoin('ppmp_signatory','ppmp_signatory.emp_id = ppmp_bac_member.emp_id')
                ->leftJoin('tbloffice', 'tbloffice.id = ppmp_signatory.office_id')
                ->where(['tbloffice.abbreviation' => $model->office_id, 'bac_group' => 'End User'])
                ->asArray()
                ->all();

        $endUsers = ArrayHelper::map($endUsers, 'emp_id', 'name');

        $bidModel = new Bid();
        $bidModel->scenario = 'createBid';
        $bidModel->pr_id = $model->id;
        $bidModel->rfq_id = $rfq_id;

        $memberModels = [];
        $chairModel = new BidMember();
        $chairModel->emp_id = $chair->value;
        $chairModel->position = 'BAC Chairperson';
        $memberModels[$chairModel->position] = $chairModel;

        $viceChairModel = new BidMember();
        $viceChairModel->emp_id = $viceChair->value;
        $viceChairModel->position = 'BAC Vice-Chairperson';
        $memberModels[$viceChairModel->position] = $viceChairModel;

        $memberModel = new BidMember();
        $memberModel->emp_id = $member->value;
        $memberModel->position = 'BAC Member';
        $memberModels[$memberModel->position] = $memberModel;

        $expertModel = new BidMember();
        $expertModel->position = 'Provisional Member';
        $memberModels[$expertModel->position] = $expertModel;

        $endUserModel = new BidMember();
        $endUserModel->emp_id = $endUser->emp_id;
        $endUserModel->position = 'Provisional Member - End User';
        $memberModels[$endUserModel->position] = $endUserModel;

        if($bidModel->load(Yii::$app->request->post()) && MultipleModel::loadMultiple($memberModels, Yii::$app->request->post()))
        {
            $time = str_pad($bidModel->time_opened, 2, '0', STR_PAD_LEFT).':'.str_pad($bidModel->minute, 2, '0', STR_PAD_LEFT).' '.$bidModel->meridian;
            $bidModel->bid_no = $model->pr_no.'-00';
            $bidModel->time_opened = $time;
            if($bidModel->save())
            {
                $chairModel->bid_id = $bidModel->id;
                $chairModel->save();

                $viceChairModel->bid_id = $bidModel->id;
                $viceChairModel->save();

                $memberModel->bid_id = $bidModel->id;
                $memberModel->save();

                $expertModel->bid_id = $bidModel->id;
                $expertModel->save();

                $endUserModel->bid_id = $bidModel->id;
                $endUserModel->save();
            }
        }

        return $this->renderAjax('\steps\bid-items\bid-form', [
            'model' => $model,
            'rfq' => $rfq,
            'i' => $i,
            'signatories' => $signatories,
            'experts' => $experts,
            'endUsers' => $endUsers,
            'bidModel' => $bidModel,
            'memberModels' => $memberModels,
            'chairModel' => $chairModel,
            'viceChairModel' => $viceChairModel,
            'memberModel' => $memberModel,
            'expertModel' => $expertModel,
            'endUserModel' => $endUserModel,
        ]);
    }

    // Canvass/Bid Items -> Edit Bid
    public function actionUpdateBid($id, $i)
    {
        $bidModel = Bid::findOne(['id' => $id]);
        $bidModel->scenario = 'createBid';

        $rfq = $bidModel->rfq;
        $model = $this->findModel($bidModel->pr_id);

        $chair = Settings::findOne(['title' => 'BAC Chairperson']);
        $viceChair = Settings::findOne(['title' => 'BAC Vice-Chairperson']);
        $member = Settings::findOne(['title' => 'BAC Member']);
        $endUser = BacMember::findOne(['office_id' => $model->office_id, 'bac_group' => 'End User']);

        $signatories = Signatory::find()->all();
        $signatories = ArrayHelper::map($signatories, 'emp_id', 'name');

        $experts = BacMember::find()
                ->select(['ppmp_signatory.emp_id', 'ppmp_signatory.name'])
                ->leftJoin('ppmp_signatory','ppmp_signatory.emp_id = ppmp_bac_member.emp_id')
                ->where(['bac_group' => 'Technical Expert'])
                ->asArray()
                ->all();
        
        $experts = ArrayHelper::map($experts, 'emp_id', 'name');

        $endUsers = BacMember::find()
                ->select(['ppmp_signatory.emp_id', 'ppmp_signatory.name'])
                ->leftJoin('ppmp_signatory','ppmp_signatory.emp_id = ppmp_bac_member.emp_id')
                ->leftJoin('tbloffice', 'tbloffice.id = ppmp_signatory.office_id')
                ->where(['tbloffice.abbreviation' => $model->office_id, 'bac_group' => 'End User'])
                ->asArray()
                ->all();

        $endUsers = ArrayHelper::map($endUsers, 'emp_id', 'name');

        $memberModels = [];
        $chairModel = BidMember::findOne(['bid_id' => $bidModel->id, 'position' => 'BAC Chairperson']);
        $memberModels[$chairModel->position] = $chairModel;

        $viceChairModel = BidMember::findOne(['bid_id' => $bidModel->id, 'position' => 'BAC Vice-Chairperson']);
        $memberModels[$viceChairModel->position] = $viceChairModel;

        $memberModel = BidMember::findOne(['bid_id' => $bidModel->id, 'position' => 'BAC Member']);
        $memberModels[$memberModel->position] = $memberModel;

        $expertModel = BidMember::findOne(['bid_id' => $bidModel->id, 'position' => 'Provisional Member']);
        $memberModels[$expertModel->position] = $expertModel;

        $endUserModel = BidMember::findOne(['bid_id' => $bidModel->id, 'position' => 'Provisional Member - End User']);
        $memberModels[$endUserModel->position] = $endUserModel;

        $timeArray = explode(" ",$bidModel->time_opened);
        $time = explode(":", $timeArray[0]);
        $bidModel->time_opened = $time[0];
        $bidModel->minute = isset($time[1]) ? $time[1] : '00';
        $bidModel->meridian = isset($timeArray[1]) ? $timeArray[1] : 'AM';

        if($bidModel->load(Yii::$app->request->post()) && MultipleModel::loadMultiple($memberModels, Yii::$app->request->post()))
        {
            $time = str_pad($bidModel->time_opened, 2, '0', STR_PAD_LEFT).':'.str_pad($bidModel->minute, 2, '0', STR_PAD_LEFT).' '.$bidModel->meridian;
            $bidModel->time_opened = $time;
            if($bidModel->save())
            {
                $chairModel->bid_id = $bidModel->id;
                $chairModel->save();

                $viceChairModel->bid_id = $bidModel->id;
                $viceChairModel->save();

                $memberModel->bid_id = $bidModel->id;
                $memberModel->save();

                $expertModel->bid_id = $bidModel->id;
                $expertModel->save();

                $endUserModel->bid_id = $bidModel->id;
                $endUserModel->save();
            }
        }

        return $this->renderAjax('\steps\bid-items\bid-form', [
            'model' => $model,
            'rfq' => $rfq,
            'i' => $i,
            'signatories' => $signatories,
            'experts' => $experts,
            'endUsers' => $endUsers,
            'bidModel' => $bidModel,
            'memberModels' => $memberModels,
            'chairModel' => $chairModel,
            'viceChairModel' => $viceChairModel,
            'memberModel' => $memberModel,
            'expertModel' => $expertModel,
            'endUserModel' => $endUserModel,
        ]);
    }

    // Canvass/Bid Items -> Delete Bid
    public function actionDeleteBid($id)
    {
        $bid = Bid::findOne(['id' => $id]);
        $bid->delete();
    }

    // Canvass/Bid Items -> Select Winners
    public function actionSelectWinner($id, $i)
    {
        $bid = Bid::findOne(['id' => $id]);
        $bid->scenario = 'selectWinner';

        $rfq = Rfq::findOne(['id' => $bid->rfq_id]);
        $model = $this->findModel($bid->pr_id);
        $letters = range('A', 'Z');


        $supplierIDs = PrItemCost::find()->select(['supplier_id'])->andWhere(['pr_id' => $model->id, 'rfq_id' => $rfq->id])->andWhere(['<>', 'supplier_id', 1])->groupBy(['supplier_id'])->asArray()->all();
        $supplierIDs = ArrayHelper::map($supplierIDs, 'supplier_id', 'supplier_id');

        $supplierList = Supplier::find()->where(['in', 'id', $supplierIDs])->all();
        $supplierLetters = [];

        if($supplierList)
        {
            foreach($supplierList as $idx => $supp)
            {
                $supplierLetters[$supp->id] = $letters[$idx];
            }
        }

        $aprItemsWithValueIDs = PrItemCost::find()
                            ->select(['pr_item_id'])
                            ->andWhere(['pr_id' => $model->id])
                            ->andWhere(['supplier_id' => 1])
                            ->andWhere(['>', 'cost', 0])
                            ->asArray()
                            ->all();
        
        $aprItemsWithValueIDs = ArrayHelper::map($aprItemsWithValueIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->andWhere(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = array_intersect($aprItemIDs, $aprItemsWithValueIDs);

        $nonProcurableItemIDs = NonProcurableItem::find()
                    ->select(['pr_item_id'])
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $rfqItems = $model->rfqItemsWithAprItems;
        
        $rfqItemIDs = ArrayHelper::map($rfqItems, 'id', 'id');

        $rfqItemCosts = PrItemCost::find()
            ->select([
                'ppmp_pr_item_cost.pr_item_id', 
                'ppmp_pr_item_cost.rfq_id', 
                'ppmp_pr_item_cost.rfq_info_id', 
                'ppmp_pr_item_cost.supplier_id', 
                'ppmp_pr_item_cost.specification', 
                'ppmp_pr_item_cost.cost',
                'sum(ppmp_pr_item.quantity) as total'
                ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
            ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item_cost.pr_id' => $model->id, 
                'ppmp_pr_item_cost.rfq_id' => $rfq->id,
            ])
            ->andWhere(['ppmp_pr_item_cost.pr_item_id' => $rfqItemIDs])
            ->groupBy(['ppmp_pr_item_cost.supplier_id', 'ppmp_pr_item_cost.cost', 'ppmp_lot.id'])
            ->asArray()
            ->all();
        
        $suppliers = [];
        $winnerModels = [];
        $lotItems = [];
        
        if(!empty($rfqItems))
        {
            foreach($rfqItems as $item)
            {
                $winnerModel = BidWinner::findOne(['bid_id' => $bid->id, 'pr_item_id' => $item['id']]) ? BidWinner::findOne(['bid_id' => $bid->id, 'pr_item_id' => $item['id']]) : new BidWinner();
                $winnerModel->bid_id = $bid->id;
                $winnerModel->pr_item_id = $item['id'];
                $winnerModel->supplier_id = is_null($winnerModel->supplier_id) ? 0 : $winnerModel->supplier_id;

                $bidders = PrItemCost::find()
                ->select(['supplier_id'])
                ->andWhere(['pr_id' => $model->id, 'rfq_id' => $rfq->id, 'pr_item_id' => $item['id']])
                ->andWhere(['>', 'cost', 0])
                ->asArray()
                ->all();

                $bidders = ArrayHelper::map($bidders, 'supplier_id', 'supplier_id');
                $bidderIDs = [];
                $bidders = Supplier::find()->select(['id', 'concat(business_name," (",business_address,")") as title'])->where(['in', 'id', $bidders])->andWhere(['<>', 'id', 1])->asArray()->all();
                if(!empty($bidders))
                {
                    foreach($bidders as $idx => $bidder)
                    {
                        $bidderIDs[$bidder['id']] = $supplierLetters[$bidder['id']];
                    }
                }

                $suppliers[$item['id']] = $bidderIDs;
                $suppliers[$item['id']]['0'] = 'Failed';
                $winnerModels[$item['id']] = $winnerModel;
                $lotItems[$item['lotTitle']][] = $item;
            }
        }
        
        $costs = [];
                
        if(!empty($rfqItemCosts))
        {
            foreach($rfqItemCosts as $cost)
            {
                $costs[$cost['pr_item_id']][$cost['supplier_id']] = $cost;
            }
        }

        if($bid->load(Yii::$app->request->post()) && MultipleModel::loadMultiple($winnerModels, Yii::$app->request->post()))
        {
            $bid->save();
            
            if(!empty($winnerModels))
            {
                foreach($winnerModels as $winnerModel)
                {
                    $item = PrItem::findOne($winnerModel->pr_item_id);

                    $includedItems = PrItem::find()
                            ->select(['ppmp_pr_item.id as id', 'IF(ppmp_lot.title IS NOT NULL, concat("Lot No. ",ppmp_lot.lot_no," - ",ppmp_lot.title), 0) as lotTitle'])
                            ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
                            ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
                            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                            ->andWhere([
                                'ppmp_pr_item.pr_id' => $model->id,
                                'ppmp_item.id' => $item->ppmpItem->item_id,
                                'ppmp_pr_item.cost' => $item->cost,
                            ])
                            ->having(['lotTitle' => !is_null($item->lot) ? 'Lot No. '.$item->lot->lot_no.' - '.$item->lot->title : 0])
                            ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
                            ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                            ->all();
                        
                    $includedItems = ArrayHelper::map($includedItems, 'id', 'id');

                    if(!empty($includedItems))
                    {
                        foreach($includedItems as $includedItem)
                        {
                            $item = BidWinner::findOne(['bid_id' => $bid->id, 'pr_item_id' => $includedItem]) ? BidWinner::findOne(['bid_id' => $bid->id, 'pr_item_id' => $includedItem]) : new BidWinner();
                            $item->bid_id = $bid->id;
                            $item->supplier_id = $winnerModel->supplier_id == 0 ? null : $winnerModel->supplier_id;
                            $item->pr_item_id = $includedItem;
                            $item->justification = $winnerModel->justification;
                            $item->status = $winnerModel->supplier_id == 0 ? 'Failed' : 'Awarded';
                            $item->save(false);
                        }
                    }

                    
                }
            }
        }

        return $this->renderAjax('\steps\bid-items\select-bidder-form', [
            'model' => $model,
            'winnerModels' => $winnerModels,
            'bid' => $bid,
            'rfq' => $rfq,
            'i' => $i,
            'rfqItems' => $rfqItems,
            'lotItems' => $lotItems,
            'suppliers' => $suppliers,
            'supplierList' => $supplierList,
            'supplierIDs' => $supplierIDs,
            'rfqItemIDs' => $rfqItemIDs,
            'costs' => $costs,
        ]);
    }

    // Canvass/Bid Items -> Print AOQ
    public function actionPrintAoq($id)
    {
        $bid = Bid::findOne($id);
        $rfq = Rfq::findOne($bid->rfq_id);
        $model = $this->findModel($bid->pr_id);

        $bidWinners = $bid ? BidWinner::findAll(['bid_id' => $bid->id]) : [];
        $bidMembers = $bid ? BidMember::findAll(['bid_id' => $bid->id]) : [];

        $items = [];

        $agency = Settings::findOne(['title' => 'Agency Name']);
        $regionalOffice = Settings::findOne(['title' => 'Regional Office']);
        $address = Settings::findOne(['title' => 'Address']);
        $email = Settings::findOne(['title' => 'Email']);
        $telephoneNos = Settings::findOne(['title' => 'Telephone Nos.']);
        $regionalDirector = Settings::findOne(['title' => 'Regional Director']);

        $specifications = [];
        $forContractItems = [];

        $aprItemsWithValueIDs = PrItemCost::find()
                            ->select(['pr_item_id'])
                            ->andWhere(['pr_id' => $model->id])
                            ->andWhere(['supplier_id' => 1])
                            ->andWhere(['>', 'cost', 0])
                            ->asArray()
                            ->all();
        
        $aprItemsWithValueIDs = ArrayHelper::map($aprItemsWithValueIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->andWhere(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = array_intersect($aprItemIDs, $aprItemsWithValueIDs);

        $nonProcurableItemIDs = NonProcurableItem::find()
                    ->select(['pr_item_id'])
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $unmergedItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                's.id as ris_item_spec_id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
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
                    ) as prexc',
                'ppmp_activity.id as activityId',
                'ppmp_activity.title as activityTitle',
                'ppmp_sub_activity.id as subActivityId',
                'ppmp_sub_activity.title as subActivityTitle',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                s.activity_id = ppmp_ppmp_item.activity_id and 
                                                s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                s.item_id = ppmp_ppmp_item.item_id and 
                                                s.cost = ppmp_pr_item.cost and 
                                                s.type = ppmp_pr_item.type')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
            ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 's.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        $rfqItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'IF(ppmp_lot.title IS NOT NULL, concat("Lot No. ",ppmp_lot.lot_no," - ",ppmp_lot.title), 0) as lotTitle',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item_cost.pr_item_id',
                'ppmp_pr_item_cost.supplier_id',
                'ppmp_pr_item_cost.rfq_id',
                'ppmp_pr_item_cost.rfq_info_id',
                'ppmp_pr_item_cost.specification',
                'ppmp_pr_item.cost as abc',
                'ppmp_pr_item_cost.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_pr_item_cost', 'ppmp_pr_item_cost.pr_item_id = ppmp_pr_item.id')
            ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
            ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.rfq_id' => $rfq->id])
            ->andWhere(['<>','ppmp_pr_item_cost.supplier_id', 1])
            ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
            ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost', 'ppmp_pr_item_cost.supplier_id', 'lotTitle'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();
        
        $lotItems = [];
        //$rfqItems = $model->rfqItemsWithAprItems;

        if(!empty($unmergedItems))
        {
            foreach($unmergedItems as $item)
            {
                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }

                $forContractItem = ForContractItem::findOne(['item_id' => $item['item_id']]);
                if($forContractItem){ $forContractItems[$item['id']] = $forContractItem; }
            }
        }

        $risIDs = ArrayHelper::map($unmergedItems, 'ris_id', 'ris_id');
        $risNumbers = Ris::find()->select(['ris_no'])->where(['in', 'id', $risIDs])->asArray()->all();
        $risNumbers = ArrayHelper::map($risNumbers, 'ris_no', 'ris_no');
        $risNumbers = implode(", ", $risNumbers);

        $supplierIDs = PrItemCost::find()->select(['supplier_id'])->andWhere(['pr_id' => $model->id, 'rfq_id' => $rfq->id])->andWhere(['<>', 'supplier_id', 1])->groupBy(['supplier_id'])->asArray()->all();
        $supplierIDs = ArrayHelper::map($supplierIDs, 'supplier_id', 'supplier_id');

        $supplierList = Supplier::find()->where(['id' => $supplierIDs])->all();
        
        $prices = [];
        $colors = [];
        $winners = [];
        $justifications = [];

        if(!empty($rfqItems))
        {
            foreach($rfqItems as $item)
            {   
                $lotItems[$item['lotTitle']][] = $item;

                $winner = $bid ? BidWinner::findOne(['bid_id' => $bid->id, 'pr_item_id' => $item['id'], 'status' => 'Awarded']) : []; 
                $winners[$item['id']] = !empty($winner) ? Supplier::findOne(['id' => $winner->supplier_id]) : [];
                $justifications[$item['id']] = !empty($winner) ? $winner->justification : '';
                if($supplierList)
                {
                    foreach($supplierList as $sup)
                    {
                        $prices[$item['id']][$sup->id] = PrItemCost::findOne(['pr_id' => $model->id, 'pr_item_id' => $item['id'], 'rfq_id' => $rfq->id, 'supplier_id' => $sup->id]);
                        $colors[$item['id']][$sup->id] = !empty($winner) ? $winner->supplier_id == $sup->id ? 'yellow' : 'transparent' : 'transparent';
                    }
                }
            }
        }

        return $this->renderAjax('\reports\aoq', [
            'model' => $model,
            'bid' => $bid,
            'prices' => $prices,
            'colors' => $colors,
            'winners' => $winners,
            'justifications' => $justifications,
            'bidMembers' => $bidMembers,
            'rfq' => $rfq,
            'rfqItems' => $rfqItems,
            'lotItems' => $lotItems,
            'specifications' => $specifications,
            'agency' => $agency,
            'regionalOffice' => $regionalOffice,
            'address' => $address,
            'email' => $email,
            'telephoneNos' => $telephoneNos,
            'forContractItems' => $forContractItems,
            'risNumbers' => $risNumbers,
            'supplierList' => $supplierList,
            'regionalDirector' => $regionalDirector,
        ]);
    }

    // Create Purchase Order -> Select Type
    public function actionSelectType($id, $bid_id, $supplier_id, $j, $i, $k)
    {
        $model = $this->findModel($id);
        $bid = $bid_id != 0 ? Bid::findOne($bid_id) : null;
        $supplier = Supplier::findOne($supplier_id);
        $po = !is_null($bid) ? Po::findOne(['pr_id' => $model->id, 'bid_id' => $bid->id, 'supplier_id' => $supplier->id, 'type' => 'PO']) : Po::findOne(['pr_id' => $model->id, 'bid_id' => null, 'supplier_id' => $supplier->id, 'type' => 'PO']);
        $contract = !is_null($bid) ? Po::findOne(['pr_id' => $model->id, 'bid_id' => $bid->id, 'supplier_id' => $supplier->id, 'type' => 'Contract']) : Po::findOne(['pr_id' => $model->id, 'bid_id' => null, 'supplier_id' => $supplier->id, 'type' => 'Contract']);

        return $this->renderAjax('\steps\create-po-contract\index', [
            'model' => $model,
            'bid' => $bid,
            'supplier' => $supplier,
            'j' => $j,
            'i' => $i,
            'k' => $k,
            'po' => $po,
            'contract' => $contract,
        ]);
    }

    // Create Purchase Order -> Select Supplier
    public function actionCreatePurchaseOrder($id, $bid_id, $supplier_id, $j, $i, $k)
    {
        $model = $this->findModel($id);
        $bid = $bid_id != 'null' ? Bid::findOne($bid_id) : null;
        $supplier = Supplier::findOne($supplier_id);

        $agency = Settings::findOne(['title' => 'Agency Name']);
        $regionalOffice = Settings::findOne(['title' => 'Regional Office']);
        $address = Settings::findOne(['title' => 'Address']);
        $exactAddress = Settings::findOne(['title' => 'Exact Address']);
        $rd = Settings::findOne(['title' => 'Regional Director']);
        $accountant = Settings::findOne(['title' => 'Accountant']);
        $accountantPosition = Settings::findOne(['title' => 'Accountant Position']);
        
        if(!is_null($bid))
        {
            $poModel = Po::findOne(['pr_id' => $model->id, 'bid_id' => $bid->id, 'supplier_id' => $supplier->id, 'type' => 'PO']) ? 
            Po::findOne(['pr_id' => $model->id, 'bid_id' => $bid->id, 'supplier_id' => $supplier->id, 'type' => 'PO']) : new Po();
        }else
        {
            $poModel = Po::findOne(['pr_id' => $model->id, 'bid_id' => null, 'supplier_id' => $supplier->id, 'type' => 'PO']) ? Po::findOne(['pr_id' => $model->id, 'bid_id' => null, 'supplier_id' => $supplier->id, 'type' => 'PO']) : new Po();
        }

        $poModel->pr_id = $model->id;
        $poModel->bid_id = !is_null($bid) ? $bid->id : null;
        $poModel->supplier_id = $supplier->id;
        $poModel->type = 'PO';

        $paymentTerms = PaymentTerm::find()->all();
        $paymentTerms = ArrayHelper::map($paymentTerms, 'id', 'title');

        $specifications = [];

        $awardedItems = !is_null($bid) ? BidWinner::find()
                ->select(['pr_item_id'])
                ->where([
                    'bid_id' => $bid->id,
                    'supplier_id' => $supplier->id,
                    'status' => 'Awarded'
                ])
                ->asArray()
                ->all() : 
                AprItem::find()
                ->select(['pr_item_id'])
                ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                ->andWhere(['ppmp_apr.pr_id' => $model->id])
                ->asArray()
                ->all();

        $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

        $unmergedItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                's.id as ris_item_spec_id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
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
                    ) as prexc',
                'ppmp_activity.id as activityId',
                'ppmp_activity.title as activityTitle',
                'ppmp_sub_activity.id as subActivityId',
                'ppmp_sub_activity.title as subActivityTitle',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                s.activity_id = ppmp_ppmp_item.activity_id and 
                                                s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                s.item_id = ppmp_ppmp_item.item_id and 
                                                s.cost = ppmp_pr_item.cost and 
                                                s.type = ppmp_pr_item.type')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->groupBy(['ppmp_item.id', 's.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        $items = !is_null($bid) ? PrItemCost::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item_cost.cost as cost',
                'sum(ppmp_pr_item.quantity) as total',
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['ppmp_pr_item_cost.rfq_id' => $bid->rfq->id])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all() : PrItemCost::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item_cost.cost as cost',
                'sum(ppmp_pr_item.quantity) as total',
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['is', 'ppmp_pr_item_cost.rfq_id', null])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        if(!empty($unmergedItems))
        {
            foreach($unmergedItems as $item)
            {
                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }
            }
        }

        $initNo = 10;
        $no = $model->getPos()->where(['type' => 'PO'])->all() ? $model->getPos()->where(['type' => 'PO'])->count() + $initNo : $initNo ;

        if($poModel->load(Yii::$app->request->post()))
        {
            $poModel->po_no = $poModel->isNewRecord ? $model->pr_no.'-'.$no : $poModel->po_no;
            $poModel->delivery_place = $exactAddress->value;
            $poModel->save();
        }

        return $this->renderAjax('\steps\create-po-contract\purchase_order', [
            'model' => $model,
            'bid' => $bid,
            'supplier' => $supplier,
            'poModel' => $poModel,
            'paymentTerms' => $paymentTerms,
            'agency' => $agency,
            'regionalOffice' => $regionalOffice,
            'address' => $address,
            'exactAddress' => $exactAddress,
            'no' => $no,
            'items' => $items,
            'specifications' => $specifications,
            'rd' => $rd,
            'j' => $j,
            'i' => $i,
            'k' => $k,
            'accountant' => $accountant,
            'accountantPosition' => $accountantPosition,
        ]);
    }

    // Create Contract -> Select Supplier
    public function actionCreateContract($id, $bid_id, $supplier_id, $j, $i, $k)
    {
        $model = $this->findModel($id);
        $bid = $bid_id != 'null' ? Bid::findOne($bid_id) : null;
        $supplier = Supplier::findOne($supplier_id);

        $agency = Settings::findOne(['title' => 'Agency Name']);
        $entity = Settings::findOne(['title' => 'Entity Name']);
        $regionalOffice = Settings::findOne(['title' => 'Regional Office']);
        $address = Settings::findOne(['title' => 'Address']);
        $exactAddress = Settings::findOne(['title' => 'Exact Address']);
        $rd = Settings::findOne(['title' => 'Regional Director']);
        $accountant = Settings::findOne(['title' => 'Accountant']);
        $accountantPosition = Settings::findOne(['title' => 'Accountant Position']);
        $regionalAccountant = Signatory::findOne(['designation' => 'Regional Accountant']);

        if(!is_null($bid))
        {
            $contractModel = Po::findOne(['pr_id' => $model->id, 'bid_id' => $bid->id, 'supplier_id' => $supplier->id, 'type' => 'Contract']) ? 
            Po::findOne(['pr_id' => $model->id, 'bid_id' => $bid->id, 'supplier_id' => $supplier->id, 'type' => 'Contract']) : new Po();
        }else
        {
            $contractModel = Po::findOne(['pr_id' => $model->id, 'bid_id' => null, 'supplier_id' => $supplier->id, 'type' => 'Contract']) ? Po::findOne(['pr_id' => $model->id, 'bid_id' => null, 'supplier_id' => $supplier->id, 'type' => 'Contract']) : new Po();
        }

        $contractModel->pr_id = $model->id;
        $contractModel->bid_id = !is_null($bid) ? $bid->id : null;
        $contractModel->supplier_id = $supplier->id;
        $contractModel->type = 'Contract';

        $awardedItems = !is_null($bid) ? BidWinner::find()
                ->select(['pr_item_id'])
                ->where([
                    'bid_id' => $bid->id,
                    'supplier_id' => $supplier->id,
                    'status' => 'Awarded'
                ])
                ->asArray()
                ->all() : 
                AprItem::find()
                ->select(['pr_item_id'])
                ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                ->andWhere(['ppmp_apr.pr_id' => $model->id])
                ->asArray()
                ->all();

        $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

        $total = !is_null($bid) ? PrItemCost::find()
            ->select([
                'sum(ppmp_pr_item.quantity * ppmp_pr_item_cost.cost) as total'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['ppmp_pr_item_cost.rfq_id' => $bid->rfq->id])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->asArray()
            ->one() : PrItemCost::find()
            ->select([
                'sum(ppmp_pr_item.quantity * ppmp_pr_item_cost.cost) as total'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['is', 'ppmp_pr_item_cost.rfq_id', null])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->asArray()
            ->one();

        if($contractModel->load(Yii::$app->request->post()))
        {
            $contractModel->save(false);
        }

        return $this->renderAjax('\steps\create-po-contract\contract', [
            'model' => $model,
            'bid' => $bid,
            'supplier' => $supplier,
            'contractModel' => $contractModel,
            'agency' => $agency,
            'entity' => $entity,
            'regionalOffice' => $regionalOffice,
            'address' => $address,
            'exactAddress' => $exactAddress,
            'rd' => $rd,
            'j' => $j,
            'i' => $i,
            'k' => $k,
            'accountant' => $accountant,
            'accountantPosition' => $accountantPosition,
            'total' => $total,
            'regionalAccountant' => $regionalAccountant
        ]);
    }

    // Create Purchase Order -> Print PO
    public function actionPrintPo(
        $id
    )
    {
        $poModel = Po::findOne($id);
        $model = $poModel->pr;
        $bid = !is_null($poModel->bid_id) ? Bid::findOne($poModel->bid_id) : null;
        $supplier = Supplier::findOne($poModel->supplier_id);

        $agency = Settings::findOne(['title' => 'Agency Name']);
        $entity = Settings::findOne(['title' => 'Entity Name']);
        $regionalOffice = Settings::findOne(['title' => 'Regional Office']);
        $address = Settings::findOne(['title' => 'Address']);
        $exactAddress = Settings::findOne(['title' => 'Exact Address']);
        $rd = Settings::findOne(['title' => 'Regional Director']);
        $accountant = Settings::findOne(['title' => 'Accountant']);
        $accountantPosition = Settings::findOne(['title' => 'Accountant Position']);
        $regionalAccountant = Signatory::findOne(['designation' => 'Regional Accountant']);
        
        $specifications = [];

        $awardedItems = !is_null($bid) ? BidWinner::find()
            ->select(['pr_item_id'])
            ->where([
                'bid_id' => $bid->id,
                'supplier_id' => $supplier->id,
                'status' => 'Awarded'
            ])
            ->asArray()
            ->all() : 
            AprItem::find()
            ->select(['pr_item_id'])
            ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
            ->andWhere(['ppmp_apr.pr_id' => $model->id])
            ->asArray()
            ->all();
            
        $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

        $total = !is_null($bid) ? PrItemCost::find()
            ->select([
                'sum(ppmp_pr_item.quantity * ppmp_pr_item_cost.cost) as total'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['ppmp_pr_item_cost.rfq_id' => $bid->rfq->id])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->asArray()
            ->one() : PrItemCost::find()
            ->select([
                'sum(ppmp_pr_item.quantity * ppmp_pr_item_cost.cost) as total'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['is', 'ppmp_pr_item_cost.rfq_id', null])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->asArray()
            ->one();

        $unmergedItems = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                's.id as ris_item_spec_id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
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
                    ) as prexc',
                'ppmp_activity.id as activityId',
                'ppmp_activity.title as activityTitle',
                'ppmp_sub_activity.id as subActivityId',
                'ppmp_sub_activity.title as subActivityTitle',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                s.activity_id = ppmp_ppmp_item.activity_id and 
                                                s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                s.item_id = ppmp_ppmp_item.item_id and 
                                                s.cost = ppmp_pr_item.cost and 
                                                s.type = ppmp_pr_item.type')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->groupBy(['ppmp_item.id', 's.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        $items = !is_null($bid) ? PrItemCost::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item_cost.cost as cost',
                'sum(ppmp_pr_item.quantity) as total',
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['ppmp_pr_item_cost.rfq_id' => $bid->rfq->id])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all() : PrItemCost::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item_cost.cost as cost',
                'sum(ppmp_pr_item.quantity) as total',
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['is', 'ppmp_pr_item_cost.rfq_id', null])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();
        
        if(!empty($unmergedItems))
        {
            foreach($unmergedItems as $item)
            {
                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }
            }
        }

        return $poModel->type == 'PO' ? $this->renderAjax('\reports\po', [
            'model' => $model,
            'bid' => $bid,
            'supplier' => $supplier,
            'poModel' => $poModel,
            'agency' => $agency,
            'regionalOffice' => $regionalOffice,
            'address' => $address,
            'exactAddress' => $exactAddress,
            'items' => $items,
            'specifications' => $specifications,
            'rd' => $rd,
            'accountant' => $accountant,
            'accountantPosition' => $accountantPosition,
        ]) : $this->renderAjax('\reports\contract', [
            'model' => $model,
            'bid' => $bid,
            'supplier' => $supplier,
            'contractModel' => $poModel,
            'agency' => $agency,
            'entity' => $entity,
            'regionalOffice' => $regionalOffice,
            'address' => $address,
            'exactAddress' => $exactAddress,
            'rd' => $rd,
            'accountant' => $accountant,
            'accountantPosition' => $accountantPosition,
            'total' => $total,
            'regionalAccountant' => $regionalAccountant
        ]);
    }

    // Proceed Items -> Select Document
    public function actionProceedItem($id, $po_id, $i)
    {
        $model = $this->findModel($id);
        $po = Po::findOne(['id' => $po_id]);
        $ntp = Ntp:: findOne(['pr_id' => $model->id, 'po_id' => $po->id]);

        return $this->renderAjax('\steps\proceed-items\index', [
            'model' => $model,
            'po' => $po,
            'ntp' => $ntp,
            'i' => $i
        ]);
    }

    // NOA -> Create NOA
    public function actionCreateNoa($id, $bid_id, $supplier_id, $j, $i, $k)
    {
        $model = $this->findModel($id);
        $supplier = Supplier::findOne(['id' => $supplier_id]);
        $bid = Bid::findOne(['id' => $bid_id]);
        $noaModel = Noa:: findOne(['pr_id' => $model->id, 'bid_id' => $bid_id, 'supplier_id' => $supplier_id]) ? Noa:: findOne(['pr_id' => $model->id, 'bid_id' => $bid_id, 'supplier_id' => $supplier_id]) : new Noa();
        $noaModel->pr_id = $model->id;
        $noaModel->bid_id = $bid->id;
        $noaModel->supplier_id = $supplier->id;

        $rd = Settings::findOne(['title' => 'Regional Director']);

        $awardedItems = BidWinner::find()
                ->select(['pr_item_id'])
                ->where([
                    'bid_id' => $bid->id,
                    'supplier_id' => $supplier->id,
                    'status' => 'Awarded'
                ])
                ->asArray()
                ->all();

        $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

        $items = PrItemCost::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item_cost.cost as cost',
                'sum(ppmp_pr_item.quantity) as total',
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['ppmp_pr_item_cost.rfq_id' => $bid->rfq->id])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        if($noaModel->load(Yii::$app->request->post()))
        {
            $noaModel->created_by = Yii::$app->user->id;
            $noaModel->save();
        }

        return $this->renderAjax('\steps\award-items\noa_form', [
            'model' => $model,
            'bid' => $bid,
            'supplier' => $supplier,
            'noaModel' => $noaModel,
            'rd' => $rd,
            'items' => $items,
            'j' => $j,
            'i' => $i,
            'k' => $k,
        ]);
    }

    // Proceed and Award Items -> Print NOA
    public function actionPrintNoa($id)
    {
        $noa = Noa:: findOne($id);
        $model = $this->findModel($noa->pr_id);
        $supplier = Supplier::findOne(['id' => $noa->supplier_id]);
        $bid = Bid::findOne(['id' => $noa->bid_id]);

        $rd = Settings::findOne(['title' => 'Regional Director']);

        $awardedItems = BidWinner::find()
                ->select(['pr_item_id'])
                ->where([
                    'bid_id' => $bid->id,
                    'supplier_id' => $supplier->id,
                    'status' => 'Awarded'
                ])
                ->asArray()
                ->all();
        $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

        $items = PrItemCost::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item_cost.cost as cost',
                'sum(ppmp_pr_item.quantity) as total',
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['ppmp_pr_item_cost.rfq_id' => $bid->rfq->id])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        return $this->renderAjax('\reports\noa', [
            'model' => $model,
            'bid' => $bid,
            'supplier' => $supplier,
            'noa' => $noa,
            'rd' => $rd,
            'items' => $items,
        ]);
    }

    // Proceed and Award Items -> Create NTP
    public function actionCreateNtp($id, $po_id, $j, $i, $k)
    {
        $model = $this->findModel($id);
        $po = Po::findOne(['id' => $po_id]);
        $bid = !is_null($po->bid_id) ? Bid::findOne($po->bid_id) : null;
        $supplier = Supplier::findOne(['id' => $po->supplier_id]);
        $ntpModel = Ntp:: findOne(['pr_id' => $model->id, 'po_id' => $po->id]) ? Ntp:: findOne(['pr_id' => $model->id, 'po_id' => $po->id]) : new Ntp();
        $ntpModel->pr_id = $model->id;
        $ntpModel->po_id = $po->id;

        $rd = Settings::findOne(['title' => 'Regional Director']);

        $awardedItems = !is_null($bid) ? BidWinner::find()
            ->select(['pr_item_id'])
            ->where([
                'bid_id' => $bid->id,
                'supplier_id' => $supplier->id,
                'status' => 'Awarded'
            ])
            ->asArray()
            ->all() : 
            AprItem::find()
            ->select(['pr_item_id'])
            ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
            ->andWhere(['ppmp_apr.pr_id' => $model->id])
            ->asArray()
            ->all();
            
        $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

        $total = !is_null($bid) ? PrItemCost::find()
            ->select([
                'sum(ppmp_pr_item.quantity * ppmp_pr_item_cost.cost) as total'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['ppmp_pr_item_cost.rfq_id' => $bid->rfq->id])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->asArray()
            ->one() : PrItemCost::find()
            ->select([
                'sum(ppmp_pr_item.quantity * ppmp_pr_item_cost.cost) as total'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['is', 'ppmp_pr_item_cost.rfq_id', null])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->asArray()
            ->one();

        if($ntpModel->load(Yii::$app->request->post()))
        {
            $ntpModel->created_by = Yii::$app->user->id;
            $ntpModel->save();
        }

        return $this->renderAjax('\steps\proceed-items\ntp_form', [
            'model' => $model,
            'po' => $po,
            'supplier' => $supplier,
            'ntpModel' => $ntpModel,
            'rd' => $rd,
            'j' => $j,
            'i' => $i,
            'k' => $k,
            'total' => $total,
        ]);
    }
    
    // Proceed and Award Items -> Print NTP
    public function actionPrintNtp($id)
    {
        $ntp = Ntp::findOne($id);
        $model = $this->findModel($ntp->pr_id);
        $po = Po::findOne(['id' => $ntp->po_id]);
        $bid = !is_null($po->bid_id) ? Bid::findOne($po->bid_id) : null;
        $supplier = Supplier::findOne(['id' => $po->supplier_id]);
        $rd = Settings::findOne(['title' => 'Regional Director']);

        $awardedItems = !is_null($bid) ? BidWinner::find()
            ->select(['pr_item_id'])
            ->where([
                'bid_id' => $bid->id,
                'supplier_id' => $supplier->id,
                'status' => 'Awarded'
            ])
            ->asArray()
            ->all() : 
            AprItem::find()
            ->select(['pr_item_id'])
            ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
            ->andWhere(['ppmp_apr.pr_id' => $model->id])
            ->asArray()
            ->all();
            
        $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

        $total = !is_null($bid) ? PrItemCost::find()
            ->select([
                'sum(ppmp_pr_item.quantity * ppmp_pr_item_cost.cost) as total'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['ppmp_pr_item_cost.rfq_id' => $bid->rfq->id])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->asArray()
            ->one() : PrItemCost::find()
            ->select([
                'sum(ppmp_pr_item.quantity * ppmp_pr_item_cost.cost) as total'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['is', 'ppmp_pr_item_cost.rfq_id', null])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->asArray()
            ->one();

        return $this->renderAjax('\reports\ntp', [
            'model' => $model,
            'po' => $po,
            'supplier' => $supplier,
            'ntp' => $ntp,
            'rd' => $rd,
            'total' => $total
        ]);
    }

    // Inspect Items -> Select IAR
    public function actionInspectItem($id, $po_id, $i)
    {
        $model = $this->findModel($id);
        $po = Po::findOne(['id' => $po_id]);
        $supplier = Supplier::findOne(['id' => $po->supplier_id]);
        $iars = Iar::findAll(['pr_id' => $model->id, 'po_id' => $po->id]);

        return $this->renderAjax('\steps\inspect-items\index', [
            'model' => $model,
            'po' => $po,
            'supplier' => $supplier,
            'iars' => $iars,
            'i' => $i,
        ]);
    }

    // Inspect Items -> Create IAR
    public function actionCreateIar($id, $po_id, $i)
    {
        $model = $this->findModel($id);
        $po = Po::findOne(['id' => $po_id]);
        $supplier = Supplier::findOne(['id' => $po->supplier_id]);
        $bid = Bid::findOne(['id' => $po->bid_id]);
        $lastIar = Iar::find()->orderBy(['id' => SORT_DESC])->one();
        $lastNumber = $lastIar ? str_pad(intval(substr($lastIar->iar_no, -4)) + 1, 4, '0', STR_PAD_LEFT) : '0001';
        $iarNo = substr(date("Y"), -2).'-'.$lastNumber;
        
        $iarModel = new Iar();

        $iarModel->pr_id = $model->id;
        $iarModel->po_id = $po->id;
        $iarModel->iar_no = $iarNo;

        $entity = Settings::findOne(['title' => 'Entity Complete Short Name']);

        $inspectors = Signatory::findAll(['designation' => 'Inspection Officer']);
        $inspectors = ArrayHelper::map($inspectors, 'emp_id', 'name');

        $supplyOfficers = Signatory::findAll(['designation' => 'Supply Officer']);
        $supplyOfficers = ArrayHelper::map($supplyOfficers, 'emp_id', 'name');

        $awardedItems = BidWinner::find()
                ->select(['pr_item_id'])
                ->where([
                    'bid_id' => $po->bid_id,
                    'supplier_id' => $supplier->id,
                    'status' => 'Awarded'
                ])
                ->asArray()
                ->all();
        $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

        $balances = IarItem::find()
                    ->select([
                        'pr_item_id',
                        'sum(balance) as total'
                    ])
                    ->groupBy(['pr_item_id'])
                    ->createCommand()
                    ->getRawSql();

        $items = PrItemCost::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item_cost.cost as cost',
                'sum(ppmp_pr_item.quantity) as total',
                'sum(COALESCE(balances.total, 0)) as delivered',
                'sum(ppmp_pr_item.quantity - COALESCE(balances.total, 0)) as balance'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->leftJoin(['balances' => '('.$balances.')'], 'balances.pr_item_id = ppmp_pr_item.id')
            ->andWhere([
                'ppmp_pr_item_cost.pr_id' => $model->id,
                'ppmp_pr_item_cost.supplier_id' => $supplier->id,
                'ppmp_pr_item_cost.rfq_id' => $bid->rfq_id,
            ])
            ->andWhere(['in', 'ppmp_pr_item_cost.pr_item_id', $awardedItems])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        $itemModels = [];

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $itemModel = new IarItem();
                $itemModel->pr_item_id = $item['id'];
                $itemModels[$item['id']] = $itemModel;
            }
        }

        if($iarModel->load(Yii::$app->request->post()) && MultipleModel::loadMultiple($itemModels, Yii::$app->request->post()))
        {
            if($iarModel->save())
            {
                if(!empty($itemModels))
                {
                    foreach($itemModels as $itemModel)
                    {
                        $remaining = $itemModel->balance;
                        $item = PrItem::findOne($itemModel->pr_item_id);

                        $includedItems = PrItemCost::find()
                        ->select(['ppmp_pr_item.id as id'])
                        ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                        ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                        ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                        ->andWhere([
                            'ppmp_pr_item.pr_id' => $model->id,
                            'ppmp_item.id' => $item->ppmpItem->item_id,
                        ])
                        ->all();
                    
                        $includedItems = ArrayHelper::map($includedItems, 'id', 'id');

                        if(!empty($includedItems))
                        {
                            foreach($includedItems as $includedItem)
                            {
                                $prItem = PrItem::findOne(['id' => $includedItem]);
                                $deducted = $remaining >= $prItem->quantity ? $prItem->quantity : $remaining;

                                $iarItem = new IarItem();
                                $iarItem->iar_id = $iarModel->id;
                                $iarItem->pr_item_id = $includedItem;
                                $iarItem->balance = $deducted;
                                $iarItem->delivery_time = $itemModel->delivery_time;
                                $iarItem->courtesy = $itemModel->courtesy;
                                $iarItem->save();

                                $remaining -= $deducted;
                            }
                        }
                    }
                }
            }
        }

        return $this->renderAjax('\steps\inspect-items\iar_form', [
            'model' => $model,
            'po' => $po,
            'supplier' => $supplier,
            'iarModel' => $iarModel,
            'items' => $items,
            'itemModels' => $itemModels,
            'i' => $i,
            'inspectors' => $inspectors,
            'supplyOfficers' => $supplyOfficers,
            'iarNo' => $iarNo
        ]);
    }

    // Inspect Items -> Edit IAR
    public function actionUpdateIar($id, $i)
    {
        $iarModel = Iar::findOne($id);
        $iarNo = $iarModel->iar_no;
        $model = $this->findModel($iarModel->pr_id);
        $po = Po::findOne($iarModel->po_id);
        $bid = Bid::findOne(['id' => $po->bid_id]);
        $supplier = Supplier::findOne($po->supplier_id);

        $entity = Settings::findOne(['title' => 'Entity Complete Short Name']);

        $inspectors = Signatory::findAll(['designation' => 'Inspection Officer']);
        $inspectors = ArrayHelper::map($inspectors, 'emp_id', 'name');

        $supplyOfficers = Signatory::findAll(['designation' => 'Supply Officer']);
        $supplyOfficers = ArrayHelper::map($supplyOfficers, 'emp_id', 'name');

        $awardedItems = BidWinner::find()
                ->select(['pr_item_id'])
                ->where([
                    'bid_id' => $po->bid_id,
                    'supplier_id' => $supplier->id,
                    'status' => 'Awarded'
                ])
                ->asArray()
                ->all();
        $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

        $totalBalances = IarItem::find()
                    ->select([
                        'pr_item_id',
                        'sum(balance) as total'
                    ])
                    ->leftJoin('ppmp_iar', 'ppmp_iar.id = ppmp_iar_item.iar_id')
                    ->leftJoin('ppmp_po', 'ppmp_po.id = ppmp_iar.po_id')
                    ->andWhere(['<=', 'iar_id', $iarModel->id])
                    ->andWhere(['ppmp_po.id' => $po->id])
                    ->groupBy(['pr_item_id'])
                    ->createCommand()
                    ->getRawSql();
            
        $balances = IarItem::find()
                    ->select([
                        'pr_item_id',
                        'sum(balance) as total'
                    ])
                    ->andWhere(['iar_id' => $iarModel->id])
                    ->groupBy(['pr_item_id'])
                    ->createCommand()
                    ->getRawSql();

        $items = PrItemCost::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item_cost.cost as cost',
                'sum(ppmp_pr_item.quantity) as total',
                'sum(COALESCE(balances.total, 0)) as delivered',
                'sum(ppmp_pr_item.quantity - COALESCE(totalBalances.total, 0)) as balance'
            ])
            ->leftJoin('ppmp_iar_item', 'ppmp_iar_item.pr_item_id = ppmp_pr_item_cost.pr_item_id and ppmp_iar_item.iar_id = '.$iarModel->id)
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->leftJoin(['balances' => '('.$balances.')'], 'balances.pr_item_id = ppmp_pr_item.id')
            ->leftJoin(['totalBalances' => '('.$totalBalances.')'], 'totalBalances.pr_item_id = ppmp_pr_item.id')
            ->andWhere([
                'ppmp_pr_item_cost.pr_id' => $model->id,
                'ppmp_pr_item_cost.supplier_id' => $supplier->id,
                'ppmp_pr_item_cost.rfq_id' => $bid->rfq_id,
                'ppmp_iar_item.iar_id' => $iarModel->id,
            ])
            ->andWhere(['in', 'ppmp_pr_item_cost.pr_item_id', $awardedItems])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        $itemModels = [];

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $itemModel = IarItem::findOne(['iar_id' => $iarModel->id, 'pr_item_id' => $item['id']]);
                $itemModel->balance = $item['delivered'];
                $itemModels[$item['id']] = $itemModel; 
            }
        }

        if($iarModel->load(Yii::$app->request->post()) && MultipleModel::loadMultiple($itemModels, Yii::$app->request->post()))
        {
            if($iarModel->save())
            {
                if(!empty($itemModels))
                {
                    foreach($itemModels as $itemModel)
                    {
                        $remaining = $itemModel->balance;
                        $item = PrItem::findOne($itemModel->pr_item_id);

                        $includedItems = PrItemCost::find()
                        ->select(['ppmp_pr_item.id as id'])
                        ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                        ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                        ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                        ->andWhere([
                            'ppmp_pr_item.pr_id' => $model->id,
                            'ppmp_item.id' => $item->ppmpItem->item_id,
                        ])
                        ->all();
                    
                        $includedItems = ArrayHelper::map($includedItems, 'id', 'id');

                        if(!empty($includedItems))
                        {
                            foreach($includedItems as $includedItem)
                            {
                                $prItem = PrItem::findOne(['id' => $includedItem]);
                                $deducted = $remaining >= $prItem->quantity ? $prItem->quantity : $remaining;

                                $iarItem = IarItem::findOne(['iar_id' => $iarModel->id, 'pr_item_id' => $includedItem]) ? IarItem::findOne(['iar_id' => $iarModel->id, 'pr_item_id' => $includedItem]) : new IarItem();
                                $iarItem->iar_id = $iarModel->id;
                                $iarItem->pr_item_id = $includedItem;
                                $iarItem->balance = $deducted;
                                $iarItem->delivery_time = $itemModel->delivery_time;
                                $iarItem->courtesy = $itemModel->courtesy;
                                $iarItem->save();

                                $remaining -= $deducted;
                            }
                        }
                    }
                }
            }
        }

        return $this->renderAjax('\steps\inspect-items\iar_form', [
            'model' => $model,
            'po' => $po,
            'supplier' => $supplier,
            'iarModel' => $iarModel,
            'items' => $items,
            'itemModels' => $itemModels,
            'i' => $i,
            'inspectors' => $inspectors,
            'supplyOfficers' => $supplyOfficers,
            'iarNo' => $iarNo
        ]);
    }

    // Inspect Items -> View Iar
    public function actionViewIar($id)
    {
        $iar = Iar::findOne($id);
        $po = Po::findOne(['id' => $iar->po_id]);
        $bid = Bid::findOne(['id' => $po->bid_id]);
        $model = $this->findModel($iar->pr_id);
        $supplier = Supplier::findOne(['id' => $po->supplier_id]);
        $entity = Settings::findOne(['title' => 'Entity Complete Short Name']);

        $awardedItems = BidWinner::find()
            ->select(['pr_item_id'])
            ->where([
                'bid_id' => $po->bid_id,
                'supplier_id' => $supplier->id,
                'status' => 'Awarded'
            ])
            ->asArray()
            ->all();
        $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

        $totalBalances = IarItem::find()
                ->select([
                    'pr_item_id',
                    'sum(balance) as total'
                ])
                ->leftJoin('ppmp_iar', 'ppmp_iar.id = ppmp_iar_item.iar_id')
                ->leftJoin('ppmp_po', 'ppmp_po.id = ppmp_iar.po_id')
                ->andWhere(['<=', 'iar_id', $iar->id])
                ->andWhere(['ppmp_po.id' => $po->id])
                ->groupBy(['pr_item_id'])
                ->createCommand()
                ->getRawSql();
        
        $balances = IarItem::find()
                    ->select([
                        'pr_item_id',
                        'sum(balance) as total'
                    ])
                    ->andWhere(['iar_id' => $iar->id])
                    ->groupBy(['pr_item_id'])
                    ->createCommand()
                    ->getRawSql();

        $items = PrItemCost::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item_cost.cost as cost',
                'sum(ppmp_pr_item.quantity) as total',
                'sum(COALESCE(balances.total, 0)) as delivered',
                'sum(ppmp_pr_item.quantity - COALESCE(totalBalances.total, 0)) as balance',
                'ppmp_iar_item.delivery_time',
                'ppmp_iar_item.courtesy',
            ])
            ->leftJoin('ppmp_iar_item', 'ppmp_iar_item.pr_item_id = ppmp_pr_item_cost.pr_item_id and ppmp_iar_item.iar_id = '.$iar->id)
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->leftJoin(['balances' => '('.$balances.')'], 'balances.pr_item_id = ppmp_pr_item.id')
            ->leftJoin(['totalBalances' => '('.$totalBalances.')'], 'totalBalances.pr_item_id = ppmp_pr_item.id')
            ->andWhere([
                'ppmp_pr_item_cost.pr_id' => $model->id,
                'ppmp_pr_item_cost.supplier_id' => $supplier->id,
                'ppmp_pr_item_cost.rfq_id' => $bid->rfq_id,
                'ppmp_iar_item.iar_id' => $iar->id,
            ])
            ->andWhere(['in', 'ppmp_pr_item_cost.pr_item_id', $awardedItems])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();
    
        $rccs = PrItemCost::find()
            ->select(['concat(
                ppmp_cost_structure.code,"",
                ppmp_organizational_outcome.code,"",
                ppmp_program.code,"",
                ppmp_sub_program.code,"",
                ppmp_identifier.code,"",
                ppmp_pap.code,"000-",
                ppmp_activity.code
            ) as prexc',])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
            ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
            ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
            ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
            ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
            ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
            ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
            ->andWhere([
                'ppmp_pr_item.pr_id' => $model->id,
            ])
            ->groupBy(['ppmp_activity.id'])
            ->asArray()
            ->all();
        
        $rccs = ArrayHelper::map($rccs, 'prexc', 'prexc');
        
        return $this->renderAjax('\steps\inspect-items\iar', [
            'model' => $model,
            'po' => $po,
            'supplier' => $supplier,
            'iar' => $iar,
            'items' => $items,
            'entity' => $entity,
            'rccs' => $rccs,
        ]);
    }

    // Inspect Items -> Print Iar
    public function actionPrintIar($id)
    {
        $iar = Iar::findOne($id);
        $po = Po::findOne(['id' => $iar->po_id]);
        $bid = Bid::findOne(['id' => $po->bid_id]);
        $model = $this->findModel($iar->pr_id);
        $supplier = Supplier::findOne(['id' => $po->supplier_id]);
        $entity = Settings::findOne(['title' => 'Entity Complete Short Name']);

        $awardedItems = BidWinner::find()
            ->select(['pr_item_id'])
            ->where([
                'bid_id' => $po->bid_id,
                'supplier_id' => $supplier->id,
                'status' => 'Awarded'
            ])
            ->asArray()
            ->all();
        $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

        $totalBalances = IarItem::find()
                ->select([
                    'pr_item_id',
                    'sum(balance) as total'
                ])
                ->leftJoin('ppmp_iar', 'ppmp_iar.id = ppmp_iar_item.iar_id')
                ->leftJoin('ppmp_po', 'ppmp_po.id = ppmp_iar.po_id')
                ->andWhere(['<=', 'iar_id', $iar->id])
                ->andWhere(['ppmp_po.id' => $po->id])
                ->groupBy(['pr_item_id'])
                ->createCommand()
                ->getRawSql();
        
        $balances = IarItem::find()
                    ->select([
                        'pr_item_id',
                        'sum(balance) as total'
                    ])
                    ->andWhere(['iar_id' => $iar->id])
                    ->groupBy(['pr_item_id'])
                    ->createCommand()
                    ->getRawSql();
                    
        $rccs = PrItemCost::find()
                ->select(['IF(ppmp_pap.short_code IS NULL,
                            concat(
                                ppmp_cost_structure.code,"",
                                ppmp_organizational_outcome.code,"",
                                ppmp_program.code,"",
                                ppmp_sub_program.code,"",
                                ppmp_identifier.code,"",
                                ppmp_pap.code,"000-",
                                ppmp_activity.code
                            )
                            ,
                            concat(
                                ppmp_pap.short_code,"-",
                                ppmp_activity.code
                            )
                        ) as prexc'
                ])
                ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $model->id,
                ])
                ->groupBy(['ppmp_activity.id'])
                ->asArray()
                ->all();
        
        $rccs = ArrayHelper::map($rccs, 'prexc', 'prexc');

        $items = PrItemCost::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item_cost.cost as cost',
                'sum(ppmp_pr_item.quantity) as total',
                'sum(COALESCE(balances.total, 0)) as delivered',
                'sum(ppmp_pr_item.quantity - COALESCE(totalBalances.total, 0)) as balance',
                'ppmp_iar_item.delivery_time',
                'ppmp_iar_item.courtesy',
            ])
            ->leftJoin('ppmp_iar_item', 'ppmp_iar_item.pr_item_id = ppmp_pr_item_cost.pr_item_id and ppmp_iar_item.iar_id = '.$iar->id)
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->leftJoin(['balances' => '('.$balances.')'], 'balances.pr_item_id = ppmp_pr_item.id')
            ->leftJoin(['totalBalances' => '('.$totalBalances.')'], 'totalBalances.pr_item_id = ppmp_pr_item.id')
            ->andWhere([
                'ppmp_pr_item_cost.pr_id' => $model->id,
                'ppmp_pr_item_cost.supplier_id' => $supplier->id,
                'ppmp_pr_item_cost.rfq_id' => $bid->rfq_id,
                'ppmp_iar_item.iar_id' => $iar->id,
            ])
            ->andWhere(['in', 'ppmp_pr_item_cost.pr_item_id', $awardedItems])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();
        
        return $this->renderAjax('\reports\iar', [
            'model' => $model,
            'po' => $po,
            'supplier' => $supplier,
            'iar' => $iar,
            'items' => $items,
            'entity' => $entity,
            'rccs' => $rccs,
        ]);
    }

    // Inspect Items -> Delete Iar
    public function actionDeleteIar($id, $iar_id)
    {
        $iar = Iar::findOne($iar_id);
        $model = $this->findModel($id);

        $iar->delete();
    }

    // Obligate Items -> with PO
    public function actionObligateItem($id, $apr_id, $po_id, $j, $i, $k, $type)
    {
        $model = $this->findModel($id);
        $apr = null;
        $po = null;
        $ors = null;

        if($type == 'APR')
        {
            $apr = $model->apr;
            $ors = Ors::find()
                ->andWhere(['pr_id' => $model->id, 'apr_id' => $apr->id])
                ->andWhere(['is', 'po_id', null])
                ->all();
        }
        else if($type == 'PO')
        {
            $po = Po::findOne($po_id);
            $ors = Ors::find()
                ->andWhere(['pr_id' => $model->id, 'po_id' => $po_id])
                ->andWhere(['is', 'apr_id', null])
                ->all();
        }
        else
        {
            $ors = Ors::find()
                ->andWhere(['pr_id' => $model->id])
                ->andWhere(['is', 'apr_id', null])
                ->andWhere(['is', 'po_id', null])
                ->all();
        }

        return $this->renderAjax('\steps\obligate-items\index', [
            'model' => $model,
            'apr' => $apr,
            'po' => $po,
            'ors' => $ors,
            'j' => $j,
            'i' => $i,
            'k' => $k,
            'type' => $type
        ]);
    }

    // Obligate Items -> Create ORS
    public function actionCreateOrs($id, $apr_id, $po_id, $j, $i, $k, $type)
    {
        $model = $this->findModel($id);
        $apr = $apr_id != 'null' ? $model->apr : null;
        $po = $po_id != 'null' ? Po::findOne($po_id) : null;
        $bid = $po_id != 'null' ? Bid::findOne($po->bid_id) : null;
        $supplier = $po_id != 'null' ? Supplier::findOne($po->supplier_id) : null;

        $budgetOfficer = Settings::findOne(['title' => 'Budget Officer']);
        
        $orsModel = new Ors();
        $orsModel->pr_id = $model->id;
        $orsModel->apr_id = $apr_id != 'null' ? $apr->id : null;
        $orsModel->po_id = $po_id != 'null' ? $po->id : null;
        $orsModel->ors_no = $orsModel->isNewRecord ? date("Y-m-") : $orsModel->ors_no;  
        $orsModel->reviewed_by = $budgetOfficer->value;
        $orsModel->type = $type;

        $orsModel->scenario = $type;

        $awardedItems = [];

        $aprItemsWithValueIDs = PrItemCost::find()
                            ->select(['pr_item_id'])
                            ->andWhere(['pr_id' => $model->id])
                            ->andWhere(['supplier_id' => 1])
                            ->andWhere(['>', 'cost', 0])
                            ->asArray()
                            ->all();
        
        $aprItemsWithValueIDs = ArrayHelper::map($aprItemsWithValueIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->andWhere(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = array_intersect($aprItemIDs, $aprItemsWithValueIDs);

        $nonProcurableItemIDs = NonProcurableItem::find()
                ->select(['pr_item_id'])
                ->where(['pr_id' => $model->id])
                ->asArray()
                ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        if($type == 'APR'){

            $awardedItems = PrItemCost::find()
                ->select(['pr_item_id'])
                ->andWhere(['pr_id' => $model->id])
                ->andWhere(['supplier_id' => 1])
                ->andWhere(['is', 'rfq_id', null])
                ->asArray()
                ->all();
            
            $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

        }else if($type == 'PO'){

            $awardedItems = BidWinner::find()
                ->select(['pr_item_id'])
                ->where([
                    'bid_id' => $bid->id,
                    'supplier_id' => $supplier->id,
                    'status' => 'Awarded'
                ])
                ->asArray()
                ->all();
            
            $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

        }else if($type == 'NP'){

            $awardedItems = $nonProcurableItemIDs;
    
        }

        $orsItemIDs = OrsItem::find()
                    ->select(['pr_item_id'])
                    ->where(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $orsItemIDs = ArrayHelper::map($orsItemIDs, 'pr_item_id', 'pr_item_id');

        $existingOrsItemIDs = [];

        $itemModels = [];
        $items = [];

        if($type == 'APR'){
            
            $items = PrItemCost::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item_cost.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item_cost.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => 1])
            ->andWhere(['is', 'ppmp_pr_item_cost.rfq_id', null])
            ->andWhere(['in', 'ppmp_pr_item.id', $aprItemIDs])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->andWhere(['not in', 'ppmp_pr_item.id', $orsItemIDs])
            ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        }else if($type == 'PO'){

            $items = PrItemCost::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item_cost.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
            ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item_cost.pr_id' => $model->id])
            ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
            ->andWhere(['ppmp_pr_item_cost.rfq_id' => $bid->rfq->id])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->andWhere(['not in', 'ppmp_pr_item.id', $orsItemIDs])
            ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
            ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        }else if($type == 'NP'){

            $items = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->andWhere(['not in', 'ppmp_pr_item.id', $orsItemIDs])
            ->andWhere(['in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

        }
        
        if(!empty($items))
        {
            foreach($items as $item)
            {
                $orsItem = new OrsItem();
                $orsItem->id = $item['id'];
                $itemModels[$item['id']] = $orsItem;
            }
        }

        if($orsModel->load(Yii::$app->request->post()) && MultipleModel::loadMultiple($itemModels, Yii::$app->request->post()))
        {
            if(!empty($itemModels))
            {
                $orsModel->created_by = Yii::$app->user->identity->userinfo->EMP_N;
                $orsModel->date_created = date("Y-m-d");
                if($orsModel->save())
                {
                    foreach($itemModels as $item)
                    {
                        if($item['pr_item_id'] != 0)
                        {
                            $item = PrItem::findOne($item['pr_item_id']);

                            if($type == 'APR'){

                                $includedItems = PrItemCost::find()
                                ->select(['ppmp_pr_item.id as id'])
                                ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                                ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
                                ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
                                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                                ->andWhere([
                                    'ppmp_pr_item.pr_id' => $model->id,
                                    'ppmp_item.id' => $item->ppmpItem->item_id,
                                    'ppmp_pr_item.cost' => $item->cost,
                                ])
                                ->andWhere(['ppmp_pr_item_cost.supplier_id' => 1])
                                ->andWhere(['is', 'ppmp_pr_item_cost.rfq_id', null])
                                ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
                                ->andWhere(['not in', 'ppmp_pr_item.id', $orsItemIDs])
                                ->all();
     
                            }else if($type == 'PO'){
                    
                                $includedItems = PrItemCost::find()
                                ->select(['ppmp_pr_item.id as id'])
                                ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                                ->andWhere([
                                    'ppmp_pr_item.pr_id' => $model->id,
                                    'ppmp_item.id' => $item->ppmpItem->item_id,
                                    'ppmp_pr_item.cost' => $item->cost,
                                ])
                                ->andWhere([
                                    'ppmp_pr_item_cost.supplier_id' => $supplier->id,
                                    'ppmp_pr_item_cost.rfq_id' => $bid->rfq->id,
                                ])
                                ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
                                ->andWhere(['not in', 'ppmp_pr_item.id', $orsItemIDs])
                                ->all();
                    
                            }else if($type == 'NP'){
                                
                                $includedItems = PrItem::find()
                                ->select(['ppmp_pr_item.id as id'])
                                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                                ->andWhere([
                                    'ppmp_pr_item.pr_id' => $item->pr_id,
                                    'ppmp_item.id' => $item->ppmpItem->item_id,
                                    'ppmp_pr_item.cost' => $item->cost,
                                ])
                                ->select(['ppmp_pr_item.id as id'])
                                ->andWhere(['in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                                ->andWhere(['not in', 'ppmp_pr_item.id', $orsItemIDs])
                                ->all();

                            }
                            
                            $includedItems = ArrayHelper::map($includedItems, 'id', 'id');

                            if(!empty($includedItems))
                            {
                                foreach($includedItems as $includedItem)
                                {
                                    $orsItem = OrsItem::findOne(['pr_id' => $model->id, 'pr_item_id' => $includedItem]) ? OrsItem::findOne(['pr_id' => $model->id, 'pr_item_id' => $includedItem]) : new OrsItem();
                                    if($orsItem){
                                        $orsItem->pr_id = $model->id;
                                        $orsItem->pr_item_id = $includedItem;
                                        $orsItem->ors_id = $orsModel->id;
                                        $orsItem->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->renderAjax('\steps\obligate-items\ors_form', [
            'model' => $model,
            'orsModel' => $orsModel,
            'itemModels' => $itemModels,
            'existingOrsItemIDs' => $existingOrsItemIDs,
            'items' => $items,
            'apr' => $apr,
            'po' => $po,
            'j' => $j,
            'i' => $i,
            'k' => $k,
            'type' => $type
        ]);
    }

    // Obligate Items -> View ORS
    public function actionViewOrs($id)
    {
        $ors = Ors::findOne($id);
        $model = $this->findModel($ors->pr_id);
        $po = !is_null($ors->po_id) ? Po::findOne($ors->po_id) : null;
        $bid = !is_null($po) ? Bid::findOne($po->bid_id) : null;
        $supplier = null;

        $aprItemsWithValueIDs = PrItemCost::find()
                            ->select(['pr_item_id'])
                            ->andWhere(['pr_id' => $model->id])
                            ->andWhere(['supplier_id' => 1])
                            ->andWhere(['>', 'cost', 0])
                            ->asArray()
                            ->all();
        
        $aprItemsWithValueIDs = ArrayHelper::map($aprItemsWithValueIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->andWhere(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = array_intersect($aprItemIDs, $aprItemsWithValueIDs);

        $nonProcurableItemIDs = NonProcurableItem::find()
                ->select(['pr_item_id'])
                ->where(['pr_id' => $model->id])
                ->asArray()
                ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $orsItemIDs = OrsItem::find()
                    ->select(['pr_item_id'])
                    ->andWhere(['pr_id' => $model->id])
                    ->andWhere(['ors_id' => $ors->id])
                    ->asArray()
                    ->all();

        $orsItemIDs = ArrayHelper::map($orsItemIDs, 'pr_item_id', 'pr_item_id');

        if($ors->type == 'APR'){
            $supplier = Supplier::findOne(1);

            $awardedItems = PrItemCost::find()
                ->select(['pr_item_id'])
                ->andWhere(['pr_id' => $model->id])
                ->andWhere(['supplier_id' => 1])
                ->andWhere(['is', 'rfq_id', null])
                ->asArray()
                ->all();
            
            $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

            $items = PrItemCost::find()
                ->select([
                    'ppmp_pr_item.id as id',
                    'ppmp_item.id as item_id',
                    'ppmp_item.title as item',
                    'ppmp_item.unit_of_measure as unit',
                    'ppmp_pr_item.cost as cost',
                    'ppmp_pr_item_cost.cost as offer',
                    'sum(ppmp_pr_item.quantity) as total'
                ])
                ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->andWhere(['ppmp_pr_item_cost.pr_id' => $model->id])
                ->andWhere(['ppmp_pr_item_cost.supplier_id' => 1])
                ->andWhere(['is', 'ppmp_pr_item_cost.rfq_id', null])
                ->andWhere(['in', 'ppmp_pr_item.id', $aprItemIDs])
                ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
                ->andWhere(['in', 'ppmp_pr_item.id', $orsItemIDs])
                ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
                ->orderBy(['item' => SORT_ASC])
                ->asArray()
                ->all();

            $prexcs = PrItemCost::find()
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
                    ) as pap',
                    'ppmp_obj.code as objCode',
                    'sum(ppmp_pr_item.quantity * ppmp_pr_item_cost.cost) as total'
                ])
                ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_obj', 'ppmp_obj.id = ppmp_ppmp_item.obj_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->andWhere(['ppmp_pr_item_cost.pr_id' => $model->id])
                ->andWhere(['ppmp_pr_item_cost.supplier_id' => 1])
                ->andWhere(['is', 'ppmp_pr_item_cost.rfq_id', null])
                ->andWhere(['in', 'ppmp_pr_item.id', $aprItemIDs])
                ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
                ->andWhere(['in', 'ppmp_pr_item.id', $orsItemIDs])
                ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                ->groupBy(['ppmp_activity.id', 'ppmp_activity.id', 'ppmp_obj.id'])
                ->orderBy(['pap' => SORT_ASC, 'objCode' => SORT_ASC])
                ->asArray()
                ->all();

        }else if($ors->type == 'PO'){
            $supplier = Supplier::findOne(['id' => $po->supplier_id]);

            $awardedItems = BidWinner::find()
                ->select(['pr_item_id'])
                ->where([
                    'bid_id' => $bid->id,
                    'supplier_id' => $supplier->id,
                    'status' => 'Awarded'
                ])
                ->asArray()
                ->all();
            
            $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

            $items = PrItemCost::find()
                ->select([
                    'ppmp_pr_item.id as id',
                    'ppmp_item.id as item_id',
                    'ppmp_item.title as item',
                    'ppmp_item.unit_of_measure as unit',
                    'ppmp_pr_item.cost as cost',
                    'ppmp_pr_item_cost.cost as offer',
                    'sum(ppmp_pr_item.quantity) as total'
                ])
                ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
                ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->andWhere(['ppmp_pr_item_cost.pr_id' => $model->id])
                ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
                ->andWhere(['ppmp_pr_item_cost.rfq_id' => $bid->rfq->id])
                ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
                ->andWhere(['in', 'ppmp_pr_item.id', $orsItemIDs])
                ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
                ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
                ->orderBy(['item' => SORT_ASC])
                ->asArray()
                ->all();

            $prexcs = PrItemCost::find()
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
                    ) as pap',
                    'ppmp_obj.code as objCode',
                    'sum(ppmp_pr_item.quantity * ppmp_pr_item_cost.cost) as total'
                ])
                ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_obj', 'ppmp_obj.id = ppmp_ppmp_item.obj_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->andWhere(['ppmp_pr_item_cost.pr_id' => $model->id])
                ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
                ->andWhere(['ppmp_pr_item_cost.rfq_id' => $bid->rfq->id])
                ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
                ->andWhere(['in', 'ppmp_pr_item.id', $orsItemIDs])
                ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
                ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                ->groupBy(['ppmp_activity.id', 'ppmp_activity.id', 'ppmp_obj.id'])
                ->orderBy(['pap' => SORT_ASC, 'objCode' => SORT_ASC])
                ->asArray()
                ->all();

        }else if($ors->type == 'NP'){
            $supplier = null;

            $awardedItems = $nonProcurableItemIDs;

            $items = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'ppmp_pr_item.cost as offer',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->andWhere(['in', 'ppmp_pr_item.id', $orsItemIDs])
            ->andWhere(['in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

            $prexcs = PrItem::find()
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
                    ) as pap',
                    'ppmp_obj.code as objCode',
                    'sum(ppmp_pr_item.quantity * ppmp_pr_item.cost) as total'
                ])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_obj', 'ppmp_obj.id = ppmp_ppmp_item.obj_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
                ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
                ->andWhere(['in', 'ppmp_pr_item.id', $orsItemIDs])
                ->andWhere(['in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                ->groupBy(['ppmp_activity.id', 'ppmp_activity.id', 'ppmp_obj.id'])
                ->orderBy(['pap' => SORT_ASC, 'objCode' => SORT_ASC])
                ->asArray()
                ->all();
        }
        
        $prexcData = [];

        if(!empty($prexcs))
        {
            foreach($prexcs as $prexc)
            {
                $prexcData[$prexc['pap']][$prexc['objCode']] = $prexc['total'];
            }
        }

        $rowspan = count($prexcs);

        return $this->renderAjax('\steps\obligate-items\ors', [
            'model' => $model,
            'po' => $po,
            'supplier' => $supplier,
            'ors' => $ors,
            'items' => $items,
            'prexcData' => $prexcData,
            'rowspan' => $rowspan,
        ]);
    }

    // Obligate Items -> Print ORS
    public function actionPrintOrs($id)
    {
        $ors = Ors::findOne($id);
        $model = $this->findModel($ors->pr_id);
        $po = !is_null($ors->po_id) ? Po::findOne($ors->po_id) : null;
        $bid = !is_null($po) ? Bid::findOne($po->bid_id) : null;
        $supplier = null;

        $aprItemsWithValueIDs = PrItemCost::find()
                            ->select(['pr_item_id'])
                            ->andWhere(['pr_id' => $model->id])
                            ->andWhere(['supplier_id' => 1])
                            ->andWhere(['>', 'cost', 0])
                            ->asArray()
                            ->all();
        
        $aprItemsWithValueIDs = ArrayHelper::map($aprItemsWithValueIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = AprItem::find()
                    ->select(['pr_item_id'])
                    ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                    ->andWhere(['pr_id' => $model->id])
                    ->asArray()
                    ->all();

        $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

        $aprItemIDs = array_intersect($aprItemIDs, $aprItemsWithValueIDs);

        $nonProcurableItemIDs = NonProcurableItem::find()
                ->select(['pr_item_id'])
                ->where(['pr_id' => $model->id])
                ->asArray()
                ->all();

        $nonProcurableItemIDs = ArrayHelper::map($nonProcurableItemIDs, 'pr_item_id', 'pr_item_id');

        $orsItemIDs = OrsItem::find()
                    ->select(['pr_item_id'])
                    ->andWhere(['pr_id' => $model->id])
                    ->andWhere(['ors_id' => $ors->id])
                    ->asArray()
                    ->all();

        $orsItemIDs = ArrayHelper::map($orsItemIDs, 'pr_item_id', 'pr_item_id');

        if($ors->type == 'APR'){
            $supplier = Supplier::findOne(1);

            $awardedItems = PrItemCost::find()
                ->select(['pr_item_id'])
                ->andWhere(['pr_id' => $model->id])
                ->andWhere(['supplier_id' => 1])
                ->andWhere(['is', 'rfq_id', null])
                ->asArray()
                ->all();
            
            $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

            $items = PrItemCost::find()
                ->select([
                    'ppmp_pr_item.id as id',
                    'ppmp_item.id as item_id',
                    'ppmp_item.title as item',
                    'ppmp_item.unit_of_measure as unit',
                    'ppmp_pr_item.cost as cost',
                    'ppmp_pr_item_cost.cost as offer',
                    'sum(ppmp_pr_item.quantity) as total'
                ])
                ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->andWhere(['ppmp_pr_item_cost.pr_id' => $model->id])
                ->andWhere(['ppmp_pr_item_cost.supplier_id' => 1])
                ->andWhere(['is', 'ppmp_pr_item_cost.rfq_id', null])
                ->andWhere(['in', 'ppmp_pr_item.id', $aprItemIDs])
                ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
                ->andWhere(['in', 'ppmp_pr_item.id', $orsItemIDs])
                ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
                ->orderBy(['item' => SORT_ASC])
                ->asArray()
                ->all();

            $prexcs = PrItemCost::find()
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
                    ) as pap',
                    'ppmp_obj.code as objCode',
                    'sum(ppmp_pr_item.quantity * ppmp_pr_item_cost.cost) as total'
                ])
                ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_obj', 'ppmp_obj.id = ppmp_ppmp_item.obj_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->andWhere(['ppmp_pr_item_cost.pr_id' => $model->id])
                ->andWhere(['ppmp_pr_item_cost.supplier_id' => 1])
                ->andWhere(['is', 'ppmp_pr_item_cost.rfq_id', null])
                ->andWhere(['in', 'ppmp_pr_item.id', $aprItemIDs])
                ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
                ->andWhere(['in', 'ppmp_pr_item.id', $orsItemIDs])
                ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                ->groupBy(['ppmp_activity.id', 'ppmp_activity.id', 'ppmp_obj.id'])
                ->orderBy(['pap' => SORT_ASC, 'objCode' => SORT_ASC])
                ->asArray()
                ->all();

        }else if($ors->type == 'PO'){
            $supplier = Supplier::findOne(['id' => $po->supplier_id]);

            $awardedItems = BidWinner::find()
                ->select(['pr_item_id'])
                ->where([
                    'bid_id' => $bid->id,
                    'supplier_id' => $supplier->id,
                    'status' => 'Awarded'
                ])
                ->asArray()
                ->all();
            
            $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

            $items = PrItemCost::find()
                ->select([
                    'ppmp_pr_item.id as id',
                    'ppmp_item.id as item_id',
                    'ppmp_item.title as item',
                    'ppmp_item.unit_of_measure as unit',
                    'ppmp_pr_item.cost as cost',
                    'ppmp_pr_item_cost.cost as offer',
                    'sum(ppmp_pr_item.quantity) as total'
                ])
                ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
                ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->andWhere(['ppmp_pr_item_cost.pr_id' => $model->id])
                ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
                ->andWhere(['ppmp_pr_item_cost.rfq_id' => $bid->rfq->id])
                ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
                ->andWhere(['in', 'ppmp_pr_item.id', $orsItemIDs])
                ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
                ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
                ->orderBy(['item' => SORT_ASC])
                ->asArray()
                ->all();

            $prexcs = PrItemCost::find()
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
                    ) as pap',
                    'ppmp_obj.code as objCode',
                    'sum(ppmp_pr_item.quantity * ppmp_pr_item_cost.cost) as total'
                ])
                ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_obj', 'ppmp_obj.id = ppmp_ppmp_item.obj_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->andWhere(['ppmp_pr_item_cost.pr_id' => $model->id])
                ->andWhere(['ppmp_pr_item_cost.supplier_id' => $supplier->id])
                ->andWhere(['ppmp_pr_item_cost.rfq_id' => $bid->rfq->id])
                ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
                ->andWhere(['in', 'ppmp_pr_item.id', $orsItemIDs])
                ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
                ->andWhere(['not in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                ->groupBy(['ppmp_activity.id', 'ppmp_activity.id', 'ppmp_obj.id'])
                ->orderBy(['pap' => SORT_ASC, 'objCode' => SORT_ASC])
                ->asArray()
                ->all();

        }else if($ors->type == 'NP'){
             $supplier = null;
             
            $awardedItems = $nonProcurableItemIDs;

            $items = PrItem::find()
            ->select([
                'ppmp_pr_item.id as id',
                'ppmp_item.id as item_id',
                'ppmp_item.title as item',
                'ppmp_item.unit_of_measure as unit',
                'ppmp_pr_item.cost as cost',
                'ppmp_pr_item.cost as offer',
                'sum(ppmp_pr_item.quantity) as total'
            ])
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->andWhere(['in', 'ppmp_pr_item.id', $orsItemIDs])
            ->andWhere(['in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
            ->groupBy(['ppmp_item.id', 'ppmp_pr_item.cost'])
            ->orderBy(['item' => SORT_ASC])
            ->asArray()
            ->all();

            $prexcs = PrItem::find()
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
                    ) as pap',
                    'ppmp_obj.code as objCode',
                    'sum(ppmp_pr_item.quantity * ppmp_pr_item.cost) as total'
                ])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_obj', 'ppmp_obj.id = ppmp_ppmp_item.obj_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->andWhere(['ppmp_pr_item.pr_id' => $model->id])
                ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
                ->andWhere(['in', 'ppmp_pr_item.id', $orsItemIDs])
                ->andWhere(['in', 'ppmp_pr_item.id', $nonProcurableItemIDs])
                ->groupBy(['ppmp_activity.id', 'ppmp_activity.id', 'ppmp_obj.id'])
                ->orderBy(['pap' => SORT_ASC, 'objCode' => SORT_ASC])
                ->asArray()
                ->all();
        }
        
        $prexcData = [];

        if(!empty($prexcs))
        {
            foreach($prexcs as $prexc)
            {
                $prexcData[$prexc['pap']][$prexc['objCode']] = $prexc['total'];
            }
        }

        $rowspan = count($prexcs);

        return $this->renderAjax('\reports\ors', [
            'model' => $model,
            'po' => $po,
            'supplier' => $supplier,
            'ors' => $ors,
            'items' => $items,
            'prexcData' => $prexcData,
            'rowspan' => $rowspan,
        ]);
    }

    // Obligate Items -> Delete ORS
    public function actionDeleteOrs($id, $ors_id)
    {
        $model = $this->findModel($id);
        $ors = Ors::findOne($ors_id);

        if(Yii::$app->request->isPost)
        {
            OrsItem::deleteAll(['ors_id' => $ors->id]);

            $ors->delete();
        }
    }

    public function actionAoq($id)
    {
        $model = $this->findModel($id);

        $rfqs = Rfq::findAll(['pr_id' => $model->id]);

        $items = [];

        if($rfqs)
        {
            foreach($rfqs as $key => $rfq)
            {
                $agency = Settings::findOne(['title' => 'Agency Name']);
                $regionalOffice = Settings::findOne(['title' => 'Regional Office']);
                $address = Settings::findOne(['title' => 'Address']);
                $email = Settings::findOne(['title' => 'Email']);
                $telephoneNos = Settings::findOne(['title' => 'Telephone Nos.']);
                $regionalDirector = Settings::findOne(['title' => 'Regional Director']);
                $bid = Bid::findOne(['pr_id' => $model->id, 'rfq_id' => $rfq->id]);
                $bidWinners = $bid ? BidWinner::findAll(['bid_id' => $bid->id]) : [];
                $bidMembers = $bid ? BidMember::findAll(['bid_id' => $bid->id]) : [];
                $specifications = [];
                $forContractItems = [];

                $aprItemIDs = AprItem::find()
                            ->select(['pr_item_id'])
                            ->leftJoin('ppmp_apr', 'ppmp_apr.id = ppmp_apr_item.apr_id')
                            ->where(['pr_id' => $model->id])
                            ->asArray()
                            ->all();

                $aprItemIDs = ArrayHelper::map($aprItemIDs, 'pr_item_id', 'pr_item_id');

                $rfqItems = PrItem::find()
                    ->select([
                        'ppmp_ris.id as ris_id',
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
                        'ppmp_pr_item.pr_id' => $model->id,
                    ])
                    ->andWhere(['not in', 'ppmp_pr_item.id', $aprItemIDs])
                    ->groupBy(['ppmp_item.id', 's.id', 'ppmp_pr_item.cost'])
                    ->asArray()
                    ->all();
                
                $risIDs = ArrayHelper::map($rfqItems, 'ris_id', 'ris_id');
                $risNumbers = Ris::find()->select(['ris_no'])->where(['in', 'id', $risIDs])->asArray()->all();
                $risNumbers = ArrayHelper::map($risNumbers, 'ris_no', 'ris_no');
                $risNumbers = implode(", ", $risNumbers);

                $supplierIDs = PrItemCost::find()->select(['supplier_id'])->andWhere(['pr_id' => $model->id, 'rfq_id' => $rfq->id])->andWhere(['<>', 'supplier_id', 1])->groupBy(['supplier_id'])->asArray()->all();
                $supplierIDs = ArrayHelper::map($supplierIDs, 'supplier_id', 'supplier_id');

                $supplierList = Supplier::find()->where(['in', 'id', $supplierIDs])->all();
                $prices = [];
                $colors = [];
                $winners = [];
                $justifications = [];

                    if(!empty($rfqItems))
                    {
                        foreach($rfqItems as $item)
                        {
                            $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                            if($specs){ $specifications[$item['id']] = $specs; }
            
                            $forContractItem = ForContractItem::findOne(['item_id' => $item['item_id']]);
                            if($forContractItem){ $forContractItems[$item['id']] = $forContractItem; }
                            
                            $winner = $bid ? BidWinner::findOne(['bid_id' => $bid->id, 'pr_item_id' => $item['id'], 'status' => 'Awarded']) : []; 
                            $winners[$item['id']] = !empty($winner) ? Supplier::findOne(['id' => $winner->supplier_id]) : [];
                            $justifications[$item['id']] = !empty($winner) ? $winner->justification : '';
                            if($supplierList)
                            {
                                foreach($supplierList as $sup)
                                {
                                    $prices[$item['id']][$sup->id] = PrItemCost::findOne(['pr_id' => $model->id, 'pr_item_id' => $item['id'], 'rfq_id' => $rfq->id, 'supplier_id' => $sup->id]);
                                    $colors[$item['id']][$sup->id] = !empty($winner) ? $winner->supplier_id == $sup->id ? 'yellow' : 'transparent' : 'transparent';
                                }
                            }
                        }
                    }

                if($bid)
                {
                $items[$key]['label'] = '<table style="width:100%;" id="aoq-table-modal-'.$rfq->id.'">';
                $items[$key]['label'] .= '<tr>';
                $items[$key]['label'] .= '<td>Canvass/Bid No. '.$bid->bid_no.'</td>';
                $items[$key]['label'] .= '</tr>';
                $items[$key]['label'] .= '</table>';
                $items[$key]['content'] = $this->renderAjax('_aoq', [
                    'model' => $model,
                    'bid' => $bid,
                    'prices' => $prices,
                    'colors' => $colors,
                    'winners' => $winners,
                    'justifications' => $justifications,
                    'bidMembers' => $bidMembers,
                    'rfq' => $rfq,
                    'rfqItems' => $rfqItems,
                    'specifications' => $specifications,
                    'agency' => $agency,
                    'regionalOffice' => $regionalOffice,
                    'address' => $address,
                    'email' => $email,
                    'telephoneNos' => $telephoneNos,
                    'forContractItems' => $forContractItems,
                    'risNumbers' => $risNumbers,
                    'supplierList' => $supplierList,
                    'regionalDirector' => $regionalDirector,
                ]);
                $items[$key]['options'] = ['class' => 'panel panel-info'];
                }
            }
        }

        return $this->renderAjax('_aoq-modal', [
            'model' => $model,
            'rfqs' => $rfqs,
            'items' => $items,
        ]);
    }

    // Print PR
    public function actionPrintPr($id)
    {
        $model = $this->findModel($id);
        $lotItems = [];
        $specifications = [];
        $entityName = Settings::findOne(['title' => 'Entity Name']);
        $fundCluster = FundCluster::findOne($model->fund_cluster_id);
        $rccs = Pritem::find()
                ->select(['IF(ppmp_pap.short_code IS NULL,
                concat(
                    ppmp_cost_structure.code,"",
                    ppmp_organizational_outcome.code,"",
                    ppmp_program.code,"",
                    ppmp_sub_program.code,"",
                    ppmp_identifier.code,"",
                    ppmp_pap.code,"000-",
                    ppmp_activity.code
                )
                ,
                concat(
                    ppmp_pap.short_code,"-",
                    ppmp_activity.code
                )
            ) as prexc',])
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $model->id,
                ])
                ->groupBy(['ppmp_activity.id'])
                ->asArray()
                ->all();
        
        $rccs = ArrayHelper::map($rccs, 'prexc', 'prexc');
        
        $items = PrItem::find()
                ->select([
                    'ppmp_pr_item.id as id',
                    's.id as ris_item_spec_id',
                    'ppmp_item.id as item_id',
                    'ppmp_item.title as item',
                    'IF(ppmp_lot.title IS NOT NULL, concat("Lot No. ",ppmp_lot.lot_no," - ",ppmp_lot.title), 0) as lotTitle',
                    'ppmp_item.unit_of_measure as unit',
                    'ppmp_pr_item.cost as cost',
                    'sum(ppmp_pr_item.quantity) as total'
                ])
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
                ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_pr_item.cost and 
                                                    s.type = ppmp_pr_item.type')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $model->id,
                ])
                ->groupBy(['ppmp_item.id', 's.id', 'ppmp_pr_item.cost', 'lotTitle'])
                ->orderBy(['lotTitle' => SORT_ASC, 'item' => SORT_ASC])
                ->asArray()
                ->all();
        
        if(!empty($items))
        {
            foreach($items as $item)
            {
                $lotItems[$item['lotTitle']][] = $item;

                $specs = RisItemSpec::findOne(['id' => $item['ris_item_spec_id']]);
                if($specs){ $specifications[$item['id']] = $specs; }
            }
        }

        return $this->renderAjax('\reports\pr', [
            'model' => $model,
            'entityName' => $entityName,
            'fundCluster' => $fundCluster,
            'rccs' => $rccs,
            'items' => $items,
            'lotItems' => $lotItems,
            'specifications' => $specifications,
        ]);
    }

    /**
     * Creates a new Pr model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Pr();

        $approver = Settings::findOne(['title' => 'PR Approver']);
        $model->approved_by = $approver ? $approver->value : '';

        $offices = Office::find()->all();
        $offices = ArrayHelper::map($offices, 'abbreviation', 'abbreviation');

        $fundSources = FundSource::find()->all();
        $fundSources = ArrayHelper::map($fundSources, 'id', 'code');

        $fundClusters = FundCluster::find()->all();
        $fundClusters = ArrayHelper::map($fundClusters, 'id', 'title');

        $signatories = Signatory::find()->all();
        $signatories = ArrayHelper::map($signatories, 'emp_id', 'name');

        $years = Ppmp::find()->select(['distinct(year) as year'])->where(['stage' => 'Final'])->orderBy(['year' => SORT_DESC])->asArray()->all();
        $years = ArrayHelper::map($years, 'year', 'year');

        $procurementModes = ProcurementMode::find()->all();
        $procurementModes = ArrayHelper::map($procurementModes, 'id', 'title');

        $types = [
            'Supply' => 'Goods',
            'Service' => 'Service/Contract',
        ];

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            $lastPr = Pr::find()->where(['year' => $model->year])->orderBy(['id' => SORT_DESC])->one();
            $lastNumber = $lastPr ? intval(substr($lastPr->pr_no, -3)) : '001';
            $pr_no = $lastPr ? substr($model->year, -2).'-'.date("m").'-'.str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT) : substr($model->year, -2).'-'.date("m").'-'.$lastNumber;
            $model->pr_no = $pr_no;
            $model->created_by = Yii::$app->user->identity->userinfo->EMP_N; 
            $model->date_created = date("Y-m-d"); 
            $model->save();

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('create', [
            'model' => $model,
            'offices' => $offices,
            'fundSources' => $fundSources,
            'fundClusters' => $fundClusters,
            'signatories' => $signatories,
            'types' => $types,
            'years' => $years,
            'procurementModes' => $procurementModes
        ]);
    }

    /**
     * Updates an existing Pr model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $offices = Office::find()->all();
        $offices = ArrayHelper::map($offices, 'abbreviation', 'abbreviation');

        $fundSources = FundSource::find()->all();
        $fundSources = ArrayHelper::map($fundSources, 'id', 'code');

        $fundClusters = FundCluster::find()->all();
        $fundClusters = ArrayHelper::map($fundClusters, 'id', 'title');

        $signatories = Signatory::find()->all();
        $signatories = ArrayHelper::map($signatories, 'emp_id', 'name');

        $years = Ppmp::find()->select(['distinct(year) as year'])->where(['stage' => 'Final'])->orderBy(['year' => SORT_DESC])->asArray()->all();
        $years = ArrayHelper::map($years, 'year', 'year');

        $procurementModes = ProcurementMode::find()->all();
        $procurementModes = ArrayHelper::map($procurementModes, 'id', 'title');

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
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('update', [
            'model' => $model,
            'offices' => $offices,
            'fundSources' => $fundSources,
            'fundClusters' => $fundClusters,
            'signatories' => $signatories,
            'types' => $types,
            'years' => $years,
            'procurementModes' => $procurementModes
        ]);
    }

    /**
     * Deletes an existing Pr model.
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
            $statuses = Transaction::deleteAll(['model' => 'Pr', 'model_id' => $id]);
        }
        
        \Yii::$app->getSession()->setFlash('success', 'Record Deleted');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Pr model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Pr the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pr::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
