<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_sub_activity".
 *
 * @property int $id
 * @property int|null $pap_id
 * @property int|null $activity_id
 * @property string|null $code
 * @property string|null $title
 * @property string|null $description
 *
 * @property PpmpActivity $activity
 */
class SubActivity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_sub_activity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_id', 'code', 'title'], 'required'],
            [['activity_id'], 'integer'],
            [['title', 'description'], 'string'],
            [['code'], 'string', 'max' => 10],
            [['activity_id'], 'exist', 'skipOnError' => true, 'targetClass' => Activity::className(), 'targetAttribute' => ['activity_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'activity_id' => 'Activity',
            'activityTitle' => 'Activity',
            'code' => 'Code',
            'title' => 'Title',
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[Activity]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActivity()
    {
        return $this->hasOne(Activity::className(), ['id' => 'activity_id']);
    }

    public function getActivityTitle()
    {
        return $this->activity ? $this->activity->codeAndTitle : '';
    }

    public function getSubActivityTitle()
    {
        $subactivity = SubActivity::find()
                    ->select([
                        'IF(ppmp_pap.short_code IS NULL,
                            concat(
                                ppmp_cost_structure.code,"",
                                ppmp_organizational_outcome.code,"",
                                ppmp_program.code,"",
                                ppmp_sub_program.code,"",
                                ppmp_identifier.code,"",
                                ppmp_pap.code,"000-",
                                ppmp_activity.code,"-",
                                ppmp_sub_activity.code," - ",
                                ppmp_sub_activity.title
                            )
                            ,
                            concat(
                                ppmp_pap.short_code,"-",
                                ppmp_activity.code,"-",
                                ppmp_sub_activity.code," - ",
                                ppmp_sub_activity.title
                            )
                        ) as title'
                    ])
                    ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_sub_activity.activity_id')
                    ->leftJoin('ppmp_pap', 'ppmp_pap.id = ppmp_activity.pap_id')
                    ->leftJoin('ppmp_identifier', 'ppmp_identifier.id = ppmp_pap.identifier_id')
                    ->leftJoin('ppmp_sub_program', 'ppmp_sub_program.id = ppmp_pap.sub_program_id')
                    ->leftJoin('ppmp_program', 'ppmp_program.id = ppmp_pap.program_id')
                    ->leftJoin('ppmp_organizational_outcome', 'ppmp_organizational_outcome.id = ppmp_pap.organizational_outcome_id')
                    ->leftJoin('ppmp_cost_structure', 'ppmp_cost_structure.id = ppmp_pap.cost_structure_id')
                    ->andWhere(['ppmp_sub_activity.id' => $this->id])
                    ->asArray()
                    ->one();

        return !empty($subactivity) ? $subactivity['title'] : 'No title';
    }
}
