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
use yii\widgets\MaskedInput;
?>

<?php $form = ActiveForm::begin([
    'options' => ['class' => 'disable-submit-buttons'],
    'id' => 'ris-items-form',
]); ?>

<p>Note: If you have selected months above, the system will load the quantities at the order input box. You may still adjust your order and the system will charge it to the months with available quantities.</p>
<?php if($items){ ?>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th style="width: 40%;">Item</th>
          <th>Unit Cost</th>
          <th>Remaining</th>
          <th>Order</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php $quantityTotal = 0; ?>
        <?php foreach($items as $item){ ?>
          <tr>
            <td><?= $item->item->title ?></td>
            <td align=right><?= number_format($item->cost, 2) ?></td>
            <?= Html::hiddenInput('total-'.$item->id.'-hidden', 0, ['id' => 'total-'.$item->id.'-hidden']) ?>
            <td align=center><?= number_format($item->remainingQuantity, 0) ?></td>
            <td><?= $item->remainingQuantity > 0 ? $form->field($data[$item->id], "[$item->id]quantity")->textInput(['maxlength' => true, 'type' => 'number', 'min' => 0, 'placeholder' => 'Max: '.number_format($item->remainingQuantity, 0), 'max' => $item->remainingQuantity, 'onkeyup' => 'getTotal('.$item->id.','.$item->cost.','.json_encode($itemIDs).')'])->label(false) : $form->field($data[$item->id], "[$item->id]quantity")->textInput(['maxlength' => true, 'type' => 'number', 'min' => 0, 'value' => 0, 'placeholder' => 'Max: '.number_format($item->remainingQuantity, 0), 'max' => $item->remainingQuantity, 'disabled' => true, 'onkeyup' => 'getTotal('.$item->id.','.$item->cost.','.json_encode($itemIDs).')'])->label(false) ?></td>
            <td align=right><p id="total-<?= $item->id ?>">0.00</p></td>
          </tr>
          <?php $quantityTotal += $item->remainingQuantity ?>
        <?php } ?>
        <tr>
          <td colspan=4 align=right><h4>Grand Total</h4></td>
          <td align=right><h4 id="grand-total">0.00</h4></td>
          <?= Html::hiddenInput('grandtotal-hidden', 0, ['id' => 'grandtotal-hidden']) ?>
        </tr>
      </tbody>
    </table>
  <br>
  <div class="form-group pull-right">
    <?= $quantityTotal > 0 && ($model->status->status == 'Draft' || $model->status->status == 'For Revision') ? Html::submitButton('Add to RIS', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) : '' ?>
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
        $("#ris-item-form").empty();
        $("#ris-item-form").hide();
        $("#ris-item-form").fadeIn("slow");
        $("#ris-item-form").load($(this).attr("value"));
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
    }

    function loadItems()
    {
      $.ajax({
        url: "'.Url::to(['/v1/ris/load-items']).'",
        data: {
            id: '.$model->id.',
            activity_id: '.$activity->id.',
            sub_activity_id: '.$subActivity->id.',
            item_id: JSON.stringify('.json_encode($selectedItems).'),
            month_id: JSON.stringify('.json_encode($selectedMonths).'),
        },
        beforeSend: function(){
            $("#ris-item-list").html("<div class=\"text-center\" style=\"margin-top: 50px;\"><svg class=\"spinner\" width=\"30px\" height=\"30px\" viewBox=\"0 0 66 66\" xmlns=\"http://www.w3.org/2000/svg\"><circle class=\"path\" fill=\"none\" stroke-width=\"6\" stroke-linecap=\"round\" cx=\"33\" cy=\"33\" r=\"30\"></circle></svg></div>");
        },
        success: function (data) {
            console.log(this.data);
            $("#ris-item-list").empty();
            $("#ris-item-list").hide();
            $("#ris-item-list").fadeIn("slow");
            $("#ris-item-list").html(data);
        },
        error: function (err) {
            console.log(err);
        }
    });
    }
    $("#ris-items-form").on("beforeSubmit", function(e) {
      e.preventDefault();
     
      var form = $(this);
      var formData = form.serialize();

      $.ajax({
          url: form.attr("action"),
          type: form.attr("method"),
          data: formData,
          success: function (data) {
            form.enableSubmitButtons();
            loadItems();
            loadOriginalItems();
            alert("Record Saved");
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