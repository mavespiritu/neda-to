<?php

namespace common\modules\v1\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ppmp_lot".
 *
 * @property int $id
 * @property int|null $pr_id
 * @property int|null $lot_no
 * @property string|null $title
 *
 * @property PpmpPr $pr
 * @property PpmpLotItem[] $ppmpLotItems
 */
class Lot extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_lot';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lot_no', 'title'], 'required'],
            [['pr_id', 'lot_no'], 'integer'],
            [['title'], 'string'],
            [['pr_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pr::className(), 'targetAttribute' => ['pr_id' => 'id']],
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
            'lot_no' => 'Lot No.',
            'title' => 'Title',
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
     * Gets query for [[PpmpLotItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLotItems()
    {
        return $this->hasMany(LotItem::className(), ['lot_id' => 'id']);
    }

    public function getLotItemCount()
    {
        $lotItemIDs = LotItem::findAll(['lot_id' => $this->id]);
        $lotItemIDs = ArrayHelper::map($lotItemIDs, 'pr_item_id', 'pr_item_id');

        $items = PrItem::find()
                ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
                ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
                ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
                ->leftJoin('ppmp_activity', 'ppmp_activity.id = ppmp_ppmp_item.activity_id')
                ->leftJoin('ppmp_sub_activity', 'ppmp_sub_activity.id = ppmp_ppmp_item.sub_activity_id')
                ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
                ->leftJoin('ppmp_ris_item_spec s', 's.ris_id = ppmp_ris.id and 
                                                    s.activity_id = ppmp_ppmp_item.activity_id and 
                                                    s.sub_activity_id = ppmp_ppmp_item.sub_activity_id and 
                                                    s.item_id = ppmp_ppmp_item.item_id and 
                                                    s.cost = ppmp_pr_item.cost and 
                                                    s.type = ppmp_pr_item.type')
                ->andWhere([
                    'ppmp_pr_item.pr_id' => $this->pr_id,
                ])
                ->andWhere(['ppmp_pr_item.id' => $lotItemIDs])
                ->groupBy(['ppmp_item.id', 'ppmp_ris.id', 'ppmp_activity.id', 'ppmp_sub_activity.id', 'ppmp_pr_item.cost'])
                ->count();
        
        return $items;
    }
}
