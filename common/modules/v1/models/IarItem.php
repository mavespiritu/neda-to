<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_iar_item".
 *
 * @property int $id
 * @property int|null $iar_id
 * @property int|null $pr_item_id
 * @property string|null $status
 * @property int|null $balance
 * @property int|null $delivery_time
 * @property int|null $courtesy
 *
 * @property PpmpIar $iar
 */
class IarItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_iar_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['balance', 'delivery_time', 'courtesy'], 'required'],
            [['iar_id', 'pr_item_id', 'balance', 'delivery_time', 'courtesy'], 'integer'],
            [['iar_id'], 'exist', 'skipOnError' => true, 'targetClass' => Iar::className(), 'targetAttribute' => ['iar_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'iar_id' => 'Iar ID',
            'pr_item_id' => 'Pr Item ID',
            'balance' => 'Delivered',
            'delivery_time' => 'Delivery Time',
            'courtesy' => 'Courtesy',
        ];
    }

    /**
     * Gets query for [[Iar]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIar()
    {
        return $this->hasOne(Iar::className(), ['id' => 'iar_id']);
    }
}
