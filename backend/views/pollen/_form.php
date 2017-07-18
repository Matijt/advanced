<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use backend\models\Order;
use kartik\datecontrol\DateControl;
use kartik\widgets\Select2;
use dosamigos\datepicker\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\backend\models\Pollen */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="pollen-form">

    <?php $form = ActiveForm::begin();

    if($model->harvestDate) {
        $model->harvestDate = date('d-m-Y', strtotime($model->harvestDate));
    }
    if ($model->useWeek) {
        $model->useWeek = date('d-m-Y', strtotime($model->useWeek));
    }
    ?>


    <?= $form->errorSummary($model); ?>

    <?= $form->field($model, 'harvestWeek')->textInput(['placeholder' => 'HarvestWeek']) ?>

    <?= $form->field($model, 'harvestDate')->widget(
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
    ]);?>

    <?= $form->field($model, 'harvestMl')->textInput(['maxlength' => true, 'placeholder' => 'HarvestMl']) ?>

    <?= $form->field($model, 'useWeek')->widget(
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
    ]);?>

    <?= $form->field($model, 'useMl')->textInput(['maxlength' => true, 'placeholder' => 'UseMl']) ?>

    <?= $form->field($model, 'order_idorder')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(Order::find()->joinWith('compartmentIdCompartment')->joinWith('hybridIdHybr')->orderBy('idorder')->all(),
            'idorder', 'fullName'),
        'options' => ['placeholder' => Yii::t('app', 'Choose Order')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);
    ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end()?>

</div>
