<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\DetailView;
use yii\web\View;
use yii\bootstrap\Collapse;
?>

<h3 class="panel-title">1.2 Manage Lot</h3>
<br>
<p><i class="fa fa-exclamation-circle"></i> Create lot to group items for bidding.</p>

<div class="row">
    <div class="col-md-12 col-xs-12">
    <h3 class="panel-title">Lot List <span class="pull-right"><?= Html::button('Create Lot', ['value' => Url::to(['/v1/pr/create-lot', 'id' => $model->id]), 'class' => 'btn btn-success btn-sm', 'id' => 'create-lot-button']) ?></span></h3>
        <br>
        <table class="table table-bordered table-responsive table-condensed table-striped table-hover">
            <thead>
                <tr>
                    <td style="width: 10%;" align=center><b>Lot No.</b></td>
                    <th style="width: 35%;">Lot Title</th>
                    <td style="width: 35%;" align=center><b>No. of items</b></td>
                    <th style="width: 10%;">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
            <?php if($lots){ ?>
                <?php foreach($lots as $lot){ ?>
                    <tr>
                        <td align=center><?= $lot->lot_no ?></td>
                        <td><b><?= Html::a($lot->title, null, ['href' => 'javascript:void(0)', 'onclick' => 'viewLot('.$lot->id.')']) ?></b></td>
                        <td align=center><?= number_format($lot->lotItemCount, 0) ?></td>
                        <td align=right>
                            
                            <?= Html::button('<i class="fa fa-edit"></i>', ['value' => Url::to(['/v1/pr/update-lot', 'id' => $lot->id]), 'class' => 'btn btn-sm btn-warning update-lot-button']) ?>
                            <?= Html::button('<i class="fa fa-trash"></i>', ['onclick' => 'deleteLot('.$model->id.','.$lot->id.')', 'class' => 'btn btn-sm btn-danger']) ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<br>
<div id="lot-content"></div>
<?php
  Modal::begin([
    'id' => 'create-lot-modal',
    'size' => "modal-sm",
    'header' => '<div id="create-lot-modal-header"><h4>Create Lot</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="create-lot-modal-content"></div>';
  Modal::end();
?>
<?php
  Modal::begin([
    'id' => 'update-lot-modal',
    'size' => "modal-sm",
    'header' => '<div id="update-lot-modal-header"><h4>Edit Lot</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-lot-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
    function viewLot(id)
    {
        $.ajax({
            url: "'.Url::to(['/v1/pr/view-lot']).'?id="+ id,
            beforeSend: function(){
                $("#lot-content").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
            },
            success: function (data) {
                console.log(this.data);
                $("#lot-content").empty();
                $("#lot-content").hide();
                $("#lot-content").fadeIn("slow");
                $("#lot-content").html(data);
            },
            error: function (err) {
                console.log(err);
            }
        });
    }
        function deleteLot(id, lot_id)
        {
            if(confirm("Are you sure you want to delete this item?"))
            {
                $.ajax({
                    url: "'.Url::to(['/v1/pr/delete-lot']).'?id="+ id +"&lot_id=" + lot_id,
                    method: "post",
                    beforeSend: function(){
                        $("#pr-container").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                    },
                    success: function (data) {
                        console.log(this.data);
                        alert("Lot has been deleted");
                        lot(id);
                        $("html").animate({ scrollTop: 0 }, "slow");
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        }

        $(document).ready(function(){
            $("#create-lot-button").click(function(){
              $("#create-lot-modal").modal("show").find("#create-lot-modal-content").load($(this).attr("value"));
            });
            $(".update-lot-button").click(function(){
                $("#update-lot-modal").modal("show").find("#update-lot-modal-content").load($(this).attr("value"));
            });
        });     
    ';

    $this->registerJs($script, View::POS_END);
?>