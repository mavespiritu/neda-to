<?php 

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;
use common\modules\v1\models\PpmpItem;
use fedemotta\datatables\DataTables;
use yii\widgets\ListView;
use yii\widgets\ActiveForm;

?>
<?php $quantityTotal = 0; ?>
<?php $form = ActiveForm::begin([
    'options' => ['class' => 'disable-submit-buttons'],
    'id' => 'ris-realign-items-form',
]); ?>

<?php if($items){ ?>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th rowspan=2 style="width: 20%;">Item</th>
        <th rowspan=2>Unit</th>
        <th rowspan=2>Cost</th>
        <th rowspan=2>Remaining</th>
        <th rowspan=2>Order</th>
        <th rowspan=2>Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($items as $item){ ?>
        <?php $total = 0; ?>
        <tr>
          <td><?= $item->item->title ?></td>
          <td><?= $item->item->unit_of_measure ?></td>
          <td align=right><?= number_format($item->cost, 2) ?></td>
          <td align=center><?= number_format($item->remainingQuantity, 0) ?></td>
          <?= Html::hiddenInput('total-'.$item->id.'-hidden', 0, ['id' => 'total-'.$item->id.'-hidden']) ?>
          <td><?= $item->remainingQuantity > 0 ? $form->field($data[$item->id], "[$item->id]quantity")->textInput(['maxlength' => true, 'type' => 'number', 'min' => 0, 'value' => 0, 'placeholder' => 'Max: '.number_format($item->remainingQuantity, 0), 'max' => $item->remainingQuantity, 'onkeyup' => 'getTotal('.$item->id.','.$item->cost.','.json_encode($itemIDs).')'])->label(false) : $form->field($data[$item->id], "[$item->id]quantity")->textInput(['maxlength' => true, 'type' => 'number', 'min' => 0, 'value' => 0, 'placeholder' => 'Max: '.number_format($item->remainingQuantity, 0), 'max' => $item->remainingQuantity, 'disabled' => true, 'onkeyup' => 'getTotal('.$item->id.','.$item->cost.','.json_encode($itemIDs).')'])->label(false) ?></td>
          <td align=right><p id="total-<?= $item->id ?>">0.00</p></td>
        </tr>
        <?php $quantityTotal += $item->remainingQuantity ?>
      <?php } ?>
      <tr>
        <td colspan=5 align=right><h5>Minimum</h5></td>
        <td align=right><h5><?= ($model->getRealignAmount() - $model->getItemsTotal('Realigned')) < 0 ? number_format(0, 2) : number_format($model->getRealignAmount() - $model->getItemsTotal('Realigned'), 2) ?></h5></td>
      </tr>
      <tr>
        <td colspan=5 align=right><h5>Grand Total</h5></td>
        <td align=right><h5 id="grand-total">0.00</h5></td>
        <?= Html::hiddenInput('grandtotal-hidden', 0, ['id' => 'grandtotal-hidden']) ?>
      </tr>
      <tr>
        <td colspan=5 align=right><h5>Maximum</h5></td>
        <td align=right><h5><?= number_format(($model->getRealignAmount() + ($model->getItemsTotal('Supplemental') * 0.20)) - $model->getItemsTotal('Realigned'), 2) ?></h5></td>
      </tr>
    </tbody>
  </table>
  <div class="form-group pull-right">
  <?= ($model->status->status == 'Draft' || $model->status->status == 'For Revision') ? Html::submitButton('Realign Items', ['class' => 'btn btn-success', 'id' => 'realign-submit-button', 'data' => ['disabled-text' => 'Please Wait']]) : '' ?>
  </div>
<?php }else{ ?>
  <p class="text-center">No items selected.</p>
<?php } ?>

<?php ActiveForm::end(); ?>

<?php
  Modal::begin([
    'id' => 'buy-modal',
    'size' => "modal-lg",
    'header' => '<div id="buy-modal-header"><h4>Add to RIS</h4></div>',
    'options' => ['tabindex' => false],
  ]);
  echo '<div id="buy-modal-content"></div>';
  Modal::end();
?>
<?php
    $script = '
    $(".buy-button").click(function(){
        $("#ris-realign-item-form").empty();
        $("#ris-realign-item-form").hide();
        $("#ris-realign-item-form").fadeIn("slow");
        $("#ris-realign-item-form").load($(this).attr("value"));
    });

    function number_format (number, decimals, dec_point, thousands_sep) {
        number = (number + "").replace(/[^0-9+\-Ee.]/g, "");
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === "undefined") ? "," : thousands_sep,
            dec = (typeof dec_point === "undefined") ? "." : dec_point,
            s = "",
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return "" + Math.round(n * k) / k;
            };
        s = (prec ? toFixedFix(n, prec) : "" + Math.round(n)).split(".");
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || "").length < prec) {
            s[1] = s[1] || "";
            s[1] += new Array(prec - s[1].length + 1).join("0");
        }
        return s.join(dec);
    }

    function getTotal(id, cost, ids)
    {
      var quantity = parseInt($("#risitem-"+id+"-quantity").val());
      var total = quantity * cost;

      $("#total-"+id+"-hidden").val(total);

      $("#total-"+id).empty();
      $("#total-"+id).html(number_format(total, 2, ".", ","));    
      
      getGrandTotal(ids);
      
    }

    function getGrandTotal(ids)
    {
      var grandTotal = 0;

      if(ids)
      {
        for(var key in ids)
        {
          grandTotal += parseFloat($("#total-"+key+"-hidden").val());
        }
      }

      $("#grand-total").empty();
      $("#grand-total").html(number_format(grandTotal, 2, ".", ","));  
      $("#grandtotal-hidden").val(grandTotal);

      //allowButton(grandTotal);
    }

    function allowButton(tot)
    {
        if(
            ('.$quantityTotal.' > 0) && 
            ("'.$model->status->status.'" == "Draft" || "'.$model->status->status.'" == "For Revision") && 
            (tot >= '.$model->getRealignAmount().') &&
            (tot <= '.$model->getRealignAmount() + ($model->getItemsTotal('Supplemental') * 0.20).')
        )
        {
            $("#realign-submit-button").removeAttr("disabled");
        }else{
            $("#realign-submit-button").attr("disabled", "true");
        }
    }

    function loadRealignItems()
    {
      $.ajax({
        url: "'.Url::to(['/v1/ris/realign-items']).'",
        data: {
            id: '.$model->id.',
            activity_id: '.$activity->id.',
            sub_activity_id: '.$subActivity->id.',
            item_id: JSON.stringify('.json_encode($selectedItems).'),
        },
        beforeSend: function(){
            $("#ris-realign-item-list").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
        },
        success: function (data) {
            console.log(this.data);
            $("#ris-realign-item-list").empty();
            $("#ris-realign-item-list").hide();
            $("#ris-realign-item-list").fadeIn("slow");
            $("#ris-realign-item-list").html(data);
        },
        error: function (err) {
            console.log(err);
        }
    });
    }
    $("#ris-realign-items-form").on("beforeSubmit", function(e) {
      e.preventDefault();
     
      var form = $(this);
      var formData = form.serialize();

      $.ajax({
          url: form.attr("action"),
          type: form.attr("method"),
          data: formData,
          success: function (data) {
            if($("#grandtotal-hidden").val() > 0)
            {
              form.enableSubmitButtons();
              alert("Record Saved");
              location.reload();
            }else{
              alert("You need to enter quantity");
            }
          },
          error: function (err) {
              console.log(err);
          }
      });

      return false;
  });
    ';

    $this->registerJs($script, View::POS_END);
?>