<?php

namespace common\modules\v1\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "ppmp_rfq".
 *
 * @property int $id
 * @property int|null $pr_id
 * @property string|null $rfq_no
 * @property string|null $deadline
 * @property int|null $delivery_period
 * @property int|null $supply_warranty
 * @property int|null $supply_equipment
 * @property int|null $price_validity
 *
 * @property PpmpPr $pr
 */
class Rfq extends \yii\db\ActiveRecord
{
    public $minute;
    public $meridian;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_rfq';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['deadline_date', 'deadline_time', 'delivery_period', 'price_validity', 'meridian', 'minute'], 'required'],
            [['pr_id', 'delivery_period', 'supply_warranty', 'supply_equipment', 'price_validity'], 'integer'],
            [['deadline_date', 'deadline_time', 'minute', 'meridian', 'supply_warranty_unit', 'supply_equipment_unit'], 'safe'],
            [['rfq_no', 'supply_warranty_unit', 'supply_equipment_unit'], 'string', 'max' => 100],
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
            'rfq_no' => 'Rfq No.',
            'deadline_date' => 'Date of Deadline',
            'deadline_time' => 'Time',
            'delivery_period' => 'Delivery Period',
            'supply_warranty' => 'Supply Warranty',
            'supply_warranty_unit' => 'Unit',
            'supply_equipment' => 'Supply Equipment',
            'supply_equipment_unit' => 'Unit',
            'price_validity' => 'Price Validity',
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

    public function getRfqInfo($supplier_id)
    {
        $info = RfqInfo::findOne(['rfq_id' => $this->id, 'supplier_id' => $supplier_id]);

        return $info;
    }

    public function getSuppliers()
    {
        $ids = RfqInfo::find()
                ->select(['supplier_id'])
                ->where(['rfq_id' => $this->id])
                ->asArray()
                ->all();

        $ids = ArrayHelper::map($ids, 'supplier_id', 'supplier_id');

        return Supplier::findAll(['id' => $ids]);
    }
}
