<?php

namespace common\modules\v1\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\v1\models\Ris;

/**
 * RisSearch represents the model behind the search form of `common\modules\v1\models\Ris`.
 */
class RisSearch extends Ris
{
    public $officeName;
    public $creatorName;
    public $requesterName;
    public $statusName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'ppmp_id', 'fund_source_id', 'fund_cluster_id', 'created_by', 'requested_by', 'approved_by', 'issued_by', 'received_by'], 'integer'],
            [['type', 'office_id', 'section_id', 'unit_id', 'ris_no', 'purpose', 'date_required', 'date_created', 'date_requested', 'date_approved', 'date_issued', 'date_received', 'creatorName', 'requesterName', 'statusName'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Yii::$app->user->can('Administrator') || Yii::$app->user->can('ProcurementStaff') ? 
            Ris::find()
            ->joinWith('creator c')
            ->joinWith('requester r')
            ->joinWith('office')
            ->joinWith('fundSource')
            ->joinWith('status')
             ->orderBy(['id' => SORT_DESC]) :
            Ris::find()
            ->joinWith('creator c')
            ->joinWith('requester r')
            ->joinWith('office')
            ->joinWith('fundSource')
            ->joinWith('status')
            ->andWhere(['r.office_id' => Yii::$app->user->identity->userinfo->office->id])
            ->orderBy(['id' => SORT_DESC]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'type',
                'ris_no',
                'officeName' => [
                    'asc' => ['tbloffice.abbreviation' => SORT_ASC],
                    'desc' => ['tbloffice.abbreviation' => SORT_DESC],
                ],
                'fundSourceName' => [
                    'asc' => ['ppmp_fund_source.code' => SORT_ASC],
                    'desc' => ['ppmp_fund_source.code' => SORT_DESC],
                ],
                'purpose',
                'date_required',
                'creatorName' => [
                    'asc' => ['concat(c.FIRST_M," ",c.LAST_M)' => SORT_ASC],
                    'desc' => ['concat(c.FIRST_M," ",c.LAST_M)' => SORT_DESC],
                ],
                'date_created',
                'requesterName' => [
                    'asc' => ['concat(r.name)' => SORT_ASC],
                    'desc' => ['concat(r.name)' => SORT_DESC],
                ],
                'statusName' => [
                    'asc' => ['ppmp_transaction.status' => SORT_ASC],
                    'desc' => ['ppmp_transaction.status' => SORT_DESC],
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'ppmp_id' => $this->ppmp_id,
            'ppmp_ris.office_id' => $this->office_id,
            'section_id' => $this->section_id,
            'unit_id' => $this->unit_id,
            'fund_source_id' => $this->fund_source_id,
            'fund_cluster_id' => $this->fund_cluster_id,
            'date_required' => $this->date_required,
            'date_created' => $this->date_created,
            'date_requested' => $this->date_requested,
            'date_approved' => $this->date_approved,
            'date_issued' => $this->date_issued,
            'date_received' => $this->date_received,
            'created_by' => $this->created_by,
            'requested_by' => $this->requested_by,
            'approved_by' => $this->approved_by,
            'issued_by' => $this->issued_by,
            'received_by' => $this->received_by,
            'ppmp_transaction.status' => $this->statusName,
            'type' => $this->type,
        ]);

        $query->andFilterWhere(['like', 'ris_no', $this->ris_no])
            ->andFilterWhere(['like', 'purpose', $this->purpose])
            ->andFilterWhere(['like', 'concat(c.FIRST_M," ",c.LAST_M)', $this->creatorName])
            ->andFilterWhere(['like', 'concat(r.name)', $this->requesterName])
            ;

        if(isset($params['RisSearch']['statusName']) && $params['RisSearch']['statusName'] != '')
        {
            $ids = [];
            $transactions = Transaction::find()
                            ->innerJoin(['statuses' => '(
                            select
                                ppmp_transaction.id,
                                ppmp_transaction.model_id,
                                ppmp_transaction.status
                            from ppmp_transaction
                            inner join
                            (select max(id) as id from ppmp_transaction where model = "Ris" group by model_id) latest on latest.id = ppmp_transaction.id
                            )'], 'statuses.id = ppmp_transaction.id')
                            ->leftJoin('ppmp_ris', 'ppmp_ris.id = statuses.model_id')
                            ->andWhere(['statuses.status' => $params['RisSearch']['statusName']])
                            ->asArray()
                            ->all();

            if(!empty($transactions))
            {
                foreach($transactions as $transaction)
                {
                    $ids[] = $transaction['model_id'];
                }

            }

            if(!empty($ids))
            {
                $query->andWhere(['ppmp_ris.id' => $ids]); 
            }else{
                $query;
            }
        }

        return $dataProvider;
    }
}
