<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_apr_item".
 *
 * @property int $id
 * @property int|null $apr_id
 * @property int|null $pr_item_id
 *
 * @property PpmpApr $apr
 * @property PpmpPrItem $prItem
 */
class AprItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_apr_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['apr_id', 'pr_item_id'], 'integer'],
            [['apr_id'], 'exist', 'skipOnError' => true, 'targetClass' => Apr::className(), 'targetAttribute' => ['apr_id' => 'id']],
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
            'apr_id' => 'Apr ID',
            'pr_item_id' => 'Pr Item ID',
        ];
    }

    /**
     * Gets query for [[Apr]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApr()
    {
        return $this->hasOne(Apr::className(), ['id' => 'apr_id']);
    }

    public function getPrItemCost()
    {
        return $this->hasOne(PrItemCost::className(), ['pr_item_id' => 'pr_item_id', 'supplier_id' => 1]);
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
