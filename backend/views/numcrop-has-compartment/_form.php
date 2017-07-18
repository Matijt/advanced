<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use backend\models\Compartment;
use backend\models\Numcrop;
use backend\models\Crop;

/* @var $this yii\web\View */
/* @var $model backend\models\NumcropHasCompartment */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="numcrop-has-compartment-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'crop_idcrops')->dropDownList(
        ArrayHelper::map(Crop::find()->all(), 'idcrops', 'crop'),
        ['prompt' => 'Select the crop']
    ) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
