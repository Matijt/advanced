<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use backend\models\Crop;
use backend\models\Mother;
use backend\models\Father;

/* @var $this yii\web\View */
/* @var $model backend\models\Hybrid */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="hybrid-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'Crop_idcrops')->dropDownList(
        ArrayHelper::map(Crop::find()->andFilterWhere([">", "idcrops",1])->andFilterWhere(['=','delete','0',])->all(), 'idcrops', 'crop'),
        ['prompt' => 'Select the crop']
    ) ?>

    <?= $form->field($model, 'variety')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Father_idFather')->dropDownList(
        ArrayHelper::map(Father::find()->andFilterWhere(['=', 'delete', '0'])->all(), 'idFather', 'variety'),
        ['prompt' => 'Select the male']
    ) ?>

    <?= $form->field($model, 'Mother_idMother')->dropDownList(
        ArrayHelper::map(Mother::find()->andFilterWhere(['=','delete','0',])->all(), 'idMother', 'variety'),
        ['prompt' => 'Select the female']
    ) ?>

    <?= $form->field($model, 'sowingFemale')->textInput() ?>

    <?= $form->field($model, 'transplantingMale')->textInput() ?>

    <?= $form->field($model, 'transplantingFemale')->textInput() ?>

    <?= $form->field($model, 'pollenColectF')->textInput() ?>

    <?= $form->field($model, 'pollenColectU')->textInput() ?>

    <?= $form->field($model, 'pollinitionF')->textInput() ?>

    <?= $form->field($model, 'pollinitionU')->textInput() ?>

    <?= $form->field($model, 'harvestF')->textInput() ?>

    <?= $form->field($model, 'harvestU')->textInput() ?>

    <?= $form->field($model, 'steamDesinfection')->textInput() ?>

    <?= $form->field($model, 'remarks')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
