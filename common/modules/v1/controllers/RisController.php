<?php

namespace common\modules\v1\controllers;

use Yii;
use common\modules\v1\models\AppropriationItem;
use common\modules\v1\models\Activity;
use common\modules\v1\models\SubActivity;
use common\modules\v1\models\FUndSource;
use common\modules\v1\models\Ris;
use common\modules\v1\models\Ppmp;
use common\modules\v1\models\PpmpItem;
use common\modules\v1\models\PpmpItemSearch;
use common\modules\v1\models\FundCluster;
use common\modules\v1\models\Signatory;
use common\modules\v1\models\RisItem;
use common\modules\v1\models\RisSource;
use common\modules\v1\models\ItemBreakdown;
use common\modules\v1\models\RisSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use markavespiritu\user\models\Office;
use yii\widgets\ActiveForm;
use yii\web\Response;
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
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'view', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
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
            'Supply' => 'Supply',
            'Service' => 'Service',
        ];

        $offices = Office::find()->all();
        $offices = ArrayHelper::map($offices, 'abbreviation', 'abbreviation');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'offices' => $offices,
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
                         'ppmp_activity.title as text',
                         'p.title as groupTitle'
                     ])
                     ->leftJoin(['p' => '(SELECT id, code, title from ppmp_pap)'], 'p.id = ppmp_activity.pap_id')
                     ->andWhere(['in', 'ppmp_activity.id', $fundedActivities])
                     ->asArray()
                     ->all();
        
        $activities = ArrayHelper::map($activities, 'id', 'text', 'groupTitle');
                        
        $subActivities = [];

        $fundSources = FundSource::find()->all();
        $fundSources = ArrayHelper::map($fundSources, 'id', 'code');

        return $this->render('view', [
            'model' => $model,
            'appropriationItemModel' => $appropriationItemModel,
            'activities' => $activities,
            'subActivities' => $subActivities,
            'fundSources' => $fundSources,
        ]);
    }

    public function actionLoadItems($id, $activity_id, $sub_activity_id, $fund_source_id)
    {
        $model = $this->findModel($id);
        $ppmp = $model->ppmp;
        $activity = Activity::findOne($activity_id);
        $subActivity = SubActivity::findOne($sub_activity_id);
        $fundSource = FundSource::findOne($fund_source_id);

        $searchModel = new PpmpItemSearch();
        $searchModel->ppmp_id = $ppmp->id;
        $searchModel->activity_id = $activity->id;
        $searchModel->fund_source_id = $fundSource->id;
        $searchModel->sub_activity_id = $subActivity->id;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderAjax('_ris-items', [
            'model' => $model,
            'activity' => $activity,
            'subActivity' => $subActivity,
            'fundSource' => $fundSource,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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

    public function actionBuy($id, $item_id)
    {
        $model = $this->findModel($id);
        $item = PpmpItem::findOne($item_id);

        $risItemModel = new RisItem();
        $risItemModel->ris_id = $model->id;
        $risItemModel->ppmp_item_id = $item->id;

        $months = ItemBreakdown::find()
                ->alias('i')
                ->select(['ppmp_month.id as id', 'ppmp_month.month as title'])
                ->leftJoin('ppmp_month', 'ppmp_month.id = i.month_id')
                ->andWhere(['i.ppmp_item_id' => $item->id])
                ->andWhere(['>', 'quantity', 0])
                ->asArray()
                ->all();

        $months = ArrayHelper::map($months, 'id', 'title');

        if($risItemModel->load(Yii::$app->request->post()))
        {
            if($risItemModel->save())
            {
                if($risItemModel->ppmpItem->type == 'Original')
                {
                    $source = new RisSource();
                    $source->ris_id = $model->id;
                    $source->ris_item_id = $risItemModel->id;
                    $source->ppmp_item_id = $item->id;
                    $source->month_id = $risItemModel->month_id;
                    $source->quantity = $risItemModel->quantity;
                    $source->type = 'Original';
                    $source->save();
                }
            }
        }

        return $this->renderAjax('_buy', [
            'model' => $model,
            'item' => $item,
            'risItemModel' => $risItemModel,
            'months' => $months,
        ]);
    }

    public function actionInfo($id)
    {
        $model = $this->findModel($id);

        return $this->renderAjax('_info', [
            'model' => $model,
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

        $model->scenario = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('Procurement')) ? 'isAdmin' : 'isUser';

        $ppmps = Yii::$app->user->can('Administrator') ? [] : Ppmp::find()
        ->joinWith('office')
        ->where(['stage' => 'Final'])
        ->andWhere(['office_id' => Yii::$app->user->identity->userinfo->OFFICE_C])
        ->orderBy(['year' => SORT_DESC])
        ->all();
        
        $ppmps = Yii::$app->user->can('Administrator') ? ArrayHelper::map($ppmps, 'id', 'title') : ArrayHelper::map($ppmps, 'id', 'year');

        $offices = Office::find()->all();
        $offices = ArrayHelper::map($offices, 'abbreviation', 'abbreviation');

        $fundClusters = FundCluster::find()->all();
        $fundClusters = ArrayHelper::map($fundClusters, 'id', 'title');

        $signatories = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('Procurement')) ? [] : Signatory::find()->where(['office_id' => Yii::$app->user->identity->userinfo->OFFICE_C])->all();
        $signatories = ArrayHelper::map($signatories, 'emp_id', 'name');

        $types = [
            'Supply' => 'Supply',
            'Service' => 'Service',
        ];

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            $lastRis = Ris::find()->where(['date_created' => date("Y-m-d")])->orderBy(['id' => SORT_DESC])->one();
            $userOffice = Office::findOne(['id' => Yii::$app->user->identity->userinfo->OFFICE_C]);
            $ris_no = $lastRis ? substr(date("Y"), -2).'-'.date("md").'-'.str_pad(substr($lastRis->ris_no, -1) + 1, 3, '0', STR_PAD_LEFT) : substr(date("Y"), -2).'-'.date("md").'-001';
            $model->ris_no = $ris_no;
            $model->office_id = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('Procurement')) ? $model->office_id : $userOffice->abbreviation;
            $model->date_requested = $model->date_required; 
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
            'fundClusters' => $fundClusters,
            'signatories' => $signatories,
            'types' => $types
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

        $model->scenario = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('Procurement')) ? 'isAdmin' : 'isUser';

        $ppmps = Yii::$app->user->can('Administrator') ? Ppmp::find()
        ->joinWith('office')
        ->where(['stage' => 'Final'])
        ->orderBy(['year' => SORT_DESC])
        ->all() : Ppmp::find()
        ->joinWith('office')
        ->where(['stage' => 'Final'])
        ->andWhere(['office_id' => Yii::$app->user->identity->userinfo->OFFICE_C])
        ->orderBy(['year' => SORT_DESC])
        ->all();
        
        $ppmps = Yii::$app->user->can('Administrator') ? ArrayHelper::map($ppmps, 'id', 'title') : ArrayHelper::map($ppmps, 'id', 'year');

        $offices = Office::find()->all();
        $offices = ArrayHelper::map($offices, 'abbreviation', 'abbreviation');

        $fundClusters = FundCluster::find()->all();
        $fundClusters = ArrayHelper::map($fundClusters, 'id', 'title');

        $signatories = (Yii::$app->user->can('Administrator') || Yii::$app->user->can('Procurement')) ? Signatory::find()->all() : Signatory::find()->where(['office_id' => Yii::$app->user->identity->userinfo->OFFICE_C])->all();
        $signatories = ArrayHelper::map($signatories, 'emp_id', 'name');

        $types = [
            'Supply' => 'Supply',
            'Service' => 'Service',
        ];

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->getSession()->setFlash('success', 'Record Updated');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('update', [
            'model' => $model,
            'ppmps' => $ppmps,
            'offices' => $offices,
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
        $this->findModel($id)->delete();
        \Yii::$app->getSession()->setFlash('success', 'Record Deleted');
        return $this->redirect(['index']);
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
