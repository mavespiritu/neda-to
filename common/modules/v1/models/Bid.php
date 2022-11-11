<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_bid".
 *
 * @property int $id
 * @property int|null $pr_id
 * @property int|null $rfq_id
 * @property string|null $bid_no
 * @property string|null $date_opened
 * @property string|null $time_opened
 *
 * @property PpmpBidMember[] $ppmpBidMembers
 */
class Bid extends \yii\db\ActiveRecord
{
    public $minute;
    public $meridian;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_bid';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_opened', 'time_opened', 'minute', 'meridian'], 'required'],
            [['pr_id', 'rfq_id'], 'integer'],
            [['date_opened'], 'safe'],
            [['bid_no', 'time_opened'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pr_id' => 'Pr ID',
            'rfq_id' => 'Rfq ID',
            'bid_no' => 'Bid No',
            'date_opened' => 'Date Opened',
            'time_opened' => 'Time Opened',
            'minute' => 'Minute',
            'meridian' => 'Meridian'
        ];
    }

    /**
     * Gets query for [[RRfq]].
     * @return \yii\db\ActiveQuery
     */
    public function getRfq()
    {
        return $this->hasOne(Rfq::className(), ['id' => 'rfq_id']);
    }

    /**
     * Gets query for [[PpmpBidMembers]].
     *
     * @return \yii\db\ActiveQuery
     */
    
    public function getBidMembers()
    {
        return $this->hasMany(BidMember::className(), ['bid_id' => 'id']);
    }

    public function getBidWinners()
    {
        return $this->hasMany(BidWinner::className(), ['bid_id' => 'id']);
    }

    public function getChairperson()
    {
        $member = BidMember::findOne(['bid_id' => $this->id, 'position' => 'BAC Chairperson']);
        $memberName = $member ? Signatory::findOne(['emp_id' => $member->emp_id]) : [];
        return !empty($memberName) ? $memberName->name : '';
    }

    public function getViceChairperson()
    {
        $member = BidMember::findOne(['bid_id' => $this->id, 'position' => 'BAC Vice-Chairperson']);
        $memberName = $member ? Signatory::findOne(['emp_id' => $member->emp_id]) : [];
        return !empty($memberName) ? $memberName->name : '';
    }

    public function getMember()
    {
        $member = BidMember::findOne(['bid_id' => $this->id, 'position' => 'BAC Member']);
        $memberName = $member ? Signatory::findOne(['emp_id' => $member->emp_id]) : [];
        return !empty($memberName) ? $memberName->name : '';
    }

    public function getExpert()
    {
        $member = BidMember::findOne(['bid_id' => $this->id, 'position' => 'Provisional Member']);
        $memberName = $member ? Signatory::findOne(['emp_id' => $member->emp_id]) : [];
        return !empty($memberName) ? $memberName->name : '';
    }

    public function getEndUser()
    {
        $member = BidMember::findOne(['bid_id' => $this->id, 'position' => 'Provisional Member - End User']);
        $memberName = $member ? Signatory::findOne(['emp_id' => $member->emp_id]) : [];
        return !empty($memberName) ? $memberName->name : '';
    }
    
    public function getPos()
    {
        return $this->hasMany(Po::className(), ['bid_id' => 'id']);
    }
}
