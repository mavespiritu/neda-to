<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_issuance_item".
 *
 * @property int $id
 * @property int|null $issuance_id
 * @property int|null $ris_item_id
 * @property int|null $iar_item_id
 * @property int|null $quantity
 * @property int|null $rating
 *
 * @property PpmpIssuance $issuance
 * @property PpmpIarItem $iarItem
 * @property PpmpRisItem $risItem
 */
class IssuanceItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_issuance_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['quantity'], 'required', 'on' => 'issueItem'],
            [['rating'], 'required', 'on' => 'rateItem'],
            [['issuance_id', 'ris_item_id', 'iar_item_id', 'quantity', 'rating'], 'integer'],
            [['issuance_id'], 'exist', 'skipOnError' => true, 'targetClass' => Issuance::className(), 'targetAttribute' => ['issuance_id' => 'id']],
            [['iar_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => IarItem::className(), 'targetAttribute' => ['iar_item_id' => 'id']],
            [['ris_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => RisItem::className(), 'targetAttribute' => ['ris_item_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'issuance_id' => 'Issuance ID',
            'ris_item_id' => 'Ris Item ID',
            'iar_item_id' => 'Iar Item ID',
            'quantity' => 'Quantity',
            'rating' => 'Rating',
        ];
    }

    /**
     * Gets query for [[Issuance]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIssuance()
    {
        return $this->hasOne(Issuance::className(), ['id' => 'issuance_id']);
    }

    /**
     * Gets query for [[IarItem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIarItem()
    {
        return $this->hasOne(IarItem::className(), ['id' => 'iar_item_id']);
    }

    /**
     * Gets query for [[RisItem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRisItem()
    {
        return $this->hasOne(RisItem::className(), ['id' => 'ris_item_id']);
    }
}
