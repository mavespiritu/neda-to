<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_bid_member".
 *
 * @property int $id
 * @property int|null $bid_id
 * @property string|null $emp_id
 * @property string|null $office_id
 * @property string|null $position
 *
 * @property PpmpBid $bid
 */
class BidMember extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_bid_member';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['emp_id'], 'required'],
            [['bid_id'], 'integer'],
            [['emp_id'], 'string', 'max' => 10],
            [['position'], 'string', 'max' => 100],
            [['bid_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bid::className(), 'targetAttribute' => ['bid_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bid_id' => 'Bid ID',
            'emp_id' => 'Staff',
            'position' => 'Position',
        ];
    }

    /**
     * Gets query for [[Bid]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBid()
    {
        return $this->hasOne(Bid::className(), ['id' => 'bid_id']);
    }

    public function getSignatory()
    {
        return $this->hasOne(Signatory::className(), ['emp_id' => 'emp_id']);
    }
}
