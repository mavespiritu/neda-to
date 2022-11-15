<?php

namespace common\modules\v1\models;

use Yii;

/**
 * This is the model class for table "ppmp_fund_cluster".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $description
 */
class FundCluster extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ppmp_fund_cluster';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'unique', 'message' => 'The title has been used already'],
            [['description', 'code'], 'string'],
            [['title'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'code' => 'Code'
        ];
    }
}
