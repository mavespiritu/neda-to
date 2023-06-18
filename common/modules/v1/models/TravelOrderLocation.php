<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbltev_to_location".
 *
 * @property int $loc_id
 * @property int|null $specificLocation
 * @property string|null $TO_NO
 *
 * @property TbltevTravelorder $tONO
 * @property TbltevSpecificlocation $specificLocation0
 */
class TravelOrderLocation extends \yii\db\ActiveRecord
{
    public $region;
    public $province;
    public $citymun;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbltev_to_location';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db2');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['region', 'province', 'citymun', 'specificLocation'], 'required'],
            [['loc_id'], 'integer'],
            [['TO_NO'], 'string', 'max' => 20],
            [['specificLocation'], 'safe'],
            [['TO_NO'], 'exist', 'skipOnError' => true, 'targetClass' => TravelOrder::className(), 'targetAttribute' => ['TO_NO' => 'TO_NO']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'loc_id' => 'Loc ID',
            'specificLocation' => 'Specific Location',
            'TO_NO' => 'To  No',
            'region' => 'Region',
            'province' => 'Province',
            'citymun' => 'City/Municipality',
        ];
    }

    /**
     * Gets query for [[TONO]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTravelOrder()
    {
        return $this->hasOne(TravelOrder::className(), ['TO_NO' => 'TO_NO']);
    }

    /**
     * Gets query for [[SpecificLocation0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLocation()
    {
        return $this->hasOne(Location::className(), ['id' => 'specificLocation']);
    }
}
