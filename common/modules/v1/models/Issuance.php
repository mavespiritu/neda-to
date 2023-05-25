<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_issuance".
 *
 * @property int $id
 * @property string|null $issuance_date
 * @property string|null $issued_by
 *
 * @property PpmpIssuanceItem[] $ppmpIssuanceItems
 */
class Issuance extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_issuance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['issuance_date', 'issued_by'], 'required'],
            [['issuance_date'], 'safe'],
            [['ris_id'], 'integer'],
            [['issued_by'], 'string', 'max' => 8],
            [['ris_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ris::className(), 'targetAttribute' => ['ris_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ris_id' => 'Ris ID',
            'issuance_date' => 'Issuance Date',
            'issued_by' => 'Issued By',
        ];
    }

    /**
     * Gets query for [[Ris]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRis()
    {
        return $this->hasOne(Ris::className(), ['id' => 'ris_id']);
    }

    /**
     * Gets query for [[PpmpIssuanceItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIssuanceItems()
    {
        return $this->hasMany(IssuanceItem::className(), ['issuance_id' => 'id']);
    }

    public function getIssuer()
    {
        return $this->hasOne(Signatory::className(), ['emp_id' => 'issued_by']);
    }

    public function getIssuerName()
    {
        return $this->issuer ? $this->issuer->name : '';
    }

    public function getAverageRating()
    {
        $rating = IssuanceItem::find()
                    ->select(['avg(rating) as rating'])
                    ->where(['issuance_id' => $this->id])
                    ->asArray()
                    ->one();

        return $rating['rating'];
    }
}
