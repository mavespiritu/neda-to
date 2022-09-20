<?php

namespace common\modules\v1\controllers;

use Yii;
use common\modules\v1\models\Signatory;
use common\modules\v1\models\Settings;
use common\modules\v1\models\BacMember;
use common\modules\v1\models\BacMemberSearch;
use markavespiritu\user\models\Office;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * BacMemberController implements the CRUD actions for BacMember model.
 */
class BacMemberController extends Controller
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
                'only' => ['index',  'bac', 'expert', 'end-user'],
                'rules' => [
                    [
                        'actions' => ['index', 'bac', 'expert', 'end-user'],
                        'allow' => true,
                        'roles' => ['ProcurementStaff', 'Administrator'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all BacMember models.
     * @return mixed
     */
    public function actionIndex()
    {
        $chair = Settings::findOne(['title' => 'BAC Chairperson']) ? Settings::findOne(['title' => 'BAC Chairperson']) : new Settings();
        $chair->title = 'BAC Chairperson';

        $chairName = !($chair->isNewRecord) ? Signatory::findOne(['emp_id' => $chair->value]) : '';

        $viceChair = Settings::findOne(['title' => 'BAC Vice-Chairperson']) ? Settings::findOne(['title' => 'BAC Vice-Chairperson']) : new Settings();
        $viceChair->title = 'BAC Vice-Chairperson';

        $viceChairName = !($viceChair->isNewRecord) ? Signatory::findOne(['emp_id' => $viceChair->value]) : '';

        $member = Settings::findOne(['title' => 'BAC Member']) ? Settings::findOne(['title' => 'BAC Member']) : new Settings();
        $member->title = 'BAC Member';

        $memberName = !($member->isNewRecord) ? Signatory::findOne(['emp_id' => $member->value]) : '';

        $expertises = [
            'Goods' => [
                'Supplies and Materials',
                'Automotive',
                'ICT',
                'Others (catering)',
            ],
            'Infrastructure' => 'Infrastructure',
            'Consultancy' => 'Consultancy'
        ];

        $expertMembers = [];
        $divisionMembers = [];

        if(!empty($expertises))
        {
            foreach($expertises as $expertise => $subExpertises)
            {
                if(gettype($subExpertises) == 'array')
                {
                    foreach($subExpertises as $subExpertise)
                    {
                        $bacMember = BacMember::findOne(['expertise' => $expertise, 'sub_expertise' => $subExpertise, 'bac_group' => 'Technical Expert']);
                        $bacMemberName = $bacMember ? Signatory::findOne(['emp_id' => $bacMember->emp_id]) : '';
                        $expertMembers[$expertise][$subExpertise] = $bacMemberName;
                    }
                }else{
                    $bacMember = BacMember::findOne(['expertise' => $expertise, 'bac_group' => 'Technical Expert']);
                    $bacMemberName = $bacMember ? Signatory::findOne(['emp_id' => $bacMember->emp_id]) : '';
                    $expertMembers[$expertise] = $bacMemberName;
                }
            }
        }

        $divisions = Office::find()->all();
        $divisions = ArrayHelper::map($divisions, 'abbreviation', 'abbreviation');

        if(!empty($divisions))
        {
            foreach($divisions as $division)
            {
                $bacMember = BacMember::findOne(['office_id' => $division, 'bac_group' => 'End User']);
                $bacMemberName = $bacMember ? Signatory::findOne(['emp_id' => $bacMember->emp_id]) : '';
                $divisionMembers[$division] = $bacMemberName;
            }
        }

        return $this->render('index', [
            'chair' => $chair,
            'chairName' => $chairName,
            'viceChair' => $viceChair,
            'viceChairName' => $viceChairName,
            'member' => $member,
            'memberName' => $memberName,
            'divisions' => $divisions,
            'expertMembers' => $expertMembers,
            'divisionMembers' => $divisionMembers,
        ]);
    }

    public function actionBac($title)
    {
        $member = Settings::findOne(['title' => $title]) ? Settings::findOne(['title' => $title]) : new Settings();
        $member->title = $title;

        $signatories = Signatory::find()->all();
        $signatories = ArrayHelper::map($signatories, 'emp_id', 'name');

        if($member->load(Yii::$app->request->post()))
        {
            $member->save();
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('_bac', [
            'member' => $member,
            'title' => $title,
            'signatories' => $signatories,
        ]);
    }

    public function actionExpert($expertise, $sub_expertise)
    {
        if($sub_expertise != '')
        {
            $member = BacMember::findOne(['bac_group' => 'Technical Expert', 'expertise' => $expertise, 'sub_expertise' => $sub_expertise]) ? BacMember::findOne(['bac_group' => 'Technical Expert', 'expertise' => $expertise, 'sub_expertise' => $sub_expertise]) : new BacMember();
        }else{
            $member = BacMember::findOne(['bac_group' => 'Technical Expert', 'expertise' => $expertise]) ? BacMember::findOne(['bac_group' => 'Technical Expert', 'expertise' => $expertise]) : new BacMember();
        }

        $member->bac_group = 'Technical Expert';
        $member->expertise = $expertise;
        $member->sub_expertise = $sub_expertise;

        $signatories = Signatory::find()->all();
        $signatories = ArrayHelper::map($signatories, 'emp_id', 'name');

        if($member->load(Yii::$app->request->post()))
        {
            $member->save(false);
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('_expert', [
            'member' => $member,
            'expertise' => $expertise,
            'sub_expertise' => $sub_expertise,
            'signatories' => $signatories,
        ]);
    }

    public function actionEndUser($office_id)
    {
        $member = BacMember::findOne(['bac_group' => 'End User', 'office_id' => $office_id]) ? BacMember::findOne(['bac_group' => 'End User', 'office_id' => $office_id]) : new BacMember();

        $member->bac_group = 'End User';
        $member->office_id = $office_id;

        $signatories = Signatory::find()->all();
        $signatories = ArrayHelper::map($signatories, 'emp_id', 'name');

        if($member->load(Yii::$app->request->post()))
        {
            $member->save(false);
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('_end-user', [
            'member' => $member,
            'office_id' => $office_id,
            'signatories' => $signatories,
        ]);
    }

    /**
     * Finds the BacMember model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return BacMember the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BacMember::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
