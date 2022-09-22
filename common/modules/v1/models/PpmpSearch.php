<?php

namespace common\modules\v1\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\v1\models\Ppmp;

/**
 * PpmpSearch represents the model behind the search form of `common\modules\v1\models\Ppmp`.
 */
class PpmpSearch extends Ppmp
{
    public $officeName;
    public $creatorName;
    public $statusName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'office_id', 'year', 'created_by', 'updated_by'], 'integer'],
            [['stage', 'date_created', 'date_updated', 'officeName', 'creatorName', 'updaterName', 'statusName'], 'safe'],
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
        $query = Yii::$app->user->can('Administrator') ? Ppmp::find()
                ->joinWith('creator c')
                ->joinWith('office')
                ->orderBy(['year' => SORT_DESC])
                 : Ppmp::find()
                ->joinWith('creator c')
                ->joinWith('office')
                ->andWhere(['office_id' => Yii::$app->user->identity->userinfo->OFFICE_C])
                ->orderBy(['year' => SORT_DESC])
                ;

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'officeName' => [
                    'asc' => ['tbloffice.abbreviation' => SORT_ASC],
                    'desc' => ['tbloffice.abbreviation' => SORT_DESC],
                ],
                'year',
                'stage',
                'creatorName' => [
                    'asc' => ['concat(c.FIRST_M," ",c.LAST_M)' => SORT_ASC],
                    'desc' => ['concat(c.FIRST_M," ",c.LAST_M)' => SORT_DESC],
                ],
                'date_created',
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
            'year' => $this->year,
            'office_id' => $this->office_id,
            'stage' => $this->stage,
        ]);

        $query->andFilterWhere(['like', 'concat(c.FIRST_M," ",c.LAST_M)', $this->creatorName]);

        if(isset($params['PpmpSearch']['statusName']) && $params['PpmpSearch']['statusName'] != '')
        {
            $ids = [];
            $transactions = Ppmp::find()
                            ->innerJoin(['statuses' => '(
                            select
                                ppmp_transaction.id,
                                ppmp_transaction.model_id,
                                ppmp_transaction.status
                            from ppmp_transaction
                            inner join
                            (select max(id) as id from ppmp_transaction where model = "Ppmp" group by model_id) latest on latest.id = ppmp_transaction.id
                            )'], 'statuses.model_id = ppmp_ppmp.id')
                            ->andWhere(['statuses.status' => $params['PpmpSearch']['statusName']])
                            ->asArray()
                            ->all();

            if(!empty($transactions))
            {
                foreach($transactions as $transaction)
                {
                    $ids[] = $transaction['id'];
                }

            }

            $query->andWhere(['ppmp_ppmp.id' => $ids]); 
        }

        return $dataProvider;
    }
}
