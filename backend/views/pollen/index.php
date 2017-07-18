<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use kartik\export\ExportMenu;
use kartik\grid\GridView;

$this->title = Yii::t('app', 'Pollen');
$this->params['breadcrumbs'][] = $this->title;
$search = "$('.search-button').click(function(){
	$('.search-form').toggle(1000);
	return false;
});";
$this->registerJs($search);
?>
<div class="pollen-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Pollen'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php 
    $gridColumn = [
        'harvestWeek',
        [
            'attribute' => 'order_idorder',
            'label' => Yii::t('app', 'Order information'),
            'value' => function($model){
                $genial = 'Crop: '.$model->orderIdorder->numCrop.' Compartment: '.$model->orderIdorder->compartmentIdCompartment->compNum.
                    ' Hybrid: '.$model->orderIdorder->hybridIdHybr->variety." Father: ".$model->orderIdorder->hybridIdHybr->fatherIdFather->variety." Lot number: ".$model->orderIdorder->contractNumber;
                return $genial;;
            },
            'filterType' => GridView::FILTER_SELECT2,
            'filter' => \yii\helpers\ArrayHelper::map(\backend\models\Order::find()->asArray()->all(), 'idorder', 'idorder'),
            'filterWidgetOptions' => [
                'pluginOptions' => ['allowClear' => true],
            ],
            'filterInputOptions' => ['placeholder' => 'Order', 'id' => 'grid--order_idorder']
        ],
        'harvestDate',
//        'harvestMl',
        'useWeek',
  //      'useMl',
    //    'youHaveMl',
        [
            'class' => 'yii\grid\ActionColumn',
        ],
    ]; 
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => $gridColumn,
        'pjax' => true,
                'pjaxSettings' => ['options' => ['id' => 'kv-pjax-container-pollen']],
                'panel' => [
                    'type' => GridView::TYPE_SUCCESS,
                    'heading' => '<span class="glyphicon glyphicon-book"></span>  ' . Html::encode($this->title),
                ],
                'export' => false,
                // your toolbar can include the additional full export menu
                'toolbar' => [
                    '{export}',
                    ExportMenu::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            'harvestWeek',
                            [
                                'attribute' => 'order_idorder',
                                'label' => Yii::t('app', 'Order information'),
                                'value' => function($model){
                                    $genial = 'Crop: '.$model->orderIdorder->numCrop.' Compartment: '.$model->orderIdorder->compartmentIdCompartment->compNum.
                                        ' Hybrid: '.$model->orderIdorder->hybridIdHybr->variety." Father: ".$model->orderIdorder->hybridIdHybr->fatherIdFather->variety." Lot number: ".$model->orderIdorder->contractNumber;
                                    return $genial;;
                                },
                                'filterType' => GridView::FILTER_SELECT2,
                                'filter' => \yii\helpers\ArrayHelper::map(\backend\models\Order::find()->asArray()->all(), 'idorder', 'idorder'),
                                'filterWidgetOptions' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'filterInputOptions' => ['placeholder' => 'Order', 'id' => 'grid--order_idorder']
                            ],
                            'harvestDate',
        //        'harvestMl',
                            'useWeek',
                            //      'useMl',
                            //    'youHaveMl',
                        ],
                        
                        'target' => ExportMenu::TARGET_BLANK,
                        'fontAwesome' => true,
                        'dropdownOptions' => [
                            'label' => 'Full',
                            'class' => 'btn btn-default',
                            'itemsBefore' => [
                                '<li class="dropdown-header">Export All Data</li>',
                            ],
                        ],
                        'exportConfig' => [
                            ExportMenu::FORMAT_PDF => false
                        ]
                    ]) ,
                ],
    ]); ?>

</div>
