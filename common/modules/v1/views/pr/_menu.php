<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use yii\bootstrap\ButtonDropdown;
?>

  <div class="pull-left">
    <?= Html::a('<i class="fa fa-angle-double-left"></i> Back to PR List', ['/'.Yii::$app->session->get('PR_ReturnURL')], ['class' => 'btn btn-app']) ?>
    <a onclick="home(<?= $model->id?>);" class="btn btn-app main-menu">
      <?= $model->itemCount > 0 ? '<span class="badge bg-green">'.$model->itemCount.'</span>' : '' ?>
      <i class="fa fa-calendar-check-o"></i>View Details
    </a>
    <a onclick="manageItems(<?= $model->id?>);" class="btn btn-app main-menu" id="manage-item">
      <i class="fa fa-folder-o"></i>Manage Items
    </a>
    <?php // $this->render('menu/menu', ['model' => $model]) ?>
  </div>
  <div class="pull-right">
    <?= ($model->status->status == 'Draft' || $model->status->status == 'For Revision') || (Yii::$app->user->can('ProcurementStaff') || Yii::$app->user->can('Administrator')) ? Html::button('<i class="fa fa-edit"></i> Edit PR', ['value' => Url::to(['/v1/pr/update', 'id' => $model->id]), 'class' => 'btn btn-app', 'id' => 'update-button']) : '' ?>
    <?= ($model->status->status == 'Draft' || $model->status->status == 'For Revision') ||  (Yii::$app->user->can('ProcurementStaff') || Yii::$app->user->can('Administrator')) ? Html::a('<i class="fa fa-trash"></i> Delete PR', ['delete', 'id' => $model->id], [
        'class' => 'btn btn-app',
        'data' => [
            'confirm' => 'Deleting this PR will also delete all included items. Would you like to proceed?',
            'method' => 'post',
        ],
    ]) : '' ?>
  </div>
  <div class="clearfix"></div>
<?php
  Modal::begin([
    'id' => 'update-modal',
    'size' => "modal-md",
    'header' => '<div id="update-modal-header"><h4>Edit PR</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="update-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
        function home(id)
        {
            $.ajax({
                url: "'.Url::to(['/v1/pr/home']).'?id=" + id,
                beforeSend: function(){
                    $("#pr-main").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                    console.log(this.data);
                    $("#pr-main").empty();
                    $("#pr-container").empty();
                    $("#pr-submenu").empty();

                    $("#pr-main").hide();
                    $("#pr-main").fadeIn("slow");
                    $("#pr-main").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
        
        function manageItems(id)
        {
            $.ajax({
              url: "'.Url::to(['/v1/pr/sub-menu']).'?id=" + id + "&step=manageItems",
                beforeSend: function(){
                    $("#pr-submenu").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
                },
                success: function (data) {
                  $("#pr-main").empty();
                  $("#pr-submenu").empty();
                  $("#pr-submenu").hide();
                  $("#pr-submenu").fadeIn("slow");
                  $("#pr-submenu").html(data);
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }

        $(document).ready(function(){
          $("#update-button").click(function(){
            $("#update-modal").modal("show").find("#update-modal-content").load($(this).attr("value"));
          });
        });

        function printPr()
        {
          var printWindow = window.open(
            "'.Url::to(['/v1/pr/print', 'id' => $model->id]).'", 
            "Print",
            "left=200", 
            "top=200", 
            "width=950", 
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