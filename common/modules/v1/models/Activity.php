<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_activity".
 *
 * @property int $id
 * @property int|null $pap_id
 * @property string|null $code
 * @property string|null $title
 * @property string|null $description
 *
 * @property PpmpPap $pap
 * @property PpmpSubactivity[] $ppmpSubactivities
 */
class Activity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_activity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pap_id', 'code', 'title'], 'required'],
            [['pap_id'], 'integer'],
            [['title', 'description'], 'string'],
            [['code'], 'string', 'max' => 10],
            [['pap_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pap::className(), 'targetAttribute' => ['pap_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pap_id' => 'PAP',
            'papTitle' => 'PAP',
            'code' => 'Code',
            'title' => 'Title',
            'description' => 'Description',
        ];
    }

    public function getCodeAndTitle()
    {
        return $this->pap ? $this->pap->codeTitle.'-'.$this->code.' - '.$this->title : '';
    }

    /**
     * Gets query for [[Pap]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPap()
    {
        return $this->hasOne(Pap::className(), ['id' => 'pap_id']);
    }

    public function getPapTitle()
    {
        return $this->pap? $this->pap->codeAndTitle : '';
    }

    /**
     * Gets query for [[PpmpSubactivities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubactivities()
    {
        return $this->hasMany(Subactivity::className(), ['activity_id' => 'id']);
    }

    /**
     * Gets query for [[PpmpSubactivities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPpas()
    {
        return $this->hasMany(Ppa::className(), ['activity_id' => 'id']);
    }

    public function getActivityTitle()
    {
        $activity = Activity::find()
                    ->select([
                        'IF(ppmp_pap.short_code IS NULL,
                            concat(
                                ppmp_cost_structure.code,"",
                                ppmp_organizational_outcome.code,"",
                                ppmp_program.code,"",
                                ppmp_sub_program.code,"",
                                ppmp_identifier.code,"",
                                ppmp_pap.code,"000-",
                                ppmp_activity.code," - ",
                                ppmp_activity.title
                            )
                            ,
                            concat(
                                ppmp_pap.short_code,"-",
                                ppmp_activity.code," - ",
                                ppmp_activity.title
                            )
                        ) as title'
                    ])
                    ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                    ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                    ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                    ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                    ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                    ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                    ->andWhere(['ppmp_activity.id' => $this->id])
                    ->asArray()
                    ->one();

        return !empty($activity) ? $activity['title'] : 'No title';
    }
}
