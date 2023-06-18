<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbltev_travelorder".
 *
 * @property string $TO_NO
 * @property string|null $date_filed
 * @property string|null $TO_creator
 * @property string|null $TO_subject
 * @property string|null $date_from
 * @property string|null $date_to
 * @property int|null $withVehicle
 * @property string|null $isDirector_Approved
 * @property int|null $type_of_travel
 * @property string|null $otherpassenger
 * @property string|null $othervehicle
 * @property string|null $otherdriver
 *
 * @property TbltevConcernedstaff[] $tbltevConcernedstaff
 * @property TbltevDigitalsignature[] $tbltevDigitalsignatures
 * @property TbltevToLocation[] $tbltevToLocations
 * @property TbltevToVehicle[] $tbltevToVehicles
 * @property TbltevTypeoftravel $typeOfTravel
 * @property Tblemployee $tOCreator
 */
class TravelOrder extends \yii\db\ActiveRecord
{
    public $staffs;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbltev_travelorder';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db2');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[
                'type_of_travel',
                'TO_subject',
                'date_from',
                'date_to',
                'withVehicle',
                'staffs'
            ], 'required'],
            [['remarks'], 'required', 'on' => 'revise'],
            [['TO_subject', 'otherpassenger', 'othervehicle', 'otherdriver', 'remarks'], 'string'],
            [['withVehicle', 'type_of_travel'], 'integer'],
            [['TO_NO', 'date_filed', 'TO_creator', 'date_from', 'date_to'], 'string', 'max' => 20],
            [['isDirector_Approved'], 'string', 'max' => 1],
            [['TO_NO'], 'unique'],
            [['type_of_travel'], 'exist', 'skipOnError' => true, 'targetClass' => TravelType::className(), 'targetAttribute' => ['type_of_travel' => 'id']],
            [['TO_creator'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['TO_creator' => 'emp_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'TO_NO' => 'Travel Order No.',
            'date_filed' => 'Date Created',
            'TO_creator' => 'Created By',
            'creatorName' => 'Created By',
            'TO_subject' => 'Purpose *',
            'date_from' => 'Start Date *',
            'date_to' => 'End Date *',
            'withVehicle' => 'Request with vehicle *',
            'isDirector_Approved' => 'Is Director Approved',
            'type_of_travel' => 'Travel Type *',
            'travelTypeName' => 'Travel Type',
            'otherpassenger' => 'Other Passenger/s',
            'othervehicle' => 'Other Vehicle/s',
            'otherdriver' => 'Other Driver/s',
            'staffs' => 'List of staff included',
            'status' => 'Status',
            'remarks' => 'Remarks'
        ];
    }

    /**
     * Gets query for [[TbltevConcernedstaff]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConcernStaffs()
    {
        return $this->hasMany(ConcernStaff::className(), ['TO_NO' => 'TO_NO']);
    }

    /**
     * Gets query for [[TbltevDigitalsignatures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDigitalSignatures()
    {
        return $this->hasMany(Digitalsignature::className(), ['to_no' => 'TO_NO']);
    }

    /**
     * Gets query for [[TbltevToLocations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTravelOrderLocations()
    {
        return $this->hasMany(TravelOrderLocation::className(), ['TO_NO' => 'TO_NO']);
    }

    /**
     * Gets query for [[TbltevToVehicles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTravelOrderVehicles()
    {
        return $this->hasMany(TravelOrderVehicle::className(), ['TO_NO' => 'TO_NO']);
    }

    /**
     * Gets query for [[TypeOfTravel]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTravelType()
    {
        return $this->hasOne(TravelType::className(), ['id' => 'type_of_travel']);
    }

    public function getTravelTypeName()
    {
        return $this->travelType ? $this->travelType->description : '';
    }

    /**
     * Gets query for [[TOCreator]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(Employee::className(), ['emp_id' => 'TO_creator']);
    }

    public function getCreatorName()
    {
        return $this->creator ? $this->creator->fname.' '.$this->creator->lname : '';
    }

    public function getStatus()
    {
        $status = '';

        if($this->isDirector_Approved == ''){
            $status = '<span class="badge bg-blue">For Approval</span>';
        }else if($this->isDirector_Approved == 1){
            $status = '<span class="badge bg-green">Approved</span>';
        }else if($this->isDirector_Approved == 0){
            $status = '<span class="badge bg-red">Disapproved</span>';
        }

        return $status;
    }

    public function getStatusInfo()
    {
        $status = '';

        if($this->isDirector_Approved == ''){
            $status = 'This travel order is for approval of management.';
        }else if($this->isDirector_Approved == 1){
            $status = 'This travel order is already approved by the management.';
        }else if($this->isDirector_Approved == 0){
            $status = 'This travel order is disapproved.';
        }

        return $status;
    }
}
