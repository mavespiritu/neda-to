<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;
use common\modules\v1\models\Iar;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\v1\models\IarSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'IAR';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="iar-index">

    <p>
        <?= Html::button('<i class="fa fa-plus"></i> Create', ['value' => Url::to(['/v1/iar/create']), 'class' => 'btn btn-app', 'id' => 'create-button']) ?>
    </p>


    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-search"></i> Search Filter
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <?= $this->render('_search', [
                        'model' => $searchModel,
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-xs-12">
            <div class="box box-primary">
                <div class="box-header panel-title"><i class="fa fa-list"></i> IAR List</div>
                <div class="box-body">
                    <?= GridView::widget([
                        'options' => [
                            'class' => 'table-responsive',
                        ],
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            'iar_no',
                            'iar_date',
                            'prNo',
                            'poNo',
                            'invoice_no',
                            'invoice_date',
                            'inspectorName',
                            'date_inspected',
                            'receiverName',
                            'date_received',
                            'status',

                            [
                                'format' => 'raw', 
                                'value' => function($model){
                                    $buttons = Html::a('View', ['/v1/iar/view', 'id' => $model->id],['class' => 'btn btn-primary btn-xs']);
                                    $buttons.= '&nbsp;&nbsp;';
                                    $buttons.= Html::a('Print', null, ['class' => 'btn btn-info btn-xs', 'onclick' => 'printIar('.$model->id.')']);

                                    return $buttons;
                            }],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
  Modal::begin([
    'id' => 'create-modal',
    'size' => "modal-md",
    'header' => '<div id="create-modal-header"><h4>Create IAR</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="create-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        $(document).ready(function(){
            $("#create-button").click(function(){
              $("#create-modal").modal("show").find("#create-modal-content").load($(this).attr("value"));
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>
<?php
    $script = '
        function printIar(id)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-iar']).'?id=" + id, 
                "Print",
                "left=200", 
                "top=200", 
                "width=650", 
                "height=500", 
                "toolbar=0", 
                "resizable=0"
                );
                printWindow.addEventListener("load", function() {
                    printWindow.print();
                    setTimeout(function() {
                    printWindow.close();
                }, 1);
                }, true);
        }
    ';

    $this->registerJs($script, View::POS_END);
?>