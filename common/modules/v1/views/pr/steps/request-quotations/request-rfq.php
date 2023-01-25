<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use yii\web\View;
use yii\bootstrap\Collapse;
?>

<h3 class="panel-title">3.2 Request RFQ</h3>
<br>
<p><i class="fa fa-exclamation-circle"></i> Create quotation to be provided to suppliers.</p>

<div class="row">
    <div class="col-md-6 col-xs-12">
    <h3 class="panel-title">RFQs <span class="pull-right"><?= Html::button('Create RFQ', ['value' => Url::to(['/v1/pr/create-rfq', 'id' => $model->id]), 'class' => 'btn btn-success btn-sm', 'id' => 'create-rfq-button']) ?></span></h3>
        <br>
        <table class="table table-bordered table-responsive table-condensed table-striped table-hover">
            <thead>
                <tr>
                    <th style="width: 40%;">RFQ No.</th>
                    <td style="width: 40%;" align=right><b>ABC</b></td>
                    <th style="width: 20%;">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
            <?php if($rfqs){ ?>
                <?php foreach($rfqs as $rfq){ ?>
                    <tr>
                        <td><b><?= Html::a($rfq->rfq_no, null, ['href' => 'javascript:void(0)', 'onclick' => 'viewRfq('.$rfq->id.')']) ?></b></td>
                        <td align=right><?= number_format($model->rfqTotal, 2) ?></td>
                        <td align=right>
                            <?= Html::button('<i class="fa fa-print"></i>', ['onclick' => 'printRfq('.$rfq->id.')', 'class' => 'btn btn-sm btn-info']) ?>
                            <?= Html::button('<i class="fa fa-edit"></i>', ['value' => Url::to(['/v1/pr/update-rfq', 'id' => $rfq->id]), 'class' => 'btn btn-sm btn-warning update-rfq-button']) ?>
                            <?= Html::button('<i class="fa fa-trash"></i>', ['onclick' => 'deleteRfq('.$model->id.','.$rfq->id.')', 'class' => 'btn btn-sm btn-danger']) ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<div id="rfq-content"></div>
<?php // !empty($items) ? Collapse::widget(['items' => $items, 'encodeLabels' => false, 'autoCloseItems' => true]) : '<p>No RFQs generated</p>' ?>
<?php
  Modal::begin([
    'id' => 'create-rfq-modal',
    'size' => "modal-lg",
    'header' => '<div id="create-rfq-modal-header"><h4>Create RFQ</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="create-rfq-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'update-rfq-modal',
    'size' => "modal-lg",
    'header' => '<div id="update-rfq-modal-header"><h4>Edit RFQ</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-rfq-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        function printRfq(id)
        {
            var printWindow = window.open(
                "'.Url::to(['/v1/pr/print-rfq']).'?id=" + id, 
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
        
        function deleteRfq(id, rfq_id)
        {
            if(confirm("Are you sure you want to delete this item?"))
            {
                $.ajax({
                    url: "'.Url::to(['/v1/pr/delete-rfq']).'?id="+ id +"&rfq_id=" + rfq_id,
                    method: "post",
                    beforeSend: function(){
                        $("#pr-container").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                    },
                    success: function (data) {
                        console.log(this.data);
                        alert("RFQ has been deleted");
                        menu('.$model->id.');
                        rfqQuotation(id);
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        }

        function viewRfq(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/view-rfq']).'?id="+ id,
                beforeSend: function(){
                    $("#rfq-content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#rfq-content").empty();
                    $("#rfq-content").hide();
                    $("#rfq-content").fadeIn("slow");
                    $("#rfq-content").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $(document).ready(function(){
            $("#create-rfq-button").click(function(){
              $("#create-rfq-modal").modal("show").find("#create-rfq-modal-content").load($(this).attr("value"));
            });
            $(".update-rfq-button").click(function(){
                $("#update-rfq-modal").modal("show").find("#update-rfq-modal-content").load($(this).attr("value"));
              });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>