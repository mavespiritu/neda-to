<?php

namespace common\modules\v1\controllers;

use Yii;
use common\modules\v1\models\AuthApprover;
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
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * TravelOrderController implements the CRUD actions for TravelOrder model.
 */
class SignatoryController extends Controller
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
                        'roles' => ['Administrator'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $data = [];

        $staffs = AuthApprover::find()->select([
            'staff.emp_id as emp_id',
            'staff.division_id as division',
            'IF(staff.mname <> "", concat(staff.fname," ",LEFT(staff.mname, 1),". ",staff.lname), concat(staff.fname," ",staff.lname)) as staff',
            'IF(recommendingApprover.mname <> "", concat(recommendingApprover.fname," ",LEFT(recommendingApprover.mname, 1),". ",recommendingApprover.lname), concat(recommendingApprover.fname," ",recommendingApprover.lname)) as recommender',
            'IF(finalApprover.mname <> "", concat(finalApprover.fname," ",LEFT(finalApprover.mname, 1),". ",finalApprover.lname), concat(finalApprover.fname," ",finalApprover.lname)) as approver',
        ])
        ->leftJoin('tblemployee staff', 'staff.emp_id = tbltev_authapprover.emp_id')
        ->leftJoin('tblemployee recommendingApprover', 'recommendingApprover.emp_id = tbltev_authapprover.recommending')
        ->leftJoin('tblemployee finalApprover', 'finalApprover.emp_id = tbltev_authapprover.final')
        ->where(['staff.work_status' => 'Active'])
        ->orderBy(['staff.division_id' => SORT_ASC, 'staff' => SORT_ASC])
        ->asArray()
        ->all();

        if(!empty($staffs)){
            foreach($staffs as $staff){
                $data[$staff['division']][] = $staff;
            }
        }

        return $this->render('index',[
            'data' => $data
        ]);
    }

    public function actionAssign($id)
    {
        $model = AuthApprover::findOne(['emp_id' => $id]);

        $staffs = Employee::find()
                ->select([
                    'emp_id',
                    'IF(tblemployee.mname <> "", concat(tblemployee.fname," ",LEFT(tblemployee.mname, 1),". ",tblemployee.lname), concat(tblemployee.fname," ",tblemployee.lname)) as staff'])
                ->where(['work_status' => 'Active'])
                ->asArray()
                ->all();

        $staffs = ArrayHelper::map($staffs, 'emp_id', 'staff');

        if($model->load(Yii::$app->request->post())){

            $model->save();

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->renderAjax('_form',[
            'model' => $model,
            'staffs' => $staffs
        ]);
    }
}
