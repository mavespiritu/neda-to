<?php

namespace common\modules\v1\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\v1\models\Pr;

/**
 * PrSearch represents the model behind the search form of `common\modules\v1\models\Pr`.
 */
class PrSearch extends Pr
{
    public $officeName;
    public $procurementModeName;
    public $creatorName;
    public $requesterName;
    public $statusName;
    public $risNo;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'fund_source_id', 'fund_cluster_id'], 'integer'],
            [['pr_no', 'office_id', 'section_id', 'unit_id', 'purpose', 'requested_by', 'date_requested', 'approved_by', 'date_approved', 'type', 'officeName', 'procurementModeName', 'creatorName', 'requesterName', 'statusName', 'risNo', 'year'], 'safe'],
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
            Pr::find()
            ->joinWith('creator c')
            ->joinWith('requester r')
            ->joinWith('office')
            ->joinWith('fundSource')
             ->orderBy(['pr_no' => SORT_DESC]) :
            Pr::find()
            ->joinWith('creator c')
            ->joinWith('requester r')
            ->joinWith('office')
            ->joinWith('fundSource')
            ->andWhere(['ppmp_pr.office_id' => Yii::$app->user->identity->userinfo->office->abbreviation])
            ->orderBy(['pr_no' => SORT_DESC]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'type',
                'pr_no',
                'procurementModeName' => [
                    'asc' => ['ppmp_procurement_mode.title' => SORT_ASC],
                    'desc' => ['ppmp_procurement_mode.title' => SORT_DESC],
                ],
                'officeName' => [
                    'asc' => ['tbloffice.abbreviation' => SORT_ASC],
                    'desc' => ['tbloffice.abbreviation' => SORT_DESC],
                ],
                'fundSourceName' => [
                    'asc' => ['ppmp_fund_source.code' => SORT_ASC],
                    'desc' => ['ppmp_fund_source.code' => SORT_DESC],
                ],
                'purpose',
                'date_requested',
                'creatorName' => [
                    'asc' => ['concat(c.FIRST_M," ",c.LAST_M)' => SORT_ASC],
                    'desc' => ['concat(c.FIRST_M," ",c.LAST_M)' => SORT_DESC],
                ],
                'date_created',
                'requesterName' => [
                    'asc' => ['concat(r.name)' => SORT_ASC],
                    'desc' => ['concat(r.name)' => SORT_DESC],
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
            'ppmp_pr.office_id' => $this->office_id,
            'ppmp_pr.section_id' => $this->section_id,
            'ppmp_pr.unit_id' => $this->unit_id,
            'fund_source_id' => $this->fund_source_id,
            'fund_cluster_id' => $this->fund_cluster_id,
            'date_requested' => $this->date_requested,
            'date_approved' => $this->date_approved,
            'requested_by' => $this->requested_by,
            'approved_by' => $this->approved_by,
            'type' => $this->type,
            'ppmp_pr.year' => $this->year,
        ]);

        $query->andFilterWhere(['like', 'pr_no', $this->pr_no])
            ->andFilterWhere(['like', 'purpose', $this->purpose])
            ->andFilterWhere(['like', 'concat(c.FIRST_M," ",c.LAST_M)', $this->creatorName])
            ->andFilterWhere(['like', 'concat(r.name)', $this->requesterName])
            ;

        if(isset($params['PrSearch']['statusName']) && $params['PrSearch']['statusName'] != '')
        {
            $ids = [];
            $transactions = Pr::find()
                            ->innerJoin(['statuses' => '(
                            select
                                ppmp_transaction.id,
                                ppmp_transaction.model_id,
                                ppmp_transaction.status
                            from ppmp_transaction
                            inner join
                            (select max(id) as id from ppmp_transaction where model = "Pr" group by model_id) latest on latest.id = ppmp_transaction.id
                            )'], 'statuses.model_id = ppmp_pr.id')
                            ->andWhere(['statuses.status' => $params['PrSearch']['statusName']])
                            ->asArray()
                            ->all();

            if(!empty($transactions))
            {
                foreach($transactions as $transaction)
                {
                    $ids[] = $transaction['id'];
                }

            }

            $query->andWhere(['ppmp_pr.id' => $ids]); 
        }

        if(isset($params['PrSearch']['risNo']) && $params['PrSearch']['risNo'] != '')
        {
            $ids = [];
            $transactions = PrItem::find()
                            ->select(['ppmp_pr_item.pr_id'])
                            ->leftJoin('ppmp_pr', 'ppmp_pr.id = ppmp_pr_item.pr_id')
                            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                            ->andWhere(['ppmp_ris.ris_no' => $params['PrSearch']['risNo']])
                            ->asArray()
                            ->all();

            if(!empty($transactions))
            {
                foreach($transactions as $transaction)
                {
                    $ids[] = $transaction['pr_id'];
                }

            }

            $query->andWhere(['ppmp_pr.id' => $ids]); 
        }

        return $dataProvider;
    }
}
