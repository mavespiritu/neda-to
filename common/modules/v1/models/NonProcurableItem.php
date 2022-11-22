<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_non_procurable_item".
 *
 * @property int $id
 * @property int|null $pr_id
 * @property int|null $pr_item_id
 *
 * @property PpmpPrItem $prItem
 */
class NonProcurableItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_non_procurable_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pr_id', 'pr_item_id'], 'integer'],
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
     * Gets query for [[PrItem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrItem()
    {
        return $this->hasOne(PrItem::className(), ['id' => 'pr_item_id']);
    }
}
