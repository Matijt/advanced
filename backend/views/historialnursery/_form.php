<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Historialnursery */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="historialnursery-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class='col-lg-6'>
            <?= $form->field($model, 'title')->textInput() ?>
        </div>
        <div class='col-lg-6'>
            <?= $form->field($model, 'nursery_idnursery')->dropDownList(
                ArrayHelper::map(\backend\models\Nursery::find()->all(), 'idnursery', 'numcompartment'),
                [
                    'prompt' => 'Select Nursery'
                ]
            ) ?>
        </div>
    </div>
    <?= $form->field($model, 'content')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
