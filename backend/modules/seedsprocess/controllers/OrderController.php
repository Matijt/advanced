<?php

namespace backend\modules\seedsprocess\controllers;

use backend\models\Histcrop;
use Yii;
use backend\models\NumcropHasCompartment;
use backend\models\Numcrop;
use backend\modules\seedsprocess\models\Crop;
use backend\modules\seedsprocess\models\Order;
use backend\modules\seedsprocess\models\Mother;
use backend\modules\seedsprocess\models\Germination;
use backend\modules\seedsprocess\models\OrderSearch;
use backend\models\OrderSearchm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use backend\codigo\Facil;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Order models.
     * @return mixed inicio
     */
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $searchModel = OrderSearch::find()->where('(order.state = "Seeds on its way") OR (order.state = "Seeds arrive")')->all();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Order models.
     * @return mixed inicio planting
     */
    public function actionPlantingmindex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = ['defaultOrder' => ['sowingDateM'=>SORT_ASC]];
        $dataProvider->query->andWhere(["=", "state", "Seeds arrive"])
            ->andFilterWhere(['=', 'delete',0])->all();
        return $this->render('plantingm/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionPlantingfindex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = ['defaultOrder' => ['sowingDateF'=>SORT_ASC]];
        $dataProvider->query->andWhere(["=", "state", "Male seeds planted"])
            ->andFilterWhere(['=', 'delete',0])->all();
        return $this->render('plantingf/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionArriveindex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = ['defaultOrder' => ['ssRecDate'=>SORT_ASC]];
        $dataProvider->query->andWhere(["=", "state", "Seeds on its way"])
            ->andFilterWhere(['=', 'delete',0])->all();
        return $this->render('arrive/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionTransplantingmindex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = ['defaultOrder' => ['transplantingM'=>SORT_ASC]];
        $dataProvider->query->andWhere(["=", "state", "female seeds planted"])
            ->andFilterWhere(['=', 'delete',0])->all();
        return $this->render('transplantingm/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionTransplantingfindex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = ['defaultOrder' => ['transplantingF'=>SORT_ASC]];
        $dataProvider->query->andWhere(["=", "state", "Male plants just transplanted"])
            ->andFilterWhere(['=', 'delete',0])->all();
        return $this->render('transplantingf/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionOnlypcindex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = ['defaultOrder' => ['transplantingF'=>SORT_ASC]];
        $dataProvider->query->andWhere(["=", "state", "Female plants just transplanted"])
            ->andFilterWhere(['=', 'delete',0])->all();
        return $this->render('onlypc/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionHarvestindex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = ['defaultOrder' => ['transplantingF'=>SORT_ASC]];
        $dataProvider->query->andWhere(["=", "state", "Plants after pollen collect and pollination"])
            ->andFilterWhere(['=', 'delete',0])->all();
        return $this->render('harvest/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * Displays a single Order model. view
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCleanindex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = ['defaultOrder' => ['transplantingF'=>SORT_ASC]];
        $dataProvider->query->andWhere(["=", "state", "Harvested plants"])
            ->andFilterWhere(['=', 'delete',0])->all();
        return $this->render('clean/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionFinishindex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = ['defaultOrder' => ['selector'=>SORT_ASC]];
        $dataProvider->query->andWhere(["=", "state", "Order finish"])
            ->andFilterWhere(['=', 'delete',0])->all();
        return $this->render('finish/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Order model. view planting
     * @param integer $id
     * @return mixed
     */
    public function actionPlantingview($id)
    {
        return $this->render('planting/view', [
            'model' => $this->findModel($id),
        ]);
    }



    /**
     * Updates an existing Order model. update
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {

        $model = $this->findModel($id);
        $models = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $hecho = new Facil();
            $hecho->editar($model, $models);

            if ($model->save() && $model->hybridIdHybr->cropIdcrops->save()) {
                return $this->redirect(['view', 'id' => $model->idorder]);
            }else{
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    /**
     * Updates an existing Order model. updateplanting
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionPlantingupdate($id)
    {

        $model = $this->findModel($id);
        $models = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            $hecho = new Facil();
            $hecho->editar($model, $models);
            if ($model->save() && $model->hybridIdHybr->cropIdcrops->save()) {
                return $this->redirect(['plantingview', 'id' => $model->idorder]);
            }else{

                return $this->render('planting/update', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->render('planting/update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * delete an existing Order model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (($modelNHC = NumcropHasCompartment::findOne(['numcrop_cropnum' => ($model->numCrop)-1, 'compartment_idCompartment' => $model->compartment_idCompartment])) !== null){
            if($modelNHC->rowsLeft == 0){
                $modelNHC->lastUpdatedDate = date("Y-m-d");
                $modelNHC->rowsOccupied = $modelNHC->rowsOccupied - $model->numRows;
                $modelNHC->rowsLeft = $modelNHC->rowsLeft + $model->numRows;
                $modelNHC->save();
            }else{
                if (($modelNHC = NumcropHasCompartment::findOne(['numcrop_cropnum' => ($model->numCrop), 'compartment_idCompartment' => $model->compartment_idCompartment])) !== null) {
                    $modelNHC->lastUpdatedDate = date("Y-m-d");
                    $modelNHC->rowsOccupied = $modelNHC->rowsOccupied - $model->numRows;
                    $modelNHC->rowsLeft = $modelNHC->rowsLeft + $model->numRows;
                    $modelNHC->save();
                }
            }
        }
        $model->delete = 1;
        $model->save();
        return $this->redirect(['index']);
    }
    /**
     * delete planting an existing Order model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionPlantingdelete($id)
    {
        $model = $this->findModel($id);
            if (($modelNHC = NumcropHasCompartment::findOne(['numcrop_cropnum' => ($model->numCrop)-1, 'compartment_idCompartment' => $model->compartment_idCompartment])) !== null){
                if($modelNHC->rowsLeft == 0){
                    $modelNHC->lastUpdatedDate = date("Y-m-d");
                    $modelNHC->rowsOccupied = $modelNHC->rowsOccupied - $model->numRows;
                    $modelNHC->rowsLeft = $modelNHC->rowsLeft + $model->numRows;
                    $modelNHC->save();
                }else{
                    if (($modelNHC = NumcropHasCompartment::findOne(['numcrop_cropnum' => ($model->numCrop), 'compartment_idCompartment' => $model->compartment_idCompartment])) !== null) {
                        $modelNHC->lastUpdatedDate = date("Y-m-d");
                        $modelNHC->rowsOccupied = $modelNHC->rowsOccupied - $model->numRows;
                        $modelNHC->rowsLeft = $modelNHC->rowsLeft + $model->numRows;
                        $modelNHC->save();
                    }
                }
            }
        $model->delete = 1;
        $model->save();
        $vista = $_GET['name'];
        if(isset($vista)) {
            return $this->redirect([$vista.'index']);
        }
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
