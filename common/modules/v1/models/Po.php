<?php

namespace common\modules\v1\models;

use Yii;
use yii\helpers\ArrayHelper;

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
 * @property int|null $delivery_term_id
 * @property int|null $payment_term_id
 *
 * @property PpmpPaymentTerm $paymentTerm
 * @property PpmpDeliveryTerm $deliveryTerm
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
            [['delivery_term', 'po_date', 'payment_term_id'], 'required'],
            [['pr_id', 'bid_id', 'supplier_id', 'payment_term_id'], 'integer'],
            [['po_date', 'delivery_date'], 'safe'],
            [['delivery_place', 'delivery_term', 'type', 'represented_by'], 'string'],
            [['po_no'], 'string', 'max' => 20],
            [['payment_term_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentTerm::className(), 'targetAttribute' => ['payment_term_id' => 'id']],
            [['pr_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pr::className(), 'targetAttribute' => ['pr_id' => 'id']],
            [['bid_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bid::className(), 'targetAttribute' => ['bid_id' => 'id']],
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
            'bid_id' => 'Bid ID',
            'supplier_id' => 'Supplier ID',
            'po_no' => 'PO No',
            'po_date' => 'PO Date',
            'delivery_place' => 'Delivery Place',
            'delivery_date' => 'Delivery Date',
            'delivery_term' => 'Delivery Term',
            'payment_term_id' => 'Payment Term',
            'type' => 'Type',
            'represented_by' => 'Represented By'
        ];
    }

    /**
     * Gets query for [[PaymentTerm]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentTerm()
    {
        return $this->hasOne(PaymentTerm::className(), ['id' => 'payment_term_id']);
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
     * Gets query for [[Bid]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBid()
    {
        return $this->hasOne(Bid::className(), ['id' => 'bid_id']);
    }

    /**
     * Gets query for [[Supplier]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(Supplier::className(), ['id' => 'supplier_id']);
    }

    /**
     * Gets query for [[Ntp]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNtps()
    {
        return $this->hasMany(Ntp::className(), ['po_id' => 'id']);
    }

    /**
     * Gets query for [[Nta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNoas()
    {
        return $this->hasMany(Noa::className(), ['po_id' => 'id']);
    }

    public function getOrs()
    {
        return $this->hasMany(Ors::className(), ['po_id' => 'id']);
    }

    public function getPocnNo()
    {
        return $this->type == 'PO' ? $this->po_no : 'CN-'.$this->po_no;
    }

    public function getTotal()
    {
        $awardedItems = BidWinner::find()
                ->select(['pr_item_id'])
                ->where([
                    'bid_id' => $this->bid_id,
                    'supplier_id' => $this->supplier_id,
                    'status' => 'Awarded'
                ])
                ->asArray()
                ->all();
        $awardedItems = ArrayHelper::map($awardedItems, 'pr_item_id', 'pr_item_id');

        $total = PrItem::find()
            ->select([
                'COALESCE(sum(ppmp_pr_item.cost * ppmp_pr_item.quantity), 0) as total',
            ])
            ->andWhere(['ppmp_pr_item.pr_id' => $this->pr_id])
            ->andWhere(['in', 'ppmp_pr_item.id', $awardedItems])
            ->asArray()
            ->one();

        return $total['total'];
    }

    public function getDeliveryBalance()
    {
        $totalDelivered = IarItem::find()
                    ->select([
                        'pr_item_id',
                        'sum(balance) as total'
                    ])
                    ->leftJoin('ppmp_iar', 'ppmp_iar.id = ppmp_iar_item.iar_id')
                    ->leftJoin('ppmp_po', 'ppmp_po.id = ppmp_iar.po_id')
                    ->andWhere(['ppmp_po.id' => $this->id])
                    ->groupBy(['pr_item_id'])
                    ->createCommand()
                    ->getRawSql();

        $items = IarItem::find()
            ->select([
                'sum(ppmp_pr_item.quantity - totalDelivered.total) as total'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_iar_item.pr_item_id')
            ->leftJoin('ppmp_iar', 'ppmp_iar.id = ppmp_iar_item.iar_id')
            ->leftJoin('ppmp_po', 'ppmp_po.id = ppmp_iar.po_id')
            ->leftJoin(['totalDelivered' => '('.$totalDelivered.')'],'totalDelivered.pr_item_id = ppmp_pr_item.id')
            ->andWhere(['ppmp_po.id' => $this->id])
            ->asArray()
            ->one();

        return $items['total'];
    }
}
