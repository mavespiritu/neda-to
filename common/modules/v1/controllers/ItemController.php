<?php

namespace common\modules\v1\controllers;

use Yii;
use common\modules\v1\models\ProcurementMode;
use common\modules\v1\models\Obj;
use common\modules\v1\models\Item;
use common\modules\v1\models\ItemCost;
use common\modules\v1\models\ItemSearch;
use common\modules\v1\models\ItemCostSearch;
use common\modules\v1\models\ObjectItem;
use common\modules\v1\models\ObjectItemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\widgets\ActiveForm;
use kartik\mpdf\Pdf;
/**
 * ItemController implements the CRUD actions for Item model.
 */
class ItemController extends Controller
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
                        'roles' => ['SupplyStaff', 'Administrator'],
                    ],
                ],
            ],
        ];
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

    /**
     * Lists all Item models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $procurementModes = ProcurementMode::find()->all();
        $procurementModes = ArrayHelper::map($procurementModes, 'id', 'title');

        $classifications = [
            'Direct Contracting' => 'Direct Contracting',
            'PPE' => 'PPE',
            'Semi-Expendable' => 'Semi-Expendable',
            'Supply' => 'Supply',
            'Services' => 'Services',
            'Others' => 'Others',
        ];

        $categories = [
            'PESTICIDES OR PEST REPELLENTS' => 'PESTICIDES OR PEST REPELLENTS',
            'PERFUMES OR COLOGNES OR FRAGRANCES' => 'PERFUMES OR COLOGNES OR FRAGRANCES',
            'ALCOHOL OR ACETONE BASED ANTISEPTICS' => 'ALCOHOL OR ACETONE BASED ANTISEPTICS',
            'COLOR COMPOUNDS AND DISPERSIONS' => 'COLOR COMPOUNDS AND DISPERSIONS',
            'FILMS' => 'FILMS',
            'PAPER MATERIALS AND PRODUCTS' => 'PAPER MATERIALS AND PRODUCTS',
            'BATTERIES AND CELLS AND ACCESSORIES' => 'BATTERIES AND CELLS AND ACCESSORIES',
            'MANUFACTURING COMPONENTS AND SUPPLIES' => 'MANUFACTURING COMPONENTS AND SUPPLIES',
            'HEATING AND VENTILATION AND AIR CIRCULATION' => 'HEATING AND VENTILATION AND AIR CIRCULATION',
            'MEDICAL THERMOMETERS AND ACCESSORIES' => 'MEDICAL THERMOMETERS AND ACCESSORIES',
            'LIGHTING AND FIXTURES AND ACCESSORIES' => 'LIGHTING AND FIXTURES AND ACCESSORIES',
            'MEASURING AND OBSERVING AND TESTING EQUIPMENT' => 'MEASURING AND OBSERVING AND TESTING EQUIPMENT',
            'CLEANING EQUIPMENT AND SUPPLIES' => 'CLEANING EQUIPMENT AND SUPPLIES',
            'INFORMATION AND COMMUNICATION TECHNOLOGY (ICT) EQUIPMENT AND DEVICES AND ACCESSORIES' => 'INFORMATION AND COMMUNICATION TECHNOLOGY (ICT) EQUIPMENT AND DEVICES AND ACCESSORIES',
            'OFFICE EQUIPMENT AND ACCESSORIES AND SUPPLIES' => 'OFFICE EQUIPMENT AND ACCESSORIES AND SUPPLIES',
            'PRINTER OR FACSIMILE OR PHOTOCOPIER SUPPLIES' => 'PRINTER OR FACSIMILE OR PHOTOCOPIER SUPPLIES',
            'AUDIO AND VISUAL EQUIPMENT AND SUPPLIES' => 'AUDIO AND VISUAL EQUIPMENT AND SUPPLIES',
            'FLAG OR ACCESSORIES' => 'FLAG OR ACCESSORIES',
            'PRINTED PUBLICATIONS' => 'PRINTED PUBLICATIONS',
            'FIRE FIGHTING EQUIPMENT' => 'FIRE FIGHTING EQUIPMENT',
            'CONSUMER ELECTRONICS' => 'CONSUMER ELECTRONICS',
            'FURNITURE AND FURNISHINGS',
            'ARTS AND CRAFTS EQUIPMENT AND ACCESSORIES AND SUPPLIES' => 'ARTS AND CRAFTS EQUIPMENT AND ACCESSORIES AND SUPPLIES',
            'FACE MASK' => 'FACE MASK',
            'SOFTWARE' => 'SOFTWARE',
            'OTHER ITEMS' => 'OTHER ITEMS'
        ];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'procurementModes' => $procurementModes,
            'categories' => $categories,
            'classifications' => $classifications,
        ]);
    }

    public function actionDownload($type, $params)
    {
        $params = json_decode($params, true);
        $params = isset($params['ItemSearch']) ? $params['ItemSearch'] : [];

        $items = Item::find()
                ->select([
                    'ppmp_procurement_mode.title as procurement_mode',
                    'ppmp_item.category as dbm_category',
                    'ppmp_item.code as dbm_code',
                    'ppmp_item.title',
                    'ppmp_item.unit_of_measure',
                    'IF(costPerUnit.cost IS NOT NULL, costPerUnit.cost, ppmp_item.cost_per_unit) as cost_per_unit',
                    'ppmp_item.cse',
                    'ppmp_item.classification'
                ])
                ->leftJoin('ppmp_procurement_mode', 'ppmp_procurement_mode.id = ppmp_item.procurement_mode_id')
                ->leftJoin(['costPerUnit' => '(
                    select
                        ppmp_item_cost.id,
                        ppmp_item_cost.item_id,
                        ppmp_item_cost.cost
                    from ppmp_item_cost
                    inner join
                    (select max(id) as id from ppmp_item_cost group by item_id) latest on latest.id = ppmp_item_cost.id
                    )'], 'costPerUnit.item_id = ppmp_item.id');

        if(isset($params['procurement_mode_id']) && $params['procurement_mode_id'] != '')
        {
            $items->andWhere(['ppmp_item.procurement_mode_id' => $params['procurement_mode_id']]);
        }

        if(isset($params['category']) && $params['category'] != '')
        {
            $items->andWhere(['ppmp_item.category' => $params['category']]);
        }

        if(isset($params['code']) && $params['code'] != '')
        {
            $items->andWhere(['ppmp_item.code' => $params['code']]);
        }

        if(isset($params['title']) && $params['title'] != '')
        {
            $items->andWhere(['like', 'ppmp_item.title', $params['title']]);
        }

        if(isset($params['unit_of_measure']) && $params['unit_of_measure'] != '')
        {
            $items->andWhere(['ppmp_item.unit_of_measure' => $params['unit_of_measure']]);
        }
        
        if(isset($params['cse']) && $params['cse'] != '')
        {
            $items->andWhere(['ppmp_item.cse' => $params['cse']]);
        }

        if(isset($params['category']) && $params['category'] != '')
        {
            $items->andWhere(['ppmp_item.category' => $params['category']]);
        }

        if(isset($params['classification']) && $params['classification'] != '')
        {
            $items->andWhere(['ppmp_item.classification' => $params['classification']]);
        }

        $items = $items
        ->orderBy(['ppmp_item.title' => SORT_ASC])
        ->asArray()
        ->all();

        $filename = 'OPMS_Item_List_as_of_'.date("mdYHis");

        if($type == 'excel')
        {
            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=".$filename.".xls");
            return $this->renderPartial('_file', [
                'type' => $type,
                'items' => $items
            ]);
        }else if($type == 'pdf')
        {
            $content = $this->renderPartial('_file', [
                'type' => $type,
                'items' => $items
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
                                    font-family: "Arial";
                                    border-collapse: collapse;
                                }
                                thead{
                                    font-size: 12px;
                                    text-align: center;
                                }
                            
                                td{
                                    font-size: 10px;
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

    /**
     * Displays a single Item model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model =  $this->findModel($id);
        $itemCostSearchModel = new ItemCostSearch();
        $itemCostSearchModel->item_id = $model->id;
        $itemCostdataProvider = $itemCostSearchModel->search(Yii::$app->request->queryParams);
        $objectItemSearchModel = new ObjectItemSearch();
        $objectItemSearchModel->item_id = $model->id;
        $objectItemdataProvider = $objectItemSearchModel->search(Yii::$app->request->queryParams);

        $objectItemModel = new ObjectItem();

        $existingObjs = ObjectItem::find()->select(['obj_id'])->where(['item_id' => $model->id])->asArray()->all();
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

        if($objectItemModel->load(Yii::$app->request->post()))
        {
            $objectItemModel->item_id = $model->id;
            $objectItemModel->save();

            \Yii::$app->getSession()->setFlash('success', 'Object is successfully assigned');
            return $this->redirect(['/v1/item/view', 'id' => $model->id]);
        }

        return $this->render('view', [
            'model' => $model,
            'itemCostSearchModel' => $itemCostSearchModel,
            'itemCostdataProvider' => $itemCostdataProvider,
            'objectItemSearchModel' => $objectItemSearchModel,
            'objectItemdataProvider' => $objectItemdataProvider,
            'objectItemModel' => $objectItemModel,
            'objs' => $objs,
        ]);
    }

    /**
     * Creates a new Item model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Item();

        $procurementModes = ProcurementMode::find()->all();
        $procurementModes = ArrayHelper::map($procurementModes, 'id', 'title');

        $classifications = [
            'Direct Contracting' => 'Direct Contracting',
            'PPE' => 'PPE',
            'Semi-Expendable' => 'Semi-Expendable',
            'Supply' => 'Supply',
            'Services' => 'Services',
            'Others' => 'Others',
        ];

        $categories = [
            'PESTICIDES OR PEST REPELLENTS' => 'PESTICIDES OR PEST REPELLENTS',
            'PERFUMES OR COLOGNES OR FRAGRANCES' => 'PERFUMES OR COLOGNES OR FRAGRANCES',
            'ALCOHOL OR ACETONE BASED ANTISEPTICS' => 'ALCOHOL OR ACETONE BASED ANTISEPTICS',
            'COLOR COMPOUNDS AND DISPERSIONS' => 'COLOR COMPOUNDS AND DISPERSIONS',
            'FILMS' => 'FILMS',
            'PAPER MATERIALS AND PRODUCTS' => 'PAPER MATERIALS AND PRODUCTS',
            'BATTERIES AND CELLS AND ACCESSORIES' => 'BATTERIES AND CELLS AND ACCESSORIES',
            'MANUFACTURING COMPONENTS AND SUPPLIES' => 'MANUFACTURING COMPONENTS AND SUPPLIES',
            'HEATING AND VENTILATION AND AIR CIRCULATION' => 'HEATING AND VENTILATION AND AIR CIRCULATION',
            'MEDICAL THERMOMETERS AND ACCESSORIES' => 'MEDICAL THERMOMETERS AND ACCESSORIES',
            'LIGHTING AND FIXTURES AND ACCESSORIES' => 'LIGHTING AND FIXTURES AND ACCESSORIES',
            'MEASURING AND OBSERVING AND TESTING EQUIPMENT' => 'MEASURING AND OBSERVING AND TESTING EQUIPMENT',
            'CLEANING EQUIPMENT AND SUPPLIES' => 'CLEANING EQUIPMENT AND SUPPLIES',
            'INFORMATION AND COMMUNICATION TECHNOLOGY (ICT) EQUIPMENT AND DEVICES AND ACCESSORIES' => 'INFORMATION AND COMMUNICATION TECHNOLOGY (ICT) EQUIPMENT AND DEVICES AND ACCESSORIES',
            'OFFICE EQUIPMENT AND ACCESSORIES AND SUPPLIES' => 'OFFICE EQUIPMENT AND ACCESSORIES AND SUPPLIES',
            'PRINTER OR FACSIMILE OR PHOTOCOPIER SUPPLIES' => 'PRINTER OR FACSIMILE OR PHOTOCOPIER SUPPLIES',
            'AUDIO AND VISUAL EQUIPMENT AND SUPPLIES' => 'AUDIO AND VISUAL EQUIPMENT AND SUPPLIES',
            'FLAG OR ACCESSORIES' => 'FLAG OR ACCESSORIES',
            'PRINTED PUBLICATIONS' => 'PRINTED PUBLICATIONS',
            'FIRE FIGHTING EQUIPMENT' => 'FIRE FIGHTING EQUIPMENT',
            'CONSUMER ELECTRONICS' => 'CONSUMER ELECTRONICS',
            'FURNITURE AND FURNISHINGS',
            'ARTS AND CRAFTS EQUIPMENT AND ACCESSORIES AND SUPPLIES' => 'ARTS AND CRAFTS EQUIPMENT AND ACCESSORIES AND SUPPLIES',
            'FACE MASK' => 'FACE MASK',
            'SOFTWARE' => 'SOFTWARE',
            'OTHER ITEMS' => 'OTHER ITEMS'
        ];

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            if($model->save())
            {
                $cost = new ItemCost();
                $cost->item_id = $model->id;
                $cost->cost = $model->cost_per_unit;
                $cost->save();
            }
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->renderAjax('create', [
            'model' => $model,
            'procurementModes' => $procurementModes,
            'categories' => $categories,
            'classifications' => $classifications,
        ]);
    }

    /**
     * Updates an existing Item model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id, $return_url)
    {
        $model = $this->findModel($id);

        $procurementModes = ProcurementMode::find()->all();
        $procurementModes = ArrayHelper::map($procurementModes, 'id', 'title');

        $classifications = [
            'Direct Contracting' => 'Direct Contracting',
            'PPE' => 'PPE',
            'Semi-Expendable' => 'Semi-Expendable',
            'Supply' => 'Supply',
            'Services' => 'Services',
            'Others' => 'Others',
        ];

        $categories = [
            'PESTICIDES OR PEST REPELLENTS' => 'PESTICIDES OR PEST REPELLENTS',
            'PERFUMES OR COLOGNES OR FRAGRANCES' => 'PERFUMES OR COLOGNES OR FRAGRANCES',
            'ALCOHOL OR ACETONE BASED ANTISEPTICS' => 'ALCOHOL OR ACETONE BASED ANTISEPTICS',
            'COLOR COMPOUNDS AND DISPERSIONS' => 'COLOR COMPOUNDS AND DISPERSIONS',
            'FILMS' => 'FILMS',
            'PAPER MATERIALS AND PRODUCTS' => 'PAPER MATERIALS AND PRODUCTS',
            'BATTERIES AND CELLS AND ACCESSORIES' => 'BATTERIES AND CELLS AND ACCESSORIES',
            'MANUFACTURING COMPONENTS AND SUPPLIES' => 'MANUFACTURING COMPONENTS AND SUPPLIES',
            'HEATING AND VENTILATION AND AIR CIRCULATION' => 'HEATING AND VENTILATION AND AIR CIRCULATION',
            'MEDICAL THERMOMETERS AND ACCESSORIES' => 'MEDICAL THERMOMETERS AND ACCESSORIES',
            'LIGHTING AND FIXTURES AND ACCESSORIES' => 'LIGHTING AND FIXTURES AND ACCESSORIES',
            'MEASURING AND OBSERVING AND TESTING EQUIPMENT' => 'MEASURING AND OBSERVING AND TESTING EQUIPMENT',
            'CLEANING EQUIPMENT AND SUPPLIES' => 'CLEANING EQUIPMENT AND SUPPLIES',
            'INFORMATION AND COMMUNICATION TECHNOLOGY (ICT) EQUIPMENT AND DEVICES AND ACCESSORIES' => 'INFORMATION AND COMMUNICATION TECHNOLOGY (ICT) EQUIPMENT AND DEVICES AND ACCESSORIES',
            'OFFICE EQUIPMENT AND ACCESSORIES AND SUPPLIES' => 'OFFICE EQUIPMENT AND ACCESSORIES AND SUPPLIES',
            'PRINTER OR FACSIMILE OR PHOTOCOPIER SUPPLIES' => 'PRINTER OR FACSIMILE OR PHOTOCOPIER SUPPLIES',
            'AUDIO AND VISUAL EQUIPMENT AND SUPPLIES' => 'AUDIO AND VISUAL EQUIPMENT AND SUPPLIES',
            'FLAG OR ACCESSORIES' => 'FLAG OR ACCESSORIES',
            'PRINTED PUBLICATIONS' => 'PRINTED PUBLICATIONS',
            'FIRE FIGHTING EQUIPMENT' => 'FIRE FIGHTING EQUIPMENT',
            'CONSUMER ELECTRONICS' => 'CONSUMER ELECTRONICS',
            'FURNITURE AND FURNISHINGS',
            'ARTS AND CRAFTS EQUIPMENT AND ACCESSORIES AND SUPPLIES' => 'ARTS AND CRAFTS EQUIPMENT AND ACCESSORIES AND SUPPLIES',
            'FACE MASK' => 'FACE MASK',
            'SOFTWARE' => 'SOFTWARE',
            'OTHER ITEMS' => 'OTHER ITEMS'
        ];

        $urls = explode('/', $return_url);
        $urls = array_splice($urls, 2, count($urls));
        $urls = implode('/', $urls);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $cost = new ItemCost();
            $cost->item_id = $model->id;
            $cost->cost = $model->cost_per_unit;
            $cost->source_model = 'Item';
            $cost->source_id = $model->id;
            $cost->save();

            \Yii::$app->getSession()->setFlash('success', 'Record Updated');
            return $this->redirect(['/'.$urls]);
        }

        return $this->renderAjax('update', [
            'model' => $model,
            'procurementModes' => $procurementModes,
            'categories' => $categories,
            'classifications' => $classifications,
        ]);
    }

    /**
     * Deletes an existing Item model.
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

    public function actionDeleteObject($id)
    {
        $model = ObjectItem::findOne($id);
        $item = $model->item;
        $model->delete();

        \Yii::$app->getSession()->setFlash('success', 'Record Deleted');
        return $this->redirect(['/v1/item/view', 'id' => $item->id]);
    }

    /**
     * Finds the Item model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Item the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Item::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
