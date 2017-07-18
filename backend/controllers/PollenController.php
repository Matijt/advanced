<?php

namespace backend\controllers;

use Yii;
use backend\models\Pollen;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PollenController implements the CRUD actions for Pollen model.
 */
class PollenController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Pollen models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Pollen::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Pollen model.
     * @param integer $idpollen
     * @param integer $order_idorder
     * @return mixed
     */
    public function actionView($idpollen, $order_idorder)
    {
        $model = $this->findModel($idpollen, $order_idorder);
        return $this->render('view', [
            'model' => $this->findModel($idpollen, $order_idorder),
        ]);
    }

    /**
     * Creates a new Pollen model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Pollen();

        if ($model->loadAll(Yii::$app->request->post())) {
            if($model->useMl) {
                $model->youHaveMl = $model->harvestMl - $model->useMl;
                }
            if($model->harvestDate) {
                $model->harvestDate = date('Y-m-d', strtotime($model->harvestDate));
            }
            if($model->useWeek) {
                $model->useWeek = date('Y-m-d', strtotime($model->useWeek));
            }
            if ($model->saveAll()) {
                return $this->redirect(['view', 'idpollen' => $model->idpollen, 'order_idorder' => $model->order_idorder]);
            }else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Pollen model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $idpollen
     * @param integer $order_idorder
     * @return mixed
     */
    public function actionUpdate($idpollen, $order_idorder)
    {
        $model = $this->findModel($idpollen, $order_idorder);

        if ($model->loadAll(Yii::$app->request->post())) {
            if($model->useMl) {
                $model->youHaveMl = $model->harvestMl - $model->useMl;
            }
            if($model->harvestDate) {
                $model->harvestDate = date('Y-m-d', strtotime($model->harvestDate));
            }
            if($model->useWeek) {
                $model->useWeek = date('Y-m-d', strtotime($model->useWeek));
            }
            if ($model->saveAll()) {
                return $this->redirect(['view', 'idpollen' => $model->idpollen, 'order_idorder' => $model->order_idorder]);
            }else {
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
     * Deletes an existing Pollen model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $idpollen
     * @param integer $order_idorder
     * @return mixed
     */
    public function actionDelete($idpollen, $order_idorder)
    {
        $this->findModel($idpollen, $order_idorder)->deleteWithRelated();

        return $this->redirect(['index']);
    }

    
    /**
     * Finds the Pollen model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $idpollen
     * @param integer $order_idorder
     * @return Pollen the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($idpollen, $order_idorder)
    {
        if (($model = Pollen::findOne(['idpollen' => $idpollen, 'order_idorder' => $order_idorder])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
