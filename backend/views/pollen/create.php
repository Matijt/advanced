<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\backend\models\Pollen */

$this->title = Yii::t('app', 'Create Pollen');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Pollen'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pollen-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
