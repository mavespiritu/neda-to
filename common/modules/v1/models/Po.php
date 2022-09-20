<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_po".
 *
 * @property int $id
 * @property int|null $pr_id
 * @property int|null $rfq_id
 * @property int|null $supplier_id
 * @property string|null $po_no
 * @property string|null $po_date
 * @property string|null $delivery_place
 * @property string|null $delivery_date
 * @property int|null $delivery_term
 * @property int|null $payment_term_id
 *
 * @property PpmpPaymentTerm $paymentTerm
 * @property PpmpPr $pr
 * @property PpmpRfq $rfq
 */
class Po extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_po';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['supplier_id', 'delivery_term', 'delivery_date', 'delivery_place', 'po_date', 'payment_term_id'], 'required'],
            [['pr_id', 'rfq_id', 'supplier_id', 'delivery_term', 'payment_term_id'], 'integer'],
            [['po_date', 'delivery_date'], 'safe'],
            [['delivery_place'], 'string'],
            [['po_no'], 'string', 'max' => 20],
            [['payment_term_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentTerm::className(), 'targetAttribute' => ['payment_term_id' => 'id']],
            [['pr_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pr::className(), 'targetAttribute' => ['pr_id' => 'id']],
            [['rfq_id'], 'exist', 'skipOnError' => true, 'targetClass' => Rfq::className(), 'targetAttribute' => ['rfq_id' => 'id']],
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
            'rfq_id' => 'Rfq ID',
            'supplier_id' => 'Supplier ID',
            'po_no' => 'Po No',
            'po_date' => 'Po Date',
            'delivery_place' => 'Delivery Place',
            'delivery_date' => 'Delivery Date',
            'delivery_term' => 'Delivery Term',
            'payment_term_id' => 'Payment Term ID',
        ];
    }

    /**
     * Gets query for [[PaymentTerm]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentTerm()
    {
        return $this->hasOne(PpmpPaymentTerm::className(), ['id' => 'payment_term_id']);
    }

    /**
     * Gets query for [[Pr]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPr()
    {
        return $this->hasOne(PpmpPr::className(), ['id' => 'pr_id']);
    }

    /**
     * Gets query for [[Rfq]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRfq()
    {
        return $this->hasOne(PpmpRfq::className(), ['id' => 'rfq_id']);
    }
}
