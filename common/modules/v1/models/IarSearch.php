<?php

namespace common\modules\v1\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\v1\models\Iar;

/**
 * IarSearch represents the model behind the search form of `common\modules\v1\models\Iar`.
 */
class IarSearch extends Iar
{
    public $prNo;
    public $poNo;
    public $inspectorName;
    public $receiverName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'pr_id', 'po_id', 'inspected_by', 'received_by'], 'integer'],
            [['iar_no', 'iar_date', 'invoice_no', 'invoice_date', 'date_inspected', 'date_received', 'prNo', 'poNo', 'inspectorName', 'receiverName'], 'safe'],
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
        $query = Iar::find()
                ->joinWith('pr')
                ->joinWith('po')
                ->joinWith('inspector i')
                ->joinWith('receiver r');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'iar_no',
                'iar_date',
                'prNo' => [
                    'asc' => ['ppmp_pr.pr_no' => SORT_ASC],
                    'desc' => ['ppmp_pr.pr_no' => SORT_DESC],
                ],
                'poNo' => [
                    'asc' => ['ppmp_po.po_no' => SORT_ASC],
                    'desc' => ['ppmp_po.po_no' => SORT_DESC],
                ],
                'invoice_no',
                'invoice_date',
                'inspectorName' => [
                    'asc' => ['i.name' => SORT_ASC],
                    'desc' => ['i.name' => SORT_DESC],
                ],
                'date_inspected',
                'receiverName' => [
                    'asc' => ['concat(r.name)' => SORT_ASC],
                    'desc' => ['concat(r.name)' => SORT_DESC],
                ],
                'date_received',
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
            'pr_id' => $this->pr_id,
            'po_id' => $this->po_id,
            'iar_date' => $this->iar_date,
            'invoice_date' => $this->invoice_date,
            'inspected_by' => $this->inspected_by,
            'date_inspected' => $this->date_inspected,
            'received_by' => $this->received_by,
            'date_received' => $this->date_received,
        ]);

        $query->andFilterWhere(['like', 'iar_no', $this->iar_no])
            ->andFilterWhere(['like', 'ppmp_pr.pr_no', $this->prNo])
            ->andFilterWhere(['like', 'ppmp_po.po_no', $this->poNo])
            ->andFilterWhere(['like', 'invoice_no', $this->invoice_no])
            ->andFilterWhere(['like', 'i.name', $this->inspectorName])
            ->andFilterWhere(['like', 'r.name', $this->receiverName])
            ;

        return $dataProvider;
    }
}
