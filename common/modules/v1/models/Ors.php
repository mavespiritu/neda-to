<?php

namespace common\modules\v1\models;

use Yii;
use markavespiritu\user\models\UserInfo;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ppmp_ors".
 *
 * @property int $id
 * @property int|null $pr_id
 * @property int|null $po_id
 * @property string|null $ors_no
 * @property string|null $ors_date
 * @property string|null $responsibility_center
 * @property int|null $created_by
 * @property string|null $date_created
 *
 * @property PpmpPo $po
 */
class Ors extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_ors';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ors_no', 'ors_date', 'responsibility_center'], 'required', 'on' => 'withPo'],
            [['ors_no', 'ors_date', 'responsibility_center', 'payee', 'address'], 'required', 'on' => 'withoutPo'],
            [['pr_id', 'po_id'], 'integer'],
            [['ors_date', 'date_created', 'office'], 'safe'],
            [['ors_no'], 'string', 'max' => 20],
            [['responsibility_center'], 'string', 'max' => 50],
            [['created_by', 'reviewed_by'], 'string', 'max' => 10],
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
            'pr_id' => 'Pr ID',
            'po_id' => 'Po ID',
            'ors_no' => 'ORS No',
            'ors_date' => 'ORS Date',
            'responsibility_center' => 'Responsibility Center',
            'created_by' => 'Created By',
            'date_created' => 'Date Created',
            'payee' => 'Payee',
            'office' => 'Office',
            'address' => 'Address',
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

    public function getCreator()
    {
        return $this->hasOne(UserInfo::className(), ['EMP_N' => 'created_by']); 
    }

    public function getCreatorName()
    {
        return $this->creator ? ucwords(strtolower($this->creator->FIRST_M.' '.$this->creator->LAST_M)) : '';
    }

    public function getReviewer()
    {
        return $this->hasOne(Signatory::className(), ['emp_id' => 'reviewed_by']); 
    }

    public function getReviewerName()
    {
        return $this->reviewer ? $this->reviewer->name : '';
    }

    public function getTotal()
    {
        $orsItemIDs = OrsItem::find()
                    ->select(['pr_item_id'])
                    ->andWhere(['pr_id' => $this->pr_id])
                    ->andWhere(['ors_id' => $this->id])
                    ->asArray()
                    ->all();

        $orsItemIDs = ArrayHelper::map($orsItemIDs, 'pr_item_id', 'pr_item_id');

        $po = !is_null($this->po_id) ? Po::findOne($this->po_id) : null;
        $supplier = !is_null($po) ? Supplier::findOne($po->supplier_id) : null;

        $items = !is_null($this->po_id) ? PrItemCost::find()
            ->select([
                'sum(ppmp_pr_item.quantity * ppmp_pr_item_cost.cost) as total'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_pr_item_cost.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere([
                'ppmp_pr_item_cost.pr_id' => $this->pr_id,
                'ppmp_pr_item_cost.supplier_id' => $supplier->id,
            ])
            ->andWhere(['in', 'ppmp_pr_item_cost.pr_item_id', $orsItemIDs])
            ->asArray()
            ->one() : OrsItem::find()
            ->select([
                'sum(ppmp_pr_item.quantity * ppmp_pr_item.cost) as total'
            ])
            ->leftJoin('ppmp_pr_item', 'ppmp_pr_item.id = ppmp_ors_item.pr_item_id')
            ->leftJoin('ppmp_ppmp_item', 'ppmp_ppmp_item.id = ppmp_pr_item.ppmp_item_id')
            ->leftJoin('ppmp_ris_item', 'ppmp_ris_item.id = ppmp_pr_item.ris_item_id')
            ->leftJoin('ppmp_ris', 'ppmp_ris.id = ppmp_pr_item.ris_id')
            ->leftJoin('ppmp_item', 'ppmp_item.id = ppmp_ppmp_item.item_id')
            ->andWhere(['ppmp_ors_item.pr_id' => $this->pr_id])
            ->andWhere(['in', 'ppmp_ors_item.pr_item_id', $orsItemIDs])
            ->asArray()
            ->one();

        return $items['total'];
    }
}
