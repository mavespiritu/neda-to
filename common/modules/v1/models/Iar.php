<?php

namespace common\modules\v1\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "ppmp_iar".
 *
 * @property int $id
 * @property int|null $pr_id
 * @property int|null $po_id
 * @property string|null $iar_no
 * @property string|null $iar_date
 * @property string|null $invoice_no
 * @property string|null $invoice_date
 * @property int|null $inspected_by
 * @property string|null $date_inspected
 * @property int|null $received_by
 * @property string|null $date_received
 * @property string|null $status
 *
 * @property PpmpPo $po
 * @property PpmpIarItem[] $ppmpIarItems
 */
class Iar extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_iar';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['iar_date', 'invoice_no', 'invoice_date', 'inspected_by', 'date_inspected', 'received_by', 'date_received'], 'required'],
            [['pr_id', 'po_id', 'inspected_by', 'received_by'], 'integer'],
            [['iar_date', 'invoice_date', 'date_inspected', 'date_received'], 'safe'],
            [['iar_no'], 'string', 'max' => 20],
            [['invoice_no'], 'string', 'max' => 100],
            [['po_id'], 'exist', 'skipOnError' => true, 'targetClass' => Po::className(), 'targetAttribute' => ['po_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pr_id' => 'PR No.',
            'po_id' => 'PO/Contract No.',
            'iar_no' => 'IAR No.',
            'iar_date' => 'IAR Date',
            'invoice_no' => 'Invoice No.',
            'invoice_date' => 'Invoice Date',
            'inspected_by' => 'Inspected By',
            'date_inspected' => 'Date Inspected',
            'received_by' => 'Received By',
            'date_received' => 'Date Received',
        ];
    }

    /**
     * Gets query for [[Po]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPo()
    {
        return $this->hasOne(Po::className(), ['id' => 'po_id']);
    }

    public function getStatus()
    {
        $ids = IarItem::find()->select(['pr_item_id'])->where(['iar_id' => $this->id])->asArray()->all();
        $ids = ArrayHelper::map($ids, 'pr_item_id', 'pr_item_id');

        $quantity = PrItem::find()
                    ->select([
                        'sum(quantity) as total'
                    ])
                    ->where(['in', 'id', $ids])
                    ->asArray()
                    ->one();

        $delivered = IarItem::find()
            ->select([
                'sum(ppmp_iar_item.balance) as total'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_iar_item.pr_item_id')
            ->leftJoin('ppmp_iar', 'ppmp_iar.id = ppmp_iar_item.iar_id')
            ->leftJoin('ppmp_po', 'ppmp_po.id = ppmp_iar.po_id')
            ->andWhere(['<=', 'ppmp_iar.id', $this->id])
            ->andWhere(['ppmp_po.id' => $this->po->id])
            ->asArray()
            ->one();

        return $quantity['total'] - $delivered['total'] > 0 ? 'Partial' : 'Complete';
    }

    /**
     * Gets query for [[PpmpIarItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIarItems()
    {
        return $this->hasMany(IarItem::className(), ['iar_id' => 'id']);
    }

    public function getInspector()
    {
        return $this->hasOne(Signatory::className(), ['emp_id' => 'inspected_by']);
    }

    public function getInspectorName() 
    {
        return $this->inspector ? $this->inspector->name : '';
    }

    public function getReceiver()
    {
        return $this->hasOne(Signatory::className(), ['emp_id' => 'received_by']);
    }

    public function getReceiverName()
    {
        return $this->receiver ? $this->receiver->name : '';
    }
}
