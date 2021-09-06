<?php

namespace common\modules\v1\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\v1\models\Identifier;

/**
 * IdentifierSearch represents the model behind the search form of `common\modules\v1\models\Identifier`.
 */
class IdentifierSearch extends Identifier
{
    public $codeTitle;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'cost_structure_id', 'organizational_outcome_id', 'program_id', 'sub_program_id'], 'integer'],
            [['code', 'title', 'description', 'codeTitle'], 'safe'],
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
        $query = Identifier::find()
        ->joinWith('costStructure')
        ->joinWith('organizationalOutcome')
        ->joinWith('program')
        ->joinWith('subProgram');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'codeTitle' => [
                    'asc' => ['concat(ppmp_cost_structure.code,"",ppmp_organizational_outcome.code,"",ppmp_program.code,"",ppmp_sub_program.code,"",ppmp_identifier.code)' => SORT_ASC],
                    'desc' => ['concat(ppmp_cost_structure.code,"",ppmp_organizational_outcome.code,"",ppmp_program.code,"",ppmp_sub_program.code,"",ppmp_identifier.code)' => SORT_DESC],
                ],
                'title',
                'description',
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
            'cost_structure_id' => $this->cost_structure_id,
            'organizational_outcome_id' => $this->organizational_outcome_id,
            'program_id' => $this->program_id,
            'sub_program_id' => $this->sub_program_id,
        ]);

        $query->andFilterWhere(['like', 'concat(ppmp_cost_structure.code,"",ppmp_organizational_outcome.code,"",ppmp_program.code,"",ppmp_sub_program.code,"",ppmp_identifier.code)', $this->codeTitle])
            ->andFilterWhere(['like', 'ppmp_identifier.title', $this->title])
            ->andFilterWhere(['like', 'ppmp_identifier.description', $this->description]);

        return $dataProvider;
    }
}
