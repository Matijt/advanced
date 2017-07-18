<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use backend\models\Compartment;
use backend\models\Nursery;
use backend\models\Hybrid;
use backend\models\Mother;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use kartik\widgets\Select2;



/* @var $this yii\web\View */
/* @var $model backend\models\Order */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="order-form">

    <?php $form = ActiveForm::begin();

    $model->ReqDeliveryDate = date('d-m-Y', strtotime($model->ReqDeliveryDate));
    $model->orderDate = date('d-m-Y', strtotime($model->orderDate));
    $model->ssRecDate = date('d-m-Y', strtotime($model->ssRecDate));
    $model->sowingDateM = date('d-m-Y', strtotime($model->sowingDateM));
    $model->sowingDateF = date('d-m-Y', strtotime($model->sowingDateF));
    $model->transplantingM = date('d-m-Y', strtotime($model->transplantingM));
    $model->transplantingF = date('d-m-Y', strtotime($model->transplantingF));
    $model->pollinationF = date('d-m-Y', strtotime($model->pollinationF));
    $model->pollinationU = date('d-m-Y', strtotime($model->pollinationU));
    $model->harvestF = date('d-m-Y', strtotime($model->harvestF));
    $model->harvestU = date('d-m-Y', strtotime($model->harvestU));
    $model->steamDesinfectionF = date('d-m-Y', strtotime($model->steamDesinfectionF));
    $model->steamDesinfectionU = date('d-m-Y', strtotime($model->steamDesinfectionU));
    ?>

    <?php
        if(isset($error)){
            if($error = 1){
                echo "<script>alert('You can´t use "." rows in the compartment "."')</script>";
            }
        }
    ?>
    <div class="row">
        <div class="col-lg-4">
            <?php
            if($model->isNewRecord) {
                $model->ReqDeliveryDate = date('d-m-Y');
                $model->orderDate = date('d-m-Y');
                $model->ssRecDate = date('d-m-Y');
            }
            echo "<br>" . $form->field($model, 'ReqDeliveryDate')->widget(
                    DatePicker::className(), [
                    // inline too, not bad
//        'inline' => true,
                    'language' => 'en_EN',
                    'value' => 'dd-mm-yyyy',
                    // modify template for custom rendering
//        'template' => '<div class="well well-sm" style="background-color: #fff; width:250px">{input}</div>',
                    'clientOptions' => [
                        'autoclose' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ]);
            echo "</div><div class='col-lg-4'>";
            echo "<br>" . $form->field($model, 'orderDate')->widget(
                    DatePicker::className(), [
                    // inline too, not bad
//        'inline' => true,
                    'language' => 'en_EN',
                    'value' => 'dd-mm-yyyy',
                    // modify template for custom rendering
//        'template' => '<div class="well well-sm" style="background-color: #fff; width:250px">{input}</div>',
                    'clientOptions' => [
                        'autoclose' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ]);

            echo "</div><div class='col-lg-4'>";
            echo "<br>" . $form->field($model, 'ssRecDate')->widget(
                    DatePicker::className(), [
                    // inline too, not bad
//        'inline' => true,
                    'language' => 'en_EN',
                    'value' => 'dd-mm-yyyy',
                    // modify template for custom rendering
//        'template' => '<div class="well well-sm" style="background-color: #fff; width:250px">{input}</div>',
                    'clientOptions' => [
                        'autoclose' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ]);

            echo "</div></div>";
            ?>
            <hr class="btn-warning">
    <div class="row">
            <?php
            if(!$model->isNewRecord) {
                echo "<div class='col-lg-3'>";
                echo    $form->field($model, 'numCrop')->textInput()."<br>";
                echo "</div>";
                echo "<div class='col-lg-3'>";
                echo $form->field($model, 'state')->dropDownList(["Seeds on its way" => "Seeds on its way",
                    "Seeds arrive" => "Seeds arrive",
                    "Male seeds planted" => "Male seeds planted",
                    "Female seeds planted" => "Female seeds planted",
                    "Male plants just transplanted" => "Male plants just transplanted",
                    "Female plants just transplanted" => "Female plants just transplanted",
                    "Plants after pollen collect and pollination" => "Plants after pollen collect and pollination",
                    "Harvested plants" => "Harvested plants",
                    "Order finish" => "Order finish"],
                    ['prompr' => 'Seleccione estado']);
                echo "</div>";
                echo "<div class='col-lg-3'>";
                echo $form->field($model, 'orderKg')->textInput([
                    'onchange' =>'
                                               if($.isNumeric($("#gp").val())){
                        $.post("index.php?r=order/compartment&gp="'.'+($("#gp").val())+"&kg="'.'+$(this).val(), function( data ){
                            $("#order-compartment_idcompartment").html(data);
                        });
}
                    ',
                ]);
                echo "</div>";
                echo '<div class="col-lg-3">';
                echo $form->field($model, 'contractNumber')->textInput();
                echo "</div>";
            }else{
                echo "<div class='col-lg-4'>";
                echo $form->field($model, 'orderKg')->textInput([
                        'onchange' =>'
                                                if($.isNumeric($("#gp").val())){
                        $.post("index.php?r=order/compartment&gp="'.'+($("#gp").val())+"&kg="'.'+$(this).val(), function( data ){
                            $("#order-compartment_idcompartment").html(data);
                        });
}
                        '
                ]);
                echo "</div>";
                echo "<div class='col-lg-4'>";
                echo $form->field($model, 'state')->dropDownList(["Seeds on its way" => "Seeds on its way",
                    "Seeds arrive" => "Seeds arrive",
                    "Male seeds planted" => "Male seeds planted",
                    "Female seeds planted" => "Female seeds planted",
                    "Male plants just transplanted" => "Male plants just transplanted",
                    "Female plants just transplanted" => "Female plants just transplanted",
                    "Plants after pollen collect and pollination" => "Plants after pollen collect and pollination",
                    "Harvested plants" => "Harvested plants",
                    "Order finish" => "Order finish"],
                    ['prompr' => 'Seleccione estado']);
                echo "</div>";
                echo '<div class="col-lg-4">';
                echo $form->field($model, 'contractNumber')->textInput();
                echo "</div>";


            }

            ?>
    </div>
    <hr class="btn-info">
    <div class="row">
        <div class="col-lg-2">
            <?= $form->field($model, 'Hybrid_idHybrid')->widget(Select2::classname(), [
                        'data' => ArrayHelper::map(Hybrid::find()->all(), 'idHybrid', 'variety'),
                        'options' =>
                            ['prompt' => 'Select hybrid',
                                'onchange' => '
                            $.post("index.php?r=order/history&id=' . '"+$(this).val(), function( data ){
                                $("input#gp").attr("selectBoxOptions", data);                         
                                createEditableSelect(document.forms[1].gpOrder);
                            });',
                            ]
                    ]
                );
            ?>
        </div>

        <div class="col-lg-2">
            <?php
            if($model->isNewRecord) {
                echo $form->field($model, 'gpOrder')->textInput(
                    [
                        'type' => 'textarea',
                        'id' =>'gp',
                        'name' => 'gpOrder',
                        'onchange' =>'
                        if($.isNumeric($("#order-orderkg").val())){
                        $.post("index.php?r=order/compartment&gp="'.'+($(this).val())+"&kg="'.'+$("#order-orderkg").val(), function( data ){
                            $("#order-compartment_idcompartment").html(data);
                        });
}'
                    ]);
            }else{
                echo $form->field($model, 'gpOrder')->textInput(
                    [
                        'type' => 'textarea',
                        'id' =>'gp',
                        'name' => 'gpOrder',
                        'onchange' =>'
                        if($.isNumeric($("#order-orderkg").val())){
                                                       $.post("index.php?r=order/compartment&gp="'.'+($(this).val
        ())+"&kg="'.'+$("#order-orderkg").val()+"&idc="'.'+$("#order-compartment_idcompartment").val()+"&numc="'.'+$("#order-compartment_idcompartment option:selected").text(), 
        function( data ){
                                    $("#order-compartment_idcompartment").html(data);
         });
         }
                       '
                    ]);
            }
            ?>
        </div>
        <div class="col-lg-6">

            <?= $form->field($model, 'compartment_idCompartment')->dropDownList(
                ArrayHelper::map(Compartment::find()->all(), 'idCompartment', 'compNum'),
                [
                    'prompt' => 'Select Compartments',
                    'onchange' => '$.get( "'.Url::toRoute('/order/validcompartment').'", { id:$(this).val(); try:$(this).val() }, function(data) {
                    $("select#models-contact").html(data);
                    });'
                ]
            ) ?>
        </div>
        <div class="col-lg-2">

            <?= $form->field($model, 'nursery_idnursery')->dropDownList(
                ArrayHelper::map(Nursery::find()->all(), 'idnursery', 'numcompartment'),
                [
                    'prompt' => 'Select Nursery',
//                    'onchange' => '$.get( "'.Url::toRoute('/order/validcompartment').'", { id:$(this).val(); try:$(this).val() }, function(data) {
  //                  $("select#models-contact").html(data);
    //                });'
                ]
            ) ?>
        </div>
    </div>
    <hr class="btn-success">

    <?php
        if(!($model->isNewRecord)){
            echo "<div class='row'><div class='col-lg-3'>";

            echo $form->field($model, 'sowingDateM')->widget(
                DatePicker::className(), [
                'language' => 'en_EN',
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'dd-mm-yyyy'
                ]
            ]);
            echo "</div><div class='col-lg-3'>";

            echo $form->field($model, 'sowingDateF')->widget(
                DatePicker::className(), [
                'language' => 'en_EN',
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'dd-mm-yyyy'
                ]
            ]);
            echo "</div><div class ='col-lg-3'>";
            echo $form->field($model, 'transplantingM')->widget(
                DatePicker::className(), [
                'language' => 'en_EN',
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'dd-mm-yyyy'
                ]
            ]);
            echo "</div><div class ='col-lg-3'>";
            echo $form->field($model, 'transplantingF')->widget(
                DatePicker::className(), [
                'language' => 'en_EN',
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'dd-mm-yyyy'
                ]
            ]);

            echo "</div></div>";
            echo "<hr class='btn-info'>";
            echo "<div class='row'>";
            echo "<div class='col-lg-3'>";
            echo $form->field($model, 'pollinationF')->widget(
                DatePicker::className(), [
                'language' => 'en_EN',
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'dd-mm-yyyy'
                ]
            ]);
            echo "</div>";
            echo "<div class='col-lg-3'>";
            echo $form->field($model, 'pollinationU')->widget(
                DatePicker::className(), [
                'language' => 'en_EN',
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'dd-mm-yyyy'
                ]
            ]);
            echo "</div>";
            echo "<div class='col-lg-3'>";
            echo $form->field($model, 'harvestF')->widget(
                DatePicker::className(), [
                'language' => 'en_EN',
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'dd-mm-yyyy'
                ]
            ]);
            echo "</div>";
            echo "<div class='col-lg-3'>";
            echo $form->field($model, 'harvestU')->widget(
                DatePicker::className(), [
                'language' => 'en_EN',
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'dd-mm-yyyy'
                ]
            ]);
            echo "</div></div>";
            echo "<hr class='btn-success'>";
            echo "<div class='row'>";
            echo "<div class='col-lg-6'>";
            echo $form->field($model, 'steamDesinfectionF')->widget(
                DatePicker::className(), [
                'language' => 'en_EN',
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'dd-mm-yyyy'
                ]
            ]);
            echo "</div>";
            echo "<div class='col-lg-6'>";
            echo $form->field($model, 'steamDesinfectionU')->widget(
                DatePicker::className(), [
                'language' => 'en_EN',
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'dd-mm-yyyy'
                ]
            ]);
            echo "</div></div>";
            echo "<hr class='btn-warning'>";
            echo "<div class='row'>";
            echo "<div class='col-lg-3'>";
            echo $form->field($model, 'realisedNrOfPlantsM')->textInput();
            echo "</div>";
            echo "<div class='col-lg-3'>";
            echo $form->field($model, 'extractedPlantsM')->textInput();
            echo "</div>";
            echo "<div class='col-lg-3'>";
            echo $form->field($model, 'realisedNrOfPlantsF')->textInput();
            echo "</div>";
            echo "<div class='col-lg-3'>";
            echo $form->field($model, 'extractedPlantsF')->textInput();
            echo "</div></div>";
            echo "<hr class='btn-primary'>";
        }
    ?>

    <?= $form->field($model, 'remarks')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?php
        if(isset($name)) {
            echo Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']);
        }else{
            echo Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']);
        }
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

        <script type="text/javascript">
//            createEditableSelect(document.forms[1].myText2);
        </script>
