<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_payment_term".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $description
 *
 * @property PpmpPo[] $ppmpPos
 */
class PaymentTerm extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_payment_term';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['title'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[PpmpPos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPos()
    {
        return $this->hasMany(Po::className(), ['payment_term_id' => 'id']);
    }
}
