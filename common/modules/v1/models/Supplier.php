<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_supplier".
 *
 * @property int $id
 * @property string|null $business_name
 * @property string|null $business_address
 * @property string|null $owner_name
 * @property string|null $contact_person
 * @property string|null $landline
 * @property string|null $mobile_no
 * @property string|null $email_address
 * @property string|null $philgeps_no
 * @property string|null $bir_registration
 * @property string|null $tin_no
 *
 * @property PpmpPrItemCost[] $ppmpPrItemCosts
 */
class Supplier extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_supplier';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['business_name'], 'unique'],
            [['business_name', 'business_address', 'contact_person', 'landline', 'tin_no', 'mobile_no'], 'required'],
            [['business_address'], 'string'],
            [['business_name', 'owner_name', 'contact_person'], 'string', 'max' => 200],
            [['landline', 'tin_no'], 'string', 'max' => 20],
            [['mobile_no'], 'string', 'max' => 15],
            [['email_address', 'philgeps_no', 'bir_registration'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'business_name' => 'Business Name',
            'business_address' => 'Business Address',
            'owner_name' => 'Owner Name',
            'contact_person' => 'Contact Person',
            'landline' => 'Landline',
            'mobile_no' => 'Mobile No.',
            'email_address' => 'Email Address',
            'philgeps_no' => 'Philgeps No',
            'bir_registration' => 'BIR Registration',
            'tin_no' => 'TIN No.',
        ];
    }

    /**
     * Gets query for [[PpmpPrItemCosts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPpmpPrItemCosts()
    {
        return $this->hasMany(PpmpPrItemCost::className(), ['supplier_id' => 'id']);
    }

    public function getPos($bid_id)
    {
        $pos = Po::findAll(['supplier_id' => $this->id, 'bid_id' => $bid_id]);

        return $pos;
    }
}
