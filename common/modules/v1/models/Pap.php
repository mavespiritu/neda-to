<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_pap".
 *
 * @property int $id
 * @property int|null $cost_structure_id
 * @property int|null $organizational_outcome_id
 * @property int|null $program_id
 * @property int|null $sub_program_id
 * @property int|null $identifier_id
 * @property string|null $code
 * @property string|null $title
 * @property string|null $description
 *
 * @property PpmpIdentifier $identifier
 */
class Pap extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_pap';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cost_structure_id', 'organizational_outcome_id', 'program_id', 'sub_program_id', 'identifier_id', 'code', 'title'], 'required'],
            [['cost_structure_id', 'organizational_outcome_id', 'program_id', 'sub_program_id', 'identifier_id'], 'integer'],
            [['title', 'description'], 'string'],
            [['code', 'short_code'], 'string', 'max' => 10],
            [['identifier_id'], 'exist', 'skipOnError' => true, 'targetClass' => Identifier::className(), 'targetAttribute' => ['identifier_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cost_structure_id' => 'Cost Structure',
            'organizational_outcome_id' => 'Organizational Outcome',
            'program_id' => 'Program',
            'sub_program_id' => 'Sub Program',
            'identifier_id' => 'Identifier',
            'short_code' => 'Short Code',
            'code' => 'Code',
            'codeTitle' => 'Code',
            'title' => 'Title',
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[OrganizationalOutcome]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCostStructure()
    {
        return $this->hasOne(CostStructure::className(), ['id' => 'cost_structure_id']);
    }

    public function getCostStructureTitle()
    {
        return $this->costStructure ? $this->costStructure->code.' - '.$this->costStructure->title : '';
    }

    /**
     * Gets query for [[OrganizationalOutcome]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrganizationalOutcome()
    {
        return $this->hasOne(OrganizationalOutcome::className(), ['id' => 'organizational_outcome_id']);
    }

    public function getOrganizationalOutcomeTitle()
    {
        return $this->organizationalOutcome ? $this->organizationalOutcome->code.' - '.$this->organizationalOutcome->title : '';
    }

    /**
     * Gets query for [[Program]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProgram()
    {
        return $this->hasOne(Program::className(), ['id' => 'program_id']);
    }

    public function getProgramTitle()
    {
        return $this->program ? $this->program->code.' - '.$this->program->title : '';
    }

    /**
     * Gets query for [[SubProgram]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubProgram()
    {
        return $this->hasOne(SubProgram::className(), ['id' => 'sub_program_id']);
    }

    public function getSubProgramTitle()
    {
        return $this->subProgram ? $this->subProgram->code.' - '.$this->subProgram->title : '';
    }

    /**
     * Gets query for [[SubProgram]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdentifier()
    {
        return $this->hasOne(Identifier::className(), ['id' => 'identifier_id']);
    }

    public function getIdentifierTitle()
    {
        return $this->identifier ? $this->identifier->code.' - '.$this->identifier->title : '';
    }

    public function getCodeTitle()
    {
        if(!is_null($this->short_code))
        {
            return $this->short_code;
        }else{
            return $this->identifier ? 
            $this->identifier->organizationalOutcome->costStructure->code.''.
            $this->identifier->organizationalOutcome->code.''.
            $this->identifier->program->code.''.
            $this->identifier->subProgram->code.''.
            $this->identifier->code.''.
            $this->code.'000' 
            : '';
        }
    }

    public function getCodeAndTitle()
    {
        if(!is_null($this->short_code))
        {
            return $this->short_code.' - '.$this->title;
        }else{
            return $this->identifier ? 
            $this->identifier->organizationalOutcome->costStructure->code.''.
            $this->identifier->organizationalOutcome->code.''.
            $this->identifier->program->code.''.
            $this->identifier->subProgram->code.''.
            $this->identifier->code.''.
            $this->code.'000'.' - '.
            $this->title 
            : '';
        }
    }

    public function getActivities()
    {
        return $this->hasMany(Activity::className(), ['pap_id' => 'id']);
    }
}
