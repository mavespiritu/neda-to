<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tblemployee".
 *
 * @property string $emp_id
 * @property string|null $emp_type_id
 * @property string|null $lname
 * @property string|null $fname
 * @property string|null $mname
 * @property string|null $position_id
 * @property string|null $civil_status
 * @property string|null $birth_place
 * @property string|null $birth_date
 * @property string|null $gender
 * @property string|null $citizenship
 * @property float|null $height
 * @property float|null $weight
 * @property string|null $blood_type
 * @property string|null $cell_no
 * @property string|null $e_mail_add
 * @property string|null $residential_address
 * @property string|null $residential_zip_code
 * @property string|null $residential_tel_no
 * @property string|null $permanent_address
 * @property string|null $permanent_zip_code
 * @property string|null $permanent_tel_no
 * @property string|null $spouse_surname
 * @property string|null $spouse_firstname
 * @property string|null $spouse_middlename
 * @property string|null $father_surname
 * @property string|null $father_firstname
 * @property string|null $father_middlename
 * @property string|null $father_birthday
 * @property string|null $mother_surname
 * @property string|null $mother_firstname
 * @property string|null $mother_middlename
 * @property string|null $mother_birthday
 * @property string|null $hire_date
 * @property string|null $filename
 * @property string|null $picture
 * @property string|null $identification
 * @property string|null $password
 * @property string|null $work_status
 * @property string|null $default_dtr_type
 * @property string|null $division_id
 * @property string|null $one_status
 * @property string|null $earning_credits
 * @property string|null $earning_special
 * @property string|null $nick_name
 * @property string|null $Pag_ibig
 * @property string|null $GSIS
 * @property string|null $TIN
 * @property string|null $Philhealth
 * @property string|null $SSS
 * @property string|null $cedula_number
 * @property float|null $ot_previous_year
 * @property float|null $ot_current_year
 * @property string|null $government_date
 * @property string|null $findex
 * @property string|null $findexL
 * @property string|null $inactivity_date
 * @property string|null $inactivity_reason
 * @property string|null $title
 * @property string|null $prefix
 * @property string|null $staff_detail
 * @property string|null $dtr_exempted
 * @property string|null $sub_division
 *
 * @property Chat[] $chats
 * @property Chat[] $chats0
 * @property TblactualDtr[] $tblactualDtrs
 * @property TblauditTrail[] $tblauditTrails
 * @property TblauditTrail[] $tblauditTrails0
 * @property TblauditTrailPayroll[] $tblauditTrailPayrolls
 * @property Tblaward[] $tblawards
 * @property TblcalMeetings[] $tblcalMeetings
 * @property TblcomAcknowledgement[] $tblcomAcknowledgements
 * @property TblcomEmployeeAssignedCom[] $tblcomEmployeeAssignedComs
 * @property TblcomCommunication[] $titles
 * @property TblcomRouteSlipSender[] $tblcomRouteSlipSenders
 * @property TbldtrEmpLeaveAdditional[] $tbldtrEmpLeaveAdditionals
 * @property TbldtrLeaveApplication[] $tbldtrLeaveApplications
 * @property TblempAddress[] $tblempAddresses
 * @property TblempApprovedOt[] $tblempApprovedOts
 * @property TblempChildren[] $tblempChildrens
 * @property TblempCivilService[] $tblempCivilServices
 * @property TblempDispAction[] $tblempDispActions
 * @property TblempDtrTardyUndertime[] $tblempDtrTardyUndertimes
 * @property TblempDtrType[] $tblempDtrTypes
 * @property TblempDtrTypeDefault[] $tblempDtrTypeDefaults
 * @property TblempDtrTypePm[] $tblempDtrTypePms
 * @property TblempEducationalAttainment[] $tblempEducationalAttainments
 * @property TblempEmpItem[] $tblempEmpItems
 * @property TblempExpiredOt $tblempExpiredOt
 * @property TblempMarriageContract[] $tblempMarriageContracts
 * @property TblempMedicalCertificate[] $tblempMedicalCertificates
 * @property TblempNbiClearance[] $tblempNbiClearances
 * @property TblempNpesRating[] $tblempNpesRatings
 * @property TblempOtherDocument[] $tblempOtherDocuments
 * @property TblempOtherInfo[] $tblempOtherInfos
 * @property TblempQuestions[] $tblempQuestions
 * @property TblempReferences[] $tblempReferences
 * @property TblempServiceContract[] $tblempServiceContracts
 * @property TblempSpecialOrder[] $tblempSpecialOrders
 * @property TblempSpouseOccupation[] $tblempSpouseOccupations
 * @property TblempTrainingProgram[] $tblempTrainingPrograms
 * @property TblempVoluntaryWork[] $tblempVoluntaryWorks
 * @property TblempWorkExperience[] $tblempWorkExperiences
 * @property TblemployeeType $empType
 * @property Tbldivision $subDivision
 * @property Tblposition $position
 * @property Tbldivision $division
 * @property TblformSignatory[] $tblformSignatories
 * @property TblinvLedgerCard[] $tblinvLedgerCards
 * @property TblmanTeamMembership[] $tblmanTeamMemberships
 * @property TblmonthlyCreditTransaction[] $tblmonthlyCreditTransactions
 * @property TblmonthlyDtrSummary[] $tblmonthlyDtrSummaries
 * @property TblnoVerification $tblnoVerification
 * @property Tbloic[] $tbloics
 * @property TblpassSlip[] $tblpassSlips
 * @property TblpdsSetting $tblpdsSetting
 * @property TblprlAddCompDetails[] $tblprlAddCompDetails
 * @property TblprlDivisionEmp[] $tblprlDivisionEmps
 * @property TblprlDivision[] $divisions
 * @property TblprlEmpExemption $tblprlEmpExemption
 * @property TblprlLoanSchedule[] $tblprlLoanSchedules
 * @property TblprlMonthlyPayroll[] $tblprlMonthlyPayrolls
 * @property TblprlPayroll[] $payrollNumbers
 * @property TblprlOccasionalAddComPayroll[] $tblprlOccasionalAddComPayrolls
 * @property TblprlOic[] $tblprlOics
 * @property TblprlOptionalContribution[] $tblprlOptionalContributions
 * @property TblprlPayroll[] $tblprlPayrolls
 * @property TblprlPayrollSignatory[] $tblprlPayrollSignatories
 * @property TblprlYearlyIncomeTax[] $tblprlYearlyIncomeTaxes
 * @property TblrdcInfoModifiers $tblrdcInfoModifiers
 * @property TblstaffUnit[] $tblstaffUnits
 * @property Tblunit[] $unitNames
 * @property TblsystemUser[] $tblsystemUsers
 * @property TbltevAuthapprover $tbltevAuthapprover
 * @property TbltevAuthapprover[] $tbltevAuthapprovers
 * @property TbltevAuthapprover[] $tbltevAuthapprovers0
 * @property TbltevConcernedstaff[] $tbltevConcernedstaff
 * @property TbltevDigitalsignature[] $tbltevDigitalsignatures
 * @property TbltevDrivers $tbltevDrivers
 * @property TbltevTravelorder[] $tbltevTravelorders
 * @property Tbltraining[] $tbltrainings
 * @property TblvehicleTrip[] $tblvehicleTrips
 * @property TblvehicleTrip[] $tblvehicleTrips0
 * @property TblweeklyDtrSummary[] $tblweeklyDtrSummaries
 * @property User[] $users
 */
class Employee extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tblemployee';
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
            [['emp_id'], 'required'],
            [['birth_date', 'father_birthday', 'mother_birthday', 'hire_date', 'government_date', 'inactivity_date'], 'safe'],
            [['height', 'weight', 'ot_previous_year', 'ot_current_year'], 'number'],
            [['picture', 'findex', 'findexL'], 'string'],
            [['emp_id', 'emp_type_id', 'civil_status', 'citizenship', 'cell_no', 'residential_zip_code', 'residential_tel_no', 'permanent_zip_code', 'permanent_tel_no', 'spouse_surname', 'spouse_firstname', 'spouse_middlename', 'father_surname', 'father_firstname', 'father_middlename', 'mother_surname', 'mother_firstname', 'mother_middlename', 'work_status', 'default_dtr_type', 'division_id', 'one_status', 'Pag_ibig', 'GSIS', 'TIN', 'Philhealth', 'SSS', 'cedula_number', 'sub_division'], 'string', 'max' => 20],
            [['lname', 'fname', 'mname', 'position_id', 'nick_name'], 'string', 'max' => 50],
            [['birth_place', 'e_mail_add', 'residential_address', 'permanent_address', 'filename', 'identification', 'password'], 'string', 'max' => 255],
            [['gender', 'earning_credits', 'earning_special', 'dtr_exempted'], 'string', 'max' => 10],
            [['blood_type'], 'string', 'max' => 5],
            [['inactivity_reason', 'title', 'prefix', 'staff_detail'], 'string', 'max' => 100],
            [['emp_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'emp_id' => 'Emp ID',
            'emp_type_id' => 'Emp Type ID',
            'lname' => 'Lname',
            'fname' => 'Fname',
            'mname' => 'Mname',
            'position_id' => 'Position ID',
            'civil_status' => 'Civil Status',
            'birth_place' => 'Birth Place',
            'birth_date' => 'Birth Date',
            'gender' => 'Gender',
            'citizenship' => 'Citizenship',
            'height' => 'Height',
            'weight' => 'Weight',
            'blood_type' => 'Blood Type',
            'cell_no' => 'Cell No',
            'e_mail_add' => 'E Mail Add',
            'residential_address' => 'Residential Address',
            'residential_zip_code' => 'Residential Zip Code',
            'residential_tel_no' => 'Residential Tel No',
            'permanent_address' => 'Permanent Address',
            'permanent_zip_code' => 'Permanent Zip Code',
            'permanent_tel_no' => 'Permanent Tel No',
            'spouse_surname' => 'Spouse Surname',
            'spouse_firstname' => 'Spouse Firstname',
            'spouse_middlename' => 'Spouse Middlename',
            'father_surname' => 'Father Surname',
            'father_firstname' => 'Father Firstname',
            'father_middlename' => 'Father Middlename',
            'father_birthday' => 'Father Birthday',
            'mother_surname' => 'Mother Surname',
            'mother_firstname' => 'Mother Firstname',
            'mother_middlename' => 'Mother Middlename',
            'mother_birthday' => 'Mother Birthday',
            'hire_date' => 'Hire Date',
            'filename' => 'Filename',
            'picture' => 'Picture',
            'identification' => 'Identification',
            'password' => 'Password',
            'work_status' => 'Work Status',
            'default_dtr_type' => 'Default Dtr Type',
            'division_id' => 'Division ID',
            'one_status' => 'One Status',
            'earning_credits' => 'Earning Credits',
            'earning_special' => 'Earning Special',
            'nick_name' => 'Nick Name',
            'Pag_ibig' => 'Pag Ibig',
            'GSIS' => 'Gsis',
            'TIN' => 'Tin',
            'Philhealth' => 'Philhealth',
            'SSS' => 'Sss',
            'cedula_number' => 'Cedula Number',
            'ot_previous_year' => 'Ot Previous Year',
            'ot_current_year' => 'Ot Current Year',
            'government_date' => 'Government Date',
            'findex' => 'Findex',
            'findexL' => 'Findex L',
            'inactivity_date' => 'Inactivity Date',
            'inactivity_reason' => 'Inactivity Reason',
            'title' => 'Title',
            'prefix' => 'Prefix',
            'staff_detail' => 'Staff Detail',
            'dtr_exempted' => 'Dtr Exempted',
            'sub_division' => 'Sub Division',
        ];
    }

    /**
     * Gets query for [[TbltevTravelorders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTravelOrders()
    {
        return $this->hasMany(TravelOrder::className(), ['TO_creator' => 'emp_id']);
    }
}
