<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\MotherSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Females');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mother-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::button(Yii::t('app', 'Create Female'), ['value' => 'index.php?r=mother/create','class' => 'modalButtonCreate']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'variety',
            'germination',
            'tsw',
             'gP',
   //          'ratio',
            // 'remarks:ntext',


            ['class' => 'yii\grid\ActionColumn',
                'template' => '{view}{update}{delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-eye-open"></span>', ['value' => Url::to('index.php?r=mother%2Fview&id='.$model->idMother), 'class' => 'modalButtonView btn btn-primary'], [
                            'title' => Yii::t('app', 'View'),
                        ]);
                    },

                    'update' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-pencil"></span>', ['value' => Url::to('index.php?r=mother%2Fupdate&id='.$model->idMother), 'class' => 'modalButtonEdit btn btn-primary'], [
                            'title' => Yii::t('app', 'Update'),
                        ]);
                    },

                    'delete' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', 'index.php?r=mother/delete&id='.$model->idMother, [
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
