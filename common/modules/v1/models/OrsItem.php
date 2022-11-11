<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_ors_item".
 *
 * @property int $id
 * @property int|null $pr_id
 * @property int|null $pr_item_id
 *
 * @property PpmpPr $pr
 * @property PpmpPrItem $prItem
 */
class OrsItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_ors_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pr_id', 'pr_item_id'], 'integer'],
            [['pr_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pr::className(), 'targetAttribute' => ['pr_id' => 'id']],
            [['pr_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => PrItem::className(), 'targetAttribute' => ['pr_item_id' => 'id']],
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
            'pr_item_id' => 'Pr Item ID',
        ];
    }

    /**
     * Gets query for [[Pr]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPr()
    {
        return $this->hasOne(Pr::className(), ['id' => 'pr_id']);
    }

    /**
     * Gets query for [[PrItem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrItem()
    {
        return $this->hasOne(PrItem::className(), ['id' => 'pr_item_id']);
    }
}
