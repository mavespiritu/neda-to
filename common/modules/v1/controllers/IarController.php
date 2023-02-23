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
        return $this->render('view', [
            'model' => $this->findModel($id),
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->getSession()->setFlash('success', 'Record Updated');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
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
