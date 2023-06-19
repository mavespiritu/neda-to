<?php

namespace common\modules\v1\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\v1\models\TravelOrder;

/**
 * TravelOrderSearch represents the model behind the search form of `common\modules\v1\models\TravelOrder`.
 */
class TravelOrderSearch extends TravelOrder
{
    public $creatorName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['TO_NO', 'date_filed', 'TO_creator', 'TO_subject', 'date_from', 'date_to', 'isDirector_Approved', 'otherpassenger', 'othervehicle', 'otherdriver', 'creatorName'], 'safe'],
            [['withVehicle', 'type_of_travel'], 'integer'],
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
        $query = TravelOrder::find()
                ->joinWith('creator')
                ->orderBy(['TO_NO' => SORT_DESC]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'withVehicle' => $this->withVehicle,
            'type_of_travel' => $this->type_of_travel
        ]);

        $query->andFilterWhere(['like', 'TO_NO', $this->TO_NO])
            ->andFilterWhere(['like', 'date_filed', $this->date_filed])
            ->andFilterWhere(['like', 'concat(tblemployee.fname," ",tblemployee.lname)', $this->creatorName])
            ->andFilterWhere(['like', 'TO_subject', $this->TO_subject])
            ->andFilterWhere(['like', 'date_from', $this->date_from])
            ->andFilterWhere(['like', 'date_to', $this->date_to])
            ->andFilterWhere(['like', 'isDirector_Approved', $this->isDirector_Approved])
            ->andFilterWhere(['like', 'otherpassenger', $this->otherpassenger])
            ->andFilterWhere(['like', 'othervehicle', $this->othervehicle])
            ->andFilterWhere(['like', 'otherdriver', $this->otherdriver]);

        return $dataProvider;
    }
}
