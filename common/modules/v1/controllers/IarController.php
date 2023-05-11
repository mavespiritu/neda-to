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
use common\modules\v1\models\IarSearch;
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
 * IarController implements the CRUD actions for Iar model.
 */
class IarController extends Controller
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
                'only' => ['index', 'view', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'view', 'delete'],
                        'allow' => true,
                        'roles' => ['SupplyStaff', 'Administrator'],
                    ],
                ],
            ],
        ];
    }

    public function actionPoList($id)
    {
        $pos = Po::find()->where(['pr_id' => $id])->all(); 

        $arr = [];
        $arr[] = ['id'=>'','text'=>''];
        if($pos)
        {
            foreach($pos as $po){
                $arr[] = ['id' => $po->id ,'text' => $po->po_no.' - '.$po->supplier->business_name];
            }
        }
        
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    /**
     * Lists all Iar models.
     * @return mixed
     */
    public function actionIndex()
    {
        $session = Yii::$app->session;

        $session->set('IAR_ReturnURL', Yii::$app->controller->module->getBackUrl(Url::to()));

        $searchModel = new IarSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Iar model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $pr = Pr::findOne($model->pr_id);
        $po = Po::findOne($model->po_id);
        $bid = Bid::findOne(['id' => $po->bid_id]);
        $supplier = Supplier::findOne($po->supplier_id);
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
                    'ppmp_pr_item_cost.pr_id' => $pr->id,
                    'ppmp_pr_item_cost.supplier_id' => $supplier->id,
                    'ppmp_pr_item_cost.rfq_id' => $bid->rfq_id,
                ])
                ->andWhere(['in', 'ppmp_pr_item_cost.pr_item_id', $awardedItems])
                ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
                ->orderBy(['item' => SORT_ASC])
                ->asArray()
                ->all();

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
                'ppmp_pr_item.pr_id' => $pr->id,
            ])
            ->groupBy(['ppmp_activity.id'])
            ->asArray()
            ->all();
        
        $rccs = ArrayHelper::map($rccs, 'prexc', 'prexc');
        
        $itemModels = [];

        if(!empty($items))
        {
            foreach($items as $item)
            {
                $itemModel = IarItem::findOne(['iar_id' => $model->id, 'pr_item_id' => $item['id']]) ? IarItem::findOne(['iar_id' => $model->id, 'pr_item_id' => $item['id']]) : new IarItem();
                $itemModel->iar_id = $model->id;
                $itemModel->pr_item_id = $item['id'];
                $itemModels[$item['id']] = $itemModel; 
            }
        }

        if(MultipleModel::loadMultiple($itemModels, Yii::$app->request->post()))
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

                    $includedItems = PrItemCost::find()
                            ->select(['ppmp_pr_item.id as id'])
                            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
                            ->leftJoin('ppmp_lot_item', 'ppmp_lot_item.pr_item_id = ppmp_pr_item.id')
                            ->leftJoin('ppmp_lot', 'ppmp_lot.id = ppmp_lot_item.lot_id')
                            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                            ->andWhere([
                                'ppmp_pr_item.pr_id' => $pr->id,
                                'ppmp_item.id' => $item->ppmpItem->item_id,
                                'ppmp_pr_item.cost' => $item->cost,
                                'ppmp_pr_item_cost.supplier_id' => $supplier->id,
                                'ppmp_pr_item_cost.rfq_id' => $bid->rfq_id,
                            ])
                            ->andWhere(['in', 'ppmp_pr_item_cost.pr_item_id', $awardedItems])
                            ->groupBy(['ppmp_item.id', 'ppmp_pr_item_cost.cost'])
                            ->all();
                
                    $includedItems = ArrayHelper::map($includedItems, 'id', 'id');

                    if(!empty($includedItems))
                    {
                        foreach($includedItems as $includedItem)
                        {
                            $iarItem = IarItem::findOne(['iar_id' => $model->id, 'pr_item_id' => $item['id']]) ? IarItem::findOne(['iar_id' => $model->id, 'pr_item_id' => $includedItem]) : new IarItem();
                            $iarItem->iar_id = $model->id;
                            $iarItem->pr_item_id = $includedItem;
                            $iarItem->balance = $itemModel->balance;
                            $iarItem->delivery_time = $itemModel->delivery_time;
                            $iarItem->courtesy = $itemModel->courtesy;
                            $iarItem->save();
                        }
                    }
                }
            }

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['/v1/iar/view', 'id' => $model->id]);
        }

        return $this->render('view', [
            'model' => $model,
            'itemModels' => $itemModels,
            'pr' => $pr,
            'po' => $po,
            'bid' => $bid,
            'supplier' => $supplier,
            'entity' => $entity,
            'items' => $items,
            'rccs' => $rccs,
        ]);
    }

    /**
     * Creates a new Iar model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Iar();

        $prs = Pr::find()->orderBy(['id' => SORT_DESC])->asArray()->all();
        $prs = ArrayHelper::map($prs, 'id', 'pr_no');

        $pos = [];

        $inspectors = Signatory::findAll(['designation' => 'Inspection Officer']);
        $inspectors = ArrayHelper::map($inspectors, 'emp_id', 'name');

        $supplyOfficers = Signatory::findAll(['designation' => 'Supply Officer']);
        $supplyOfficers = ArrayHelper::map($supplyOfficers, 'emp_id', 'name');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            $lastIar = Iar::find()->orderBy(['id' => SORT_DESC])->one();
            $lastNumber = $lastIar ? str_pad(intval(substr($lastIar->iar_no, -4)) + 1, 4, '0', STR_PAD_LEFT) : '0001';
            $iarNo = substr(date("Y"), -2).'-'.$lastNumber;
            
            $model->iar_no = $iarNo;
            $model->save();

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('create', [
            'model' => $model,
            'prs' => $prs,
            'pos' => $pos,
            'inspectors' => $inspectors,
            'supplyOfficers' => $supplyOfficers,
        ]);
    }

    /**
     * Updates an existing Iar model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $prs = Pr::find()->orderBy(['id' => SORT_DESC])->asArray()->all();
        $prs = ArrayHelper::map($prs, 'id', 'pr_no');

        $pos = Po::find()->where(['pr_id' => $model->pr_id])->all(); 
        $pos = ArrayHelper::map($pos, 'id', 'po_no');

        $inspectors = Signatory::findAll(['designation' => 'Inspection Officer']);
        $inspectors = ArrayHelper::map($inspectors, 'emp_id', 'name');

        $supplyOfficers = Signatory::findAll(['designation' => 'Supply Officer']);
        $supplyOfficers = ArrayHelper::map($supplyOfficers, 'emp_id', 'name');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->save();

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('update', [
            'model' => $model,
            'prs' => $prs,
            'pos' => $pos,
            'inspectors' => $inspectors,
            'supplyOfficers' => $supplyOfficers,
        ]);

    }

    /**
     * Deletes an existing Iar model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        \Yii::$app->getSession()->setFlash('success', 'Record Deleted');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Iar model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Iar the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Iar::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
