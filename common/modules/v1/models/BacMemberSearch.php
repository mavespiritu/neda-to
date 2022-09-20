<?php

namespace common\modules\v1\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\v1\models\BacMember;

/**
 * BacMemberSearch represents the model behind the search form of `common\modules\v1\models\BacMember`.
 */
class BacMemberSearch extends BacMember
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['emp_id', 'office_id', 'bac_group', 'expertise'], 'safe'],
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
        $query = BacMember::find();

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
        $query->andFilterWhere(['like', 'emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'office_id', $this->office_id])
            ->andFilterWhere(['like', 'bac_group', $this->bac_group])
            ->andFilterWhere(['like', 'expertise', $this->expertise]);

        return $dataProvider;
    }
}
