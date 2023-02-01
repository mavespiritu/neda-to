<?php

namespace common\modules\v1\controllers;

use Yii;
use common\modules\v1\models\DefaultObj;
use common\modules\v1\models\DefaultPap;
use common\modules\v1\models\FundSource;
use common\modules\v1\models\Pap;
use common\modules\v1\models\Obj;
use common\modules\v1\models\Appropriation;
use common\modules\v1\models\AppropriationPap;
use common\modules\v1\models\AppropriationObj;
use common\modules\v1\models\AppropriationItem;
use common\modules\v1\models\AppropriationAllocation;
use common\modules\v1\models\AppropriationSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\web\Response;
use markavespiritu\user\models\Office;

/**
 * PpmpController implements the CRUD actions for Ppmp model.
 */
class GaaController extends Controller
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
                        'actions' => ['index', 'create', 'copy', 'view', 'form', 'delete'],
                        'allow' => true,
                        'roles' => ['AccountingStaff', 'Administrator'],
                    ],
                ],
            ],
        ];
    }

    function recursive(array $elements, $parentId = null) {
        $branch = array();

        foreach ($elements as $element) {
            if ($element['obj_id'] == $parentId) {
                $children = $this->recursive($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
    
        return $branch;
    }

    function lastnodes(array $elements, $parentId = null) {
        $branch = array();
    
        foreach ($elements as $element) {
            if ($element['obj_id'] == $parentId) {
                $children = $this->lastnodes($elements, $element['id']);
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

    function lastlevel(array $elements, $parentId = null) {
        $branch = array();
    
        foreach ($elements as $element) {
            if ($element['obj_id'] == $parentId) {
                $children = $this->lastnodes($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }

                $branch[] = $element;
            }
        }
    
        return $branch;
    }

    public function actionFundSourceList($id, $pap_id)
    {
        $existingFundSources = AppropriationPap::find()->select(['fund_source_id'])->where(['appropriation_id' => $id, 'pap_id' => $pap_id])->asArray()->all();
        $existingFundSources = ArrayHelper::map($existingFundSources, 'fund_source_id', 'fund_source_id');

        $fundSources = FundSource::find()->select([
            'id',
            'code'
        ])
        ->where(['not in', 'id', $existingFundSources])
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

    /**
     * Lists all Ppmp models.
     * @return mixed
     */
    public function actionIndex()
    {
        $session = Yii::$app->session;

        $session->set('GAA_ReturnURL', Yii::$app->controller->module->getBackUrl(Url::to()));

        $searchModel = new AppropriationSearch();
        $searchModel->type = 'GAA';

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $papModel = new AppropriationPap();
        $papModel->appropriation_id = $model->id;

        $paps = Pap::find()->all();
        $paps = ArrayHelper::map($paps, 'id', 'codeAndTitle');

        $fundSources = [];

        if($papModel->load(Yii::$app->request->post()))
        {

            $lastPap = AppropriationPap::find()->where(['appropriation_id' => $model->id])->orderBy(['arrangement' => SORT_DESC])->one();

            $papModel->arrangement = $lastPap ? $lastPap->arrangement + 1 : 1;
            $papModel->save();
        }

        return $this->render('view', [
            'model' => $model,
            'papModel' => $papModel,
            'paps' => $paps,
            'fundSources' => $fundSources,
        ]);
    }

    public function actionPrograms($id)
    {
        $model = $this->findModel($id);
        $papModel = new AppropriationPap();
        $items = [];
        $models = [];
        if($model->appropriationPaps)
        {
            foreach($model->getAppropriationPaps()->orderBy(['arrangement' => SORT_ASC])->all() as $pap)
            {
                $items[$pap->id] = [
                    'content' => '
                        <div style="display: flex;">
                            <div><i class="fa fa-arrows-alt"></i></div>
                            <div style="padding-left: 10px;">
                                <input type="checkbox" name="checkbox[]" class="chkbox-paps" value="'.$pap->id.'"> (<b>'.$pap->fundSource->code.'</b>) - '.$pap->pap->codeAndTitle.'
                            </div>
                        </div>'
                    ];
                $models[$pap->id] = $pap;
            }
        }

        if(Yii::$app->request->post())
        {   
            $postData = Yii::$app->request->post('AppropriationPap');

            $newIndexes = $postData['arrangement'];
            $newIndexes = explode(",", $newIndexes); 

            if(!empty($newIndexes))
            {
                foreach($newIndexes as $idx => $papID)
                {
                    $models[$papID]->arrangement = $idx + 1; 
                    $models[$papID]->save();
                }
            }                             
        }

        return $this->renderAjax('paps', [
            'model' => $model,
            'papModel' => $papModel,
            'items' => $items,
        ]);

    }

    public function actionDeleteProgram($id)
    {
        $model = $this->findModel($id);
        $papModel = new AppropriationPap();
        $items = [];
        $models = [];

        if($model->appropriationPaps)
        {
            foreach($model->getAppropriationPaps()->orderBy(['arrangement' => SORT_ASC])->all() as $pap)
            {
                $items[$pap->id] = [
                    'content' => '
                        <div style="display: flex;">
                            <div><i class="fa fa-arrows-alt"></i></div>
                            <div style="padding-left: 10px;">
                                <input type="checkbox" name="checkbox[]" class="chkbox-paps" value="'.$pap->id.'"> (<b>'.$pap->fundSource->code.'</b>) - '.$pap->pap->codeAndTitle.'
                            </div>
                        </div>'
                    ];
                    
                $models[$pap->id] = $pap;
            }
        }

        if(Yii::$app->request->post())
        {
            $postData = Yii::$app->request->post();
            $ids = json_decode($postData['ids']);
            if(!empty($ids))
            {
                foreach($ids as $id)
                {
                    $pap = AppropriationPap::findOne($id);
                    AppropriationItem::findOne(['appropriation_id' => $pap->appropriation_id, 'pap_id' => $pap->pap_id, 'fund_source_id' => $pap->fund_source_id])->delete();
                }
            }
            AppropriationPap::deleteAll(['id' => $ids]);
        }

        return $this->renderAjax('paps', [
            'model' => $model,
            'papModel' => $papModel,
            'items' => $items,
        ]);
    }

    public function actionDefaultProgram()
    {
        if(Yii::$app->request->post())
        {
            $postData = Yii::$app->request->post();
            if(!empty($postData))
            {
                $models = json_decode($postData['models'],true);
                DefaultPap::deleteAll(['type' => 'GAA']);
                if(!empty($models))
                {
                    foreach($models as $model)
                    {
                        $newModel = new DefaultPap();
                        $newModel->type = 'GAA';
                        $newModel->pap_id = $model['pap_id'];
                        $newModel->fund_source_id = $model['fund_source_id'];
                        $newModel->arrangement = $model['arrangement'];
                        $newModel->save();
                    }
                }
            }
        }
    }

    public function actionObjectForm($id)
    {
        $model = $this->findModel($id);

        $objModel = new AppropriationObj();
        $objModel->appropriation_id = $model->id;

        $existingObjs = $model->getAppropriationObjs()->asArray()->all();
        $existingObjs = ArrayHelper::map($existingObjs, 'obj_id', 'obj_id');

        $objs = Obj::find()->select([
            'ppmp_obj.id', 
            'ppmp_obj.obj_id', 
            'concat(ppmp_obj.code," - ",ppmp_obj.title) as text',
            'p.title as groupTitle',
            'ppmp_obj.active'
            ])
            ->leftJoin(['p' => '(SELECT id, concat(code," - ",title) as title from ppmp_obj)'], 'p.id = ppmp_obj.obj_id')
            ->andWhere(['not in', 'ppmp_obj.id', $existingObjs])
            ->asArray()
            ->all();
        
        $objs = $this->lastnodes($objs);

        $objs = ArrayHelper::map($objs, 'id', 'text', 'groupTitle');

        if($objModel->load(Yii::$app->request->post()))
        {

            $lastObj = AppropriationObj::find()->where(['appropriation_id' => $model->id])->orderBy(['arrangement' => SORT_DESC])->one();

            $objModel->arrangement = $lastObj ? $lastObj->arrangement + 1 : 1;
            $objModel->save();
        }

        return $this->renderAjax('object-form', [
            'model' => $model,
            'objModel' => $objModel,
            'objs' => $objs,
        ]);
    }

    public function actionObjects($id)
    {
        $model = $this->findModel($id);
        $objModel = new AppropriationObj();
        $items = [];
        $models = [];
        if($model->appropriationObjs)
        {
            foreach($model->getAppropriationObjs()->orderBy(['arrangement' => SORT_ASC])->all() as $obj)
            {
                $items[$obj->id] = [
                    'content' => '
                        <div style="display: flex;">
                            <div><i class="fa fa-arrows-alt"></i></div>
                            <div style="padding-left: 10px;">
                                <input type="checkbox" name="checkbox[]" class="chkbox-objs" value="'.$obj->id.'"> '.$obj->obj->objectTitle.' (<b>'.$obj->obj->objTitle.'</b>)
                            </div>
                        </div>'
                    ];
                $models[$obj->id] = $obj;
            }
        }

        if(Yii::$app->request->post())
        {   
            $postData = Yii::$app->request->post('AppropriationObj');

            $newIndexes = $postData['arrangement'];
            $newIndexes = explode(",", $newIndexes); 

            if(!empty($newIndexes))
            {
                foreach($newIndexes as $idx => $objID)
                {
                    $models[$objID]->arrangement = $idx + 1; 
                    $models[$objID]->save();
                }
            }                             
        }

        return $this->renderAjax('objs', [
            'model' => $model,
            'objModel' => $objModel,
            'items' => $items,
        ]);

    }

    public function actionDeleteObject($id)
    {
        $model = $this->findModel($id);
        $objModel = new Appropriationobj();
        $items = [];
        $models = [];

        if($model->appropriationPaps)
        {
            foreach($model->getAppropriationObjs()->orderBy(['arrangement' => SORT_ASC])->all() as $obj)
            {
                $items[$obj->id] = [
                    'content' => '
                        <div style="display: flex;">
                            <div><i class="fa fa-arrows-alt"></i></div>
                            <div style="padding-left: 10px;">
                                <input type="checkbox" name="checkbox[]" class="chkbox-objs" value="'.$obj->id.'"> '.$obj->obj->objectTitle.' (<b>'.$obj->obj->objTitle.'</b>)
                            </div>
                        </div>'
                    ];
                $models[$obj->id] = $obj;
            }
        }

        if(Yii::$app->request->post())
        {
            $postData = Yii::$app->request->post();
            $ids = json_decode($postData['ids']);
            if(!empty($ids))
            {
                foreach($ids as $id)
                {
                    $obj = AppropriationObj::findOne($id);
                    AppropriationItem::findOne(['appropriation_id' => $obj->appropriation_id, 'obj_id' => $obj->obj_id])->delete();
                }
            }
            AppropriationObj::deleteAll(['id' => $ids]);
        }

        return $this->renderAjax('objs', [
            'model' => $model,
            'objModel' => $objModel,
            'items' => $items,
        ]);
    }

    public function actionDefaultObject()
    {
        if(Yii::$app->request->post())
        {
            $postData = Yii::$app->request->post();
            if(!empty($postData))
            {
                $models = json_decode($postData['models'],true);
                Defaultobj::deleteAll(['type' => 'GAA']);
                if(!empty($models))
                {
                    foreach($models as $model)
                    {
                        $newModel = new DefaultObj();
                        $newModel->type = 'GAA';
                        $newModel->obj_id = $model['obj_id'];
                        $newModel->arrangement = $model['arrangement'];
                        $newModel->save();
                    }
                }
            }
        }
    }

    public function actionForm($id)
    {
        $model = $this->findModel($id);

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

        //cho "<pre>"; print_r($items); exit;

        if(Yii::$app->request->post())
        {
            $postData = Yii::$app->request->post('AppropriationItem');
            $idx = $postData['idx'];

            $item = AppropriationItem::findOne(['appropriation_id' => $model->id, 'obj_id' => $postData[$idx]['obj_id'], 'pap_id' => $postData[$idx]['pap_id'], 'fund_source_id' => $postData[$idx]['fund_source_id']]) ? 
            AppropriationItem::findOne(['appropriation_id' => $model->id, 'obj_id' => $postData[$idx]['obj_id'], 'pap_id' => $postData[$idx]['pap_id'], 'fund_source_id' => $postData[$idx]['fund_source_id']]) : 
            new AppropriationItem();

            $amount = explode(",",$postData[$idx]['amount']);
            $amount = implode("", $amount);
            
            $item->appropriation_id = $model->id;
            $item->obj_id = $postData[$idx]['obj_id'];
            $item->pap_id = $postData[$idx]['pap_id'];
            $item->fund_source_id = $postData[$idx]['fund_source_id'];
            $item->amount = $amount;
            $item->save(false);
        }
        
        return $this->render('form', [
            'model' => $model,
            'items' => $items,
        ]);
    }

    public function actionAllocate($id)
    {
        $model = $this->findModel($id);
        $items = [];
        $objects = $model->getAppropriationItems()->joinWith('appropriationObj')->groupBy(['obj_id'])->orderBy(['arrangement' => SORT_ASC])->all();

        if($objects)
        {
            foreach($objects as $object)
            {
                $programs = $model->getAppropriationItems()->joinWith('appropriationPap')->where(['obj_id' => $object->obj_id])->andWhere(['>', 'amount', 0])->orderBy(['arrangement' => SORT_ASC])->all();
                
                if($programs)
                {
                    $content = '<ul>';

                    foreach($programs as $program)
                    {
                       $content.= '<li><a href="javascript:void(0);" onclick="allocate('.$program->id.')">'.$program->fundSource->code.': '.$program->appropriationPap->pap->codeAndTitle.'</a></li>';
                    }

                    $content .= '<ul>';

                    $items[$object->obj_id] = [
                        'label' => $object->appropriationObj->obj->objectTitle,
                        'content' => $content
                    ];
                }
            }
        }

        return $this->render('allocate', [
            'model' => $model,
            'items' => $items,
        ]);
    }

    public function actionAllocationForm($id)
    {
        $model = AppropriationItem::findOne($id);
        $offices = Office::find()->all();
        $items = [];

        if($offices)
        {
            foreach($offices as $office)
            {
                $item = AppropriationAllocation::findOne(['appropriation_item_id' => $model->id, 'office_id' => $office->id]) ?
                AppropriationAllocation::findOne(['appropriation_item_id' => $model->id, 'office_id' => $office->id]) :
                new AppropriationAllocation();

                $item->appropriation_item_id = $model->id;
                $item->office_id = $office->id;
                $item->amount = $item->isNewRecord ? 0 : $item->amount;

                $items[$office->id] = $item;
            }
        }
        
        $allocation = new AppropriationAllocation();

        if($allocation->load(Yii::$app->request->post()) && $allocation->validate())
        {
            $newAllocation = AppropriationAllocation::findOne(['appropriation_item_id' => $allocation->appropriation_item_id, 'office_id' => $allocation->office_id]) ?
            AppropriationAllocation::findOne(['appropriation_item_id' => $allocation->appropriation_item_id, 'office_id' => $allocation->office_id]) :
            new AppropriationAllocation();

            $newAllocation->appropriation_item_id = $allocation->appropriation_item_id;
            $newAllocation->office_id = $allocation->office_id;
            $newAllocation->amount = $allocation->amount;
            $newAllocation->save();
        }

        return $this->renderAjax('_allocate', [
            'model' => $model,
            'offices' => $offices,
            'items' => $items,
        ]);
    }

    public function actionValidateAmount()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model=  new AppropriationAllocation();
        $model->load(Yii::$app->request->post());
        return ActiveForm::validate($model);
    }

    /**
     * Creates a new Ppmp model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Appropriation();
        $model->type = 'GAA';

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

        	$model->created_by = Yii::$app->user->id;
        	$model->date_created = date("Y-m-d H:i:s");
        	$model->save();

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('create', [
            'model' => $model,
        ]);
    }

    public function actionCopy()
    {
        $model = new Appropriation();
        $model->type = 'GAA';
        $model->scenario = 'copy';

        $neps = Appropriation::find()->select(['id', 'concat(type," ",year) as title'])->where(['type' => 'GAA'])->orderBy(['year' => SORT_DESC])->asArray()->all();
        $neps = ArrayHelper::map($neps, 'id', 'title');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

        	$model->created_by = Yii::$app->user->id;
        	$model->date_created = date("Y-m-d H:i:s");
        	$model->save();

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->renderAjax('copy', [
            'model' => $model,
            'neps' => $neps,
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->getSession()->setFlash('success', 'Record Updated');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
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
        $this->findModel($id)->delete();
        \Yii::$app->getSession()->setFlash('success', 'Record Deleted');
        return $this->redirect(['index']);
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
        if (($model = Appropriation::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
