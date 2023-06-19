<?php

namespace common\modules\v1\controllers;

use Yii;
use common\modules\v1\models\Region;
use common\modules\v1\models\Province;
use common\modules\v1\models\Citymun;
use common\modules\v1\models\Employee;
use common\modules\v1\models\Location;
use common\modules\v1\models\ConcernStaff;
use common\modules\v1\models\TravelOrder;
use common\modules\v1\models\TravelOrderLocation;
use common\modules\v1\models\TravelOrderVehicle;
use common\modules\v1\models\DigitalSignature;
use common\modules\v1\models\TravelType;
use common\modules\v1\models\Vehicle;
use common\modules\v1\models\Driver;
use common\modules\v1\models\TravelOrderSearch;
use common\modules\v1\models\Model;
use common\modules\v1\models\MultipleModel;
use markavespiritu\user\models\UserInfo;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * TravelOrderController implements the CRUD actions for TravelOrder model.
 */
class TravelOrderController extends Controller
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
                'only' => ['index', 'create', 'update', 'view', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'view', 'delete'],
                        'allow' => true,
                        'roles' => ['Staff'],
                    ],
                ],
            ],
        ];
    }

    public function toNumber($date)
    {

        $year = date("Y", strtotime($date));
        $i = "0001";

        $travelOrder = TravelOrder::find()->where(['YEAR(date_filed)' => $year])->orderBy(['date_filed' => SORT_DESC])->one();

        if ($travelOrder) {
        $no = substr($travelOrder->TO_NO, -4);
        $i = intval($no) + 1;
        $i = str_pad($i, 4, "0", STR_PAD_LEFT);
        }

        $number = $year.$i;

        return $number;

    }

    public function actionProvinceList($id)
    {
        $provinces = Province::find()
                    ->select(['id', 'description'])
                    ->where(['region' => $id])
                    ->orderBy(['description' => SORT_ASC])
                    ->asArray()
                    ->all();

        $arr = [];
        $arr[] = ['id'=>'','text'=>''];
        foreach($provinces as $province){
            $arr[] = ['id' => $province['id'] ,'text' => $province['description']];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    public function actionCitymunList($id)
    {
        $citymuns = Citymun::find()
                    ->select(['id', 'description'])
                    ->where(['province_ID' => $id])
                    ->orderBy(['description' => SORT_ASC])
                    ->asArray()
                    ->all();

        $arr = [];
        $arr[] = ['id'=>'','text'=>''];
        foreach($citymuns as $citymun){
            $arr[] = ['id' => $citymun['id'] ,'text' => $citymun['description']];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    /**
     * Lists all TravelOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TravelOrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionSearch()
    {
        $searchModel = new TravelOrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderAjax('_search', [
            'model' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single TravelOrder model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionTravelInfo($id)
    {
        $model = $this->findModel($id);

        return $this->renderAjax('_travel-info', [
            'model' => $model,
        ]);
    }

    public function actionDestinationInfo($id)
    {
        $model = $this->findModel($id);

        return $this->renderAjax('_destination-info', [
            'model' => $model,
        ]);
    }

    public function actionVehicleInfo($id)
    {
        $model = $this->findModel($id);

        return $this->renderAjax('_vehicle-info', [
            'model' => $model,
        ]);
    }

    public function actionApprovalInfo($id)
    {
        $model = $this->findModel($id);

        return $this->renderAjax('_approval-info', [
            'model' => $model,
        ]);
    }

    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $concernStaffs = ConcernStaff::find()->select([
            'tbltev_concernedstaff.emp_id as emp_id',
            'IF(staff.mname <> "", concat(staff.fname," ",LEFT(staff.mname, 1),". ",staff.lname), concat(staff.fname," ",staff.lname)) as concernStaff',
            'staff.lname as concernStaffLastName',
            'IF(staff.mname <> "", concat(staff.fname," ",LEFT(staff.mname, 1),". ",staff.lname," (",staff.division_id,")"), concat(staff.fname," ",staff.lname," (",staff.division_id,")")) as concernStaffWithDivision',
            'IF(recommendingApprover.mname <> "", concat(recommendingApprover.fname," ",LEFT(recommendingApprover.mname, 1),". ",recommendingApprover.lname), concat(recommendingApprover.fname," ",recommendingApprover.lname)) as recommender',
            'recommendingApprover.lname as recommenderLastName',
            'IF(recommendingApprover.mname <> "", concat(recommendingApprover.fname," ",LEFT(recommendingApprover.mname, 1),". ",recommendingApprover.lname," (",recommendingApprover.division_id,")"), concat(recommendingApprover.fname," ",recommendingApprover.lname," (",recommendingApprover.division_id,")")) as recommenderWithDivision',
            'IF(finalApprover.mname <> "", concat(finalApprover.fname," ",LEFT(finalApprover.mname, 1),". ",finalApprover.lname), concat(finalApprover.fname," ",finalApprover.lname)) as approver',
            'finalApprover.lname as approverLastName',
            'IF(finalApprover.mname <> "", concat(finalApprover.fname," ",LEFT(finalApprover.mname, 1),". ",finalApprover.lname," (",finalApprover.division_id,")"), concat(finalApprover.fname," ",finalApprover.lname," (",finalApprover.division_id,")")) as approverWithDivision',
        ])
        ->leftJoin('tblemployee staff', 'staff.emp_id = tbltev_concernedstaff.emp_id')
        ->leftJoin('tbltev_authapprover', 'tbltev_authapprover.emp_id = tbltev_concernedstaff.emp_id')
        ->leftJoin('tblemployee recommendingApprover', 'recommendingApprover.emp_id = tbltev_authapprover.recommending')
        ->leftJoin('tblemployee finalApprover', 'finalApprover.emp_id = tbltev_authapprover.final')
        ->where(['tbltev_concernedstaff.TO_NO' => $model->TO_NO])
        ->orderBy(['staff.division_id' => SORT_ASC, 'concernStaffWithDivision' => SORT_ASC])
        ->asArray()
        ->all();

        $staffs = ArrayHelper::map($concernStaffs, 'concernStaffWithDivision', 'concernStaffWithDivision');
        $staffs = array_filter($staffs, function($value) {
            return $value !== "";
        });

        $halfOfStaffs = ceil(count($staffs) / 2);

        if (count($staffs) > 8) {
            $firstStaffs = array_slice($staffs, 0, $halfOfStaffs);
            $secondStaffs = array_slice($staffs, $halfOfStaffs);
        } else {
            $firstStaffs = $staffs;
            $secondStaffs = [];
        }

        $recommenders = ArrayHelper::map($concernStaffs, 'recommenderWithDivision', 'recommenderWithDivision');
        $recommenders = array_filter($recommenders, function($value) {
            return $value !== "";
        });

        $recommenders = array_values(array_diff($recommenders, $staffs));

        $approvers = ArrayHelper::map($concernStaffs, 'approver', 'approver');
        $approvers = array_filter($approvers, function($value) {
            return $value !== "";
        });

        $approver = array_values(array_diff_key($approvers, $recommenders));

        $staffEmptyLines = max(8 - count($staffs), 0);
        $staffEmptyLines = $staffEmptyLines > 0 ? $staffEmptyLines : 0;

        $doubleStaffEmptyLines = max(16 - count($staffs), 0);
        $doubleStaffEmptyLines = $doubleStaffEmptyLines > 0 ? $doubleStaffEmptyLines : 0;

        $recommenderEmptyLines = max(8 - count($recommenders), 0);
        $recommenderEmptyLines = $recommenderEmptyLines > 0 ? $recommenderEmptyLines : 0;

        return $this->renderAjax('report', [
            'model' => $model,
            'staffs' => $staffs,
            'recommenders' => $recommenders,
            'approver' => $approver,
            'firstStaffs' => $firstStaffs,
            'secondStaffs' => $secondStaffs,
            'staffEmptyLines' => $staffEmptyLines,
            'doubleStaffEmptyLines' => $doubleStaffEmptyLines,
            'recommenderEmptyLines' => $recommenderEmptyLines,
        ]);
    }

    /**
     * Creates a new TravelOrder model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TravelOrder();
        $destinationModels = [new TravelOrderLocation()];

        $travelTypes = TravelType::find()->orderBy(['description' => SORT_ASC])->all();
        $travelTypes = ArrayHelper::map($travelTypes, 'id', 'description');

        $regions = Region::find()->orderBy(['description' => SORT_ASC])->all();
        $regions = ArrayHelper::map($regions, 'id', 'description');

        $provinces = [];
        $citymuns = [];

        $staffs = Employee::find()->select(['emp_id', 'concat(fname," ",lname) as name'])->where(['work_status' => 'Active'])->asArray()->all();
        $staffs = ArrayHelper::map($staffs, 'emp_id', 'name');

        if ($model->load(Yii::$app->request->post())) {

            $destinationModels = Model::createMultiple(TravelOrderLocation::classname());
            Model::loadMultiple($destinationModels, Yii::$app->request->post());

            $valid = $model->validate();
            $valid = Model::validateMultiple($destinationModels) && $valid;

            if($valid){
                $transaction = \Yii::$app->db->beginTransaction();

                $staffModels = $model->staffs;

                $model->TO_NO = $this->toNumber(date("Y-m-d H:i:s"));
                $model->date_filed = date("Y-m-d H:i:s");
                $model->TO_creator = Yii::$app->user->identity->userinfo->EMP_N;
                
                try{
                    if($flag = $model->save(false)){

                        if(!empty($staffModels))
                        {
                            foreach($staffModels as $staff)
                            {
                                $lastStaff = ConcernStaff::find()->orderBy(['id' => SORT_DESC])->one();
                                $staffModel = new ConcernStaff();
                                $staffModel->id = $lastStaff ? $lastStaff->id + 1 : 0;
                                $staffModel->TO_NO = $model->TO_NO;
                                $staffModel->emp_id = $staff;
                                $staffModel->date_modified = date("Y-m-d H:i:s");

                                /* $user = UserInfo::findOne(['EMP_N' => $staffModel->emp_id]);

                                if($user){
                                    $mailer = Yii::$app->mailer->compose([
                                        'html' => 'travel-order-detail-html'
                                    ],[
                                        'model' => $model,
                                    ]);
                                    $mailer->setFrom('mvespiritu@neda.gov.ph');
                                    $mailer->setTo($user->user->email);
                                    $mailer->setSubject('Travel Order No. '.$model->TO_NO);
                                    $mailer->send();
                                }

                                */
                                if (! ($flag = $staffModel->save(false))) {
                                    $transaction->rollBack();
                                    break;
                                } 
                            }
                        }

                        foreach ($destinationModels as $destinationModel) {
                            $lastToLocation = TravelOrderLocation::find()->orderBy(['loc_id' => SORT_DESC])->one();

                            $specLocationModel = Location::find()->where(['municipality' => $destinationModel->citymun, 'description' => $destinationModel->specificLocation])->one();

                            if(!$specLocationModel){
                                $lastSpecLocation = Location::find()->orderBy(['id' => SORT_DESC])->one();
                                $newSpecLocationModel = new Location();
                                $newSpecLocationModel->id = $lastSpecLocation ? $lastSpecLocation->id + 1 : 1;
                                $newSpecLocationModel->description = $destinationModel->specificLocation;
                                $newSpecLocationModel->municipality = $destinationModel->citymun;
                                $newSpecLocationModel->save();

                                $destinationModel->specificLocation = $newSpecLocationModel->id;
                            }else{
                                $destinationModel->specificLocation = $specLocationModel->id;
                            }

                            $destinationModel->loc_id = $lastToLocation ? $lastToLocation->loc_id + 1 : 1;
                            $destinationModel->TO_NO = $model->TO_NO;

                            if (! ($flag = $destinationModel->save(false))) {
                                $transaction->rollBack();
                                break;
                            }
                        }
                    }

                    \Yii::$app->getSession()->setFlash('success', 'Record Saved');
                    return $this->redirect(['view', 'id' => $model->TO_NO]);

                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'travelTypes' => $travelTypes,
            'destinationModels' => (empty($destinationModels)) ? [new TravelOrderLocation] : $destinationModels,
            'regions' => $regions,
            'provinces' => $provinces,
            'citymuns' => $citymuns,
            'staffs' => $staffs,
        ]);
    }

    /**
     * Updates an existing TravelOrder model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if(!Yii::$app->user->can('Staff') || Yii::$app->user->identity->userinfo->EMP_N != $model->TO_creator || $model->isDirector_Approved == 1){
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $concernStaffs = ConcernStaff::find()->where(['TO_NO' => $model->TO_NO])->all();
        $concernStaffs = ArrayHelper::map($concernStaffs, 'emp_id', 'emp_id');

        $model->staffs = $concernStaffs;

        $travelTypes = TravelType::find()->orderBy(['description' => SORT_ASC])->all();
        $travelTypes = ArrayHelper::map($travelTypes, 'id', 'description');

        $regions = Region::find()->orderBy(['description' => SORT_ASC])->all();
        $regions = ArrayHelper::map($regions, 'id', 'description');

        $provinces = [];
        $citymuns = [];

        $destinationModels = $model->travelOrderLocations;

        if($model->travelOrderLocations){
            foreach($model->travelOrderLocations as $idx => $location){
                $destinationModels[$idx]['region'] = $location->location->citymun->province->regionTitle->id;
                $destinationModels[$idx]['province'] = $location->location->citymun->province->id;
                $destinationModels[$idx]['citymun'] = $location->location->citymun->id;
                $destinationModels[$idx]['specificLocation'] = $location->location->description;

                $provincePerLocations = Province::find()
                    ->select(['id', 'description'])
                    ->where(['region' => $destinationModels[$idx]['region']])
                    ->orderBy(['description' => SORT_ASC])
                    ->asArray()
                    ->all();

                $provinces[$idx] = ArrayHelper::map($provincePerLocations, 'id', 'description');

                $citymunPerLocations = Citymun::find()
                            ->select(['id', 'description'])
                            ->where(['province_ID' => $destinationModels[$idx]['province']])
                            ->orderBy(['description' => SORT_ASC])
                            ->asArray()
                            ->all();

                $citymuns[$idx] = ArrayHelper::map($citymunPerLocations, 'id', 'description');

            }
        }

        $staffs = Employee::find()->select(['emp_id', 'concat(fname," ",lname) as name'])->where(['work_status' => 'Active'])->asArray()->all();
        $staffs = ArrayHelper::map($staffs, 'emp_id', 'name');

        if ($model->load(Yii::$app->request->post())) {

            $oldDestinationIDs = ArrayHelper::map($destinationModels, 'loc_id', 'loc_id');

            $destinationModels = Model::createMultiple(TravelOrderLocation::classname(), $destinationModels);

            Model::loadMultiple($destinationModels, Yii::$app->request->post());

            $deletedDestinationIDs = array_diff($oldDestinationIDs, array_filter(ArrayHelper::map($destinationModels, 'loc_id', 
            'loc_id')));

            $oldConcernStaffIDs = $concernStaffs;

            $deletedConcernStaffIDs = array_diff($oldConcernStaffIDs, array_filter(ArrayHelper::map($model->staffs, 'emp_id', 
            'emp_id')));

            // validate all models
            $valid = $model->validate();
            $valid = Model::validateMultiple($destinationModels) && $valid;

            if ($valid) {
                $transaction = \Yii::$app->db->beginTransaction();

                $staffModels = $model->staffs;

                try{
                    if ($flag = $model->save(false)) {

                        if(!empty($deletedConcernStaffIDs))
                        {
                            foreach($deletedConcernStaffIDs as $emp_id){
                                $staff = ConcernStaff::findOne(['TO_NO' => $model->TO_NO, 'emp_id' => $emp_id]);

                                if($staff)
                                {
                                    $staff->delete();
                                }
                            }
                        }
        
                        foreach($staffModels as $staff)
                        {
                            $lastStaff = ConcernStaff::find()->orderBy(['id' => SORT_DESC])->one();
                            $staffModel = ConcernStaff::findOne(['TO_NO' => $model->TO_NO, 'emp_id' => $staff]) ? ConcernStaff::findOne(['TO_NO' => $model->TO_NO, 'emp_id' => $staff]) : new ConcernStaff();
                            $staffModel->id = $staffModel->isNewRecord ? $lastStaff ? $lastStaff->id + 1 : 0 : $staffModel->id;
                            $staffModel->TO_NO = $model->TO_NO;
                            $staffModel->emp_id = $staff;
                            $staffModel->date_modified = date("Y-m-d H:i:s");
                            if (! ($flag = $staffModel->save(false))) {
                                $transaction->rollBack();
                                break;
                            }
                        }

                        if(!empty($deletedDestinationIDs))
                        {
                            TravelOrderLocation::deleteAll(['loc_id' => $deletedDestinationIDs]);
                        }

                        if(!empty($oldDestinationIDs))
                        {
                            TravelOrderLocation::deleteAll(['loc_id' => $oldDestinationIDs]);
                        }
        
                        foreach ($destinationModels as $destinationModel) {
                            $lastToLocation = TravelOrderLocation::find()->orderBy(['loc_id' => SORT_DESC])->one();

                            $specLocationModel = Location::find()->where(['municipality' => $destinationModel->citymun, 'description' => $destinationModel->specificLocation])->one();

                            if(!$specLocationModel){
                                $lastSpecLocation = Location::find()->orderBy(['id' => SORT_DESC])->one();
                                $newSpecLocationModel = new Location();
                                $newSpecLocationModel->id = $lastSpecLocation ? $lastSpecLocation->id + 1 : 1;
                                $newSpecLocationModel->description = $destinationModel->specificLocation;
                                $newSpecLocationModel->municipality = $destinationModel->citymun;
                                $newSpecLocationModel->save();

                                $destinationModel->specificLocation = $newSpecLocationModel->id;
                            }else{
                                $destinationModel->specificLocation = $specLocationModel->id;
                            }

                            $destinationModel->loc_id = $lastToLocation ? $lastToLocation->loc_id + 1 : 1;
                            $destinationModel->TO_NO = $model->TO_NO;

                            if (! ($flag = $destinationModel->save(false))) {
                                $transaction->rollBack();
                                break;
                            }
                        }

                        \Yii::$app->getSession()->setFlash('success', 'Record Updated');
                        return $this->redirect(['view', 'id' => $model->TO_NO]);
                    }

                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }

        }

        return $this->render('update', [
            'model' => $model,
            'travelTypes' => $travelTypes,
            'destinationModels' => (empty($destinationModels)) ? [new TravelOrderLocation] : $destinationModels,
            'regions' => $regions,
            'provinces' => $provinces,
            'citymuns' => $citymuns,
            'staffs' => $staffs,
        ]);
    }

    /**
     * Deletes an existing TravelOrder model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if(!Yii::$app->user->can('Staff') || Yii::$app->user->identity->userinfo->EMP_N != $model->TO_creator || $model->isDirector_Approved == 1){
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        ConcernStaff::deleteAll(['TO_NO' => $model->TO_NO]);
        TravelOrderLocation::deleteAll(['TO_NO' => $model->TO_NO]);
        TravelOrderVehicle::deleteAll(['TO_NO' => $model->TO_NO]);
        DigitalSignature::deleteAll(['TO_NO' => $model->TO_NO]);
        $model->delete();
        
        \Yii::$app->getSession()->setFlash('success', 'Record Deleted');
        return $this->redirect(['index']);
    }

    public function actionDispatch($id)
    {
        if(!Yii::$app->user->can('Dispatcher')){
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $model = $this->findModel($id);
        
        $vehicleModel = new TravelOrderVehicle();
        $vehicleModel->TO_NO = $model->TO_NO;
        $vehicleModel->isapproved = "true";
        $vehicleModel->approvedby = Yii::$app->user->identity->userinfo->EMP_N;

        $vehicles = Vehicle::find()->orderBy(['vehicle_description' => SORT_ASC])->all();
        $vehicles = ArrayHelper::map($vehicles, 'vehicle_code', 'vehicle_description');

        $drivers = Driver::find()
                    ->select(['e.emp_id as id', 'concat(e.fname," ",e.lname) as name'])
                    ->leftJoin('tblemployee e', 'e.emp_id = tbltev_drivers.emp_id')
                    ->orderBy(['name' => SORT_ASC])
                    ->asArray()
                    ->all();
        $drivers = ArrayHelper::map($drivers, 'id', 'name');

        if($vehicleModel->load(Yii::$app->request->post())){
            $vehicleModel->date_approved = date("Y-m-d H:i:s");
            $vehicleModel->save();
        }

        return $this->renderAjax('_dispatch', [
            'model' => $model,
            'vehicleModel' => $vehicleModel,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
        ]);
    }

    public function actionUpdateDispatch($id)
    {
        if(!Yii::$app->user->can('Dispatcher')){
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        
        $vehicleModel = TravelOrderVehicle::findOne(['id' => $id]);

        $model = $vehicleModel->travelOrder;

        $vehicles = Vehicle::find()->orderBy(['vehicle_description' => SORT_ASC])->all();
        $vehicles = ArrayHelper::map($vehicles, 'vehicle_code', 'vehicle_description');

        $drivers = Driver::find()
                    ->select(['e.emp_id as id', 'concat(e.fname," ",e.lname) as name'])
                    ->leftJoin('tblemployee e', 'e.emp_id = tbltev_drivers.emp_id')
                    ->orderBy(['name' => SORT_ASC])
                    ->asArray()
                    ->all();
        $drivers = ArrayHelper::map($drivers, 'id', 'name');

        if($vehicleModel->load(Yii::$app->request->post())){
            $vehicleModel->date_approved = date("Y-m-d H:i:s");
            $vehicleModel->save();
        }

        return $this->renderAjax('_dispatch', [
            'model' => $model,
            'vehicleModel' => $vehicleModel,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
        ]);
    }

    public function actionDeleteDispatch($id)
    {
        $vehicleModel = TravelOrderVehicle::findOne(['id' => $id]);
        $vehicleModel->delete();
    }

    public function actionApprove($id)
    {
        $model = $this->findModel($id);

        $concernStaffs = ConcernStaff::find()->select([
            'recommendingApprover.emp_id as recommender',
            'finalApprover.emp_id as approver',
        ])
        ->leftJoin('tbltev_authapprover', 'tbltev_authapprover.emp_id = tbltev_concernedstaff.emp_id')
        ->leftJoin('tblemployee recommendingApprover', 'recommendingApprover.emp_id = tbltev_authapprover.recommending')
        ->leftJoin('tblemployee finalApprover', 'finalApprover.emp_id = tbltev_authapprover.final')
        ->where(['tbltev_concernedstaff.TO_NO' => $model->TO_NO])
        ->asArray()
        ->all();

        $recommenders = ArrayHelper::map($concernStaffs, 'recommenderWithDivision', 'recommenderWithDivision');
        $recommenders = array_filter($recommenders, function($value) {
            return $value !== "";
        });

        $approvers = ArrayHelper::map($concernStaffs, 'approver', 'approver');
        $approvers = array_filter($approvers, function($value) {
            return $value !== "";
        });

        $approver = array_values(array_diff_key($approvers, $recommenders));

        $approverName = isset($approver[0]) ? Employee::findOne(['emp_id' => $approver[0]]) : [];
        $approverLastName = isset($approver[0]) ? Employee::findOne(['emp_id' => $approver[0]]) : [];
        $approverName = !empty($approverName) ? $approverName->mname != '' ? $approverName->fname.' '.substr($approverName->mname, 0, 1).'. '.$approverName->lname : $approverName->fname.' '.$approverName->lname : 'No set approver. Contact ICTU';

        $approvalModel = new DigitalSignature();
        $approvalModel->lvlOfSignature = 'APPROVED';
        $approvalModel->emp_id = isset($approver[0]) ? $approver[0] : '';
        $approvalModel->designation = isset($approver[0]) ? $approverLastName->lname == 'Ubungen' ? 'OIC-Regional Director' : 'OIC-Assistant Regional Director' : '';

        if($approvalModel->load(Yii::$app->request->post())){

            $model->isDirector_Approved = 1;
            $model->remarks = '';
            $model->save(false);

            $approvalModel->to_no = $model->TO_NO;
            $approvalModel->date_approved = date("Y-m-d H:i:s");
            $approvalModel->save();

            
            /* $user = UserInfo::findOne(['EMP_N' => $model->TO_creator]);

            if($user){
                $mailer = Yii::$app->mailer->compose([
                    'html' => 'travel-order-approve-html'
                ],[
                    'model' => $model,
                ]);
                $mailer->setFrom('mvespiritu@neda.gov.ph');
                $mailer->setTo($user->user->email);
                $mailer->setSubject('Travel Order No. '.$model->TO_NO.' is now approved');
                $mailer->send();
                
            } */

            \Yii::$app->getSession()->setFlash('success', 'Travel order is now approved.');
            return $this->redirect(['view', 'id' => $model->TO_NO]);
        }

        return $this->renderAjax('_approve', [
            'model' => $model,
            'approvalModel' => $approvalModel,
            'approver' => $approver,
            'approverName' => $approverName,
        ]);
    }

    public function actionDisapprove($id)
    {
        $model = $this->findModel($id);

        $concernStaffs = ConcernStaff::find()->select([
            'recommendingApprover.emp_id as recommender',
            'finalApprover.emp_id as approver',
        ])
        ->leftJoin('tbltev_authapprover', 'tbltev_authapprover.emp_id = tbltev_concernedstaff.emp_id')
        ->leftJoin('tblemployee recommendingApprover', 'recommendingApprover.emp_id = tbltev_authapprover.recommending')
        ->leftJoin('tblemployee finalApprover', 'finalApprover.emp_id = tbltev_authapprover.final')
        ->where(['tbltev_concernedstaff.TO_NO' => $model->TO_NO])
        ->asArray()
        ->all();

        $recommenders = ArrayHelper::map($concernStaffs, 'recommenderWithDivision', 'recommenderWithDivision');
        $recommenders = array_filter($recommenders, function($value) {
            return $value !== "";
        });

        $approvers = ArrayHelper::map($concernStaffs, 'approver', 'approver');
        $approvers = array_filter($approvers, function($value) {
            return $value !== "";
        });

        $approver = array_values(array_diff_key($approvers, $recommenders));

        $approverName = isset($approver[0]) ? Employee::findOne(['emp_id' => $approver[0]]) : [];
        $approverLastName = isset($approver[0]) ? Employee::findOne(['emp_id' => $approver[0]]) : [];
        $approverName = !empty($approverName) ? $approverName->mname != '' ? $approverName->fname.' '.substr($approverName->mname, 0, 1).'. '.$approverName->lname : $approverName->fname.' '.$approverName->lname : 'No set approver. Contact ICTU';

        $approvalModel = new DigitalSignature();
        $approvalModel->lvlOfSignature = 'APPROVED';
        $approvalModel->emp_id = isset($approver[0]) ? $approver[0] : '';
        $approvalModel->designation = isset($approver[0]) ? $approverLastName->lname == 'Ubungen' ? 'OIC-Regional Director' : 'OIC-Assistant Regional Director' : '';

        if($approvalModel->load(Yii::$app->request->post())){

            $model->isDirector_Approved = 0;
            $model->remarks = '';
            $model->save(false);

            $approvalModel->to_no = $model->TO_NO;
            $approvalModel->date_disapproved = date("Y-m-d H:i:s");
            $approvalModel->save();

            /* $user = UserInfo::findOne(['EMP_N' => $model->TO_creator]);

            if($user){
                $mailer = Yii::$app->mailer->compose([
                    'html' => 'travel-order-disapprove-html'
                ],[
                    'model' => $model,
                ]);
                $mailer->setFrom('mvespiritu@neda.gov.ph');
                $mailer->setTo($user->user->email);
                $mailer->setSubject('Travel Order No. '.$model->TO_NO.' is disapproved');
                $mailer->send();
                
            } */

            \Yii::$app->getSession()->setFlash('success', 'Travel order is now disapproved.');
            return $this->redirect(['view', 'id' => $model->TO_NO]);
        }

        return $this->renderAjax('_disapprove', [
            'model' => $model,
            'approvalModel' => $approvalModel,
            'approver' => $approver,
            'approverName' => $approverName,
        ]);
    }

    public function actionForRevision($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'revise';

        if($model->load(Yii::$app->request->post())){

            $model->isDirector_Approved = '';
            $model->save(false);

            \Yii::$app->getSession()->setFlash('success', 'Travel order is now open for editing.');
            return $this->redirect(['view', 'id' => $model->TO_NO]);
        }

        return $this->renderAjax('_for-revision', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the TravelOrder model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return TravelOrder the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TravelOrder::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
