<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\HybridSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Hybrids');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hybrid-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::button(Yii::t('app', 'Create Hybrid'), ['value' => 'index.php?r=hybrid/create','class' => 'modalButtonCreate']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [            [
                'attribute'=>'Crop_idcrops',
                'value'=>'cropIdcrops.crop',
            ],
            'variety',
            [
                'attribute'=>'Father_idFather',
                'value'=>'fatherIdFather.variety',
            ],
            [
                'attribute'=>'Mother_idMother',
                'value'=>'motherIdMother.variety',
            ],
//            'remarks',

            ['class' => 'yii\grid\ActionColumn',
                'template' => '{view}{update}{delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-eye-open"></span>', ['value' => Url::to('index.php?r=hybrid%2Fview&id='.$model->idHybrid), 'class' => 'modalButtonView btn btn-primary'], [
                            'title' => Yii::t('app', 'View'),
                        ]);
                    },

                    'update' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-pencil"></span>', ['value' => Url::to('index.php?r=hybrid%2Fupdate&id='.$model->idHybrid), 'class' => 'modalButtonEdit btn btn-primary'], [
                            'title' => Yii::t('app', 'Update'),
                        ]);
                    },

                    'delete' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', 'index.php?r=hybrid/delete&id='.$model->idHybrid, [
                            'data-method' => 'POST',
                            'title' => Yii::t('app', 'Update'),
                            'data-confirm' => "Are you sure to delete this item?",
                            'role' => 'button',
                            'class' => 'modalButtonDelete',
                        ]);
                    }
                ],
            ],
        ],
    ]); ?>

</div>
