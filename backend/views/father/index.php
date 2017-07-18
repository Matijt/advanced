<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\FatherSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Males');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="father-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::button(Yii::t('app', 'Create Male'), ['value' => 'index.php?r=father/create', 'class' => 'modalButtonCreate']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'variety',
            'steril',
            'germination',
            'pollenProduction',
            // 'tsw',
            // 'remarks:ntext',

            ['class' => 'yii\grid\ActionColumn',
                'template' => '{view}{update}{delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-eye-open"></span>', ['value' => Url::to('index.php?r=father%2Fview&id='.$model->idFather), 'class' => 'modalButtonView btn btn-primary'], [
                            'title' => Yii::t('app', 'View'),
                        ]);
                    },

                    'update' => function ($url, $model) {
                        return Html::button('<span class="glyphicon glyphicon-pencil"></span>', ['value' => Url::to('index.php?r=father%2Fupdate&id='.$model->idFather), 'class' => 'modalButtonEdit btn btn-primary'], [
                            'title' => Yii::t('app', 'Update'),
                        ]);
                    },

                    'delete' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', 'index.php?r=father/delete&id='.$model->idFather, [
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
