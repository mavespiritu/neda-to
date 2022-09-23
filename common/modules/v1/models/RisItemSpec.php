<?php

namespace common\modules\v1\models;

use Yii;
use \file\models\File;
/**
 * This is the model class for table "ppmp_ris_item_spec".
 *
 * @property int $id
 * @property int|null $ris_id
 * @property int|null $activity_id
 * @property int|null $sub_activity_id
 * @property int|null $item_id
 * @property float|null $cost
 * @property string|null $type
 *
 * @property PpmpRisItemSpecValue[] $ppmpRisItemSpecValues
 */
class RisItemSpec extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_ris_item_spec';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ris_id', 'activity_id', 'sub_activity_id', 'item_id'], 'integer'],
            [['cost'], 'number'],
            [['type'], 'string'],
        ];
    }

    public function behaviors()
    {
        return [
            'fileBehavior' => [
                'class' => \file\behaviors\FileBehavior::className()
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ris_id' => 'Ris ID',
            'activity_id' => 'Activity ID',
            'sub_activity_id' => 'Sub Activity ID',
            'item_id' => 'Item ID',
            'cost' => 'Cost',
            'type' => 'Type',
        ];
    }

    /**
     * Gets query for [[PpmpRisItemSpecValues]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRisItemSpecValues()
    {
        return $this->hasMany(RisItemSpecValue::className(), ['ris_item_spec_id' => 'id']);
    }

    public function getRisItemSpecFiles()
    {
        return File::find()->where(['model' => 'RisItemSpec', 'itemId' => $this->id])->all();
    }

    public function getRisItemSpecValueString()
    {
        $details = '';
        
        if($this->risItemSpecValues)
        {
            $i = 0;
            foreach($this->risItemSpecValues as $value)
            {
                $details .= $i == count($this->risItemSpecValues) - 1 ? $value->description.': '.$value->value : $value->description.': '.$value->value.', '; 

                $i++;
            }
        }

        return $details;
    }
}
