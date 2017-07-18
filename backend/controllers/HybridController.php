<?php

namespace backend\controllers;

use Yii;
use backend\models\Hybrid;
use backend\models\HybridSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HybridController implements the CRUD actions for Hybrid model.
 */
class HybridController extends Controller
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
     * Lists all Hybrid models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new HybridSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', '`hybrid`.`delete`', '0']);
        $dataProvider->query->andFilterWhere(['=', '`father`.`delete`', '0']);
        $dataProvider->query->andFilterWhere(['=', '`mother`.`delete`', '0']);
        $dataProvider->query->andFilterWhere(['=', '`crop`.`delete`', '0']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Hybrid model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->renderAjax('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Hybrid model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Hybrid();

        if ($model->load(Yii::$app->request->post())) {
            if($model->sowingFemale == null){
                $model->sowingFemale =0;
            }
            if($model->transplantingMale == null){
                $model->transplantingMale =0;
            }
            if($model->transplantingFemale == null){
                $model->transplantingFemale =0;
            }
            if($model->pollenColectF == null){
                $model->pollenColectF =0;
            }
            if($model->pollenColectU == null){
                $model->pollenColectU =0;
            }
            if($model->pollinitionF == null){
                $model->pollinitionF =0;
            }
            if($model->pollinitionU == null){
                $model->pollinitionU =0;
            }
            if($model->harvestF == null){
                $model->harvestF =0;
            }
            if($model->harvestU == null){
                $model->harvestU =0;
            }
            if($model->steamDesinfection == null){
                $model->steamDesinfection =0;
            }

            if($model->save()){
            return $this->actionIndex();
        } else {
                return $this->renderAjax('create', [
                    'model' => $model,
                ]);
            }
        }
        else {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Hybrid model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->idHybrid]);
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Hybrid model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete = 1;
        $model->save();
        return $this->actionIndex();
    }

    /**
     * Finds the Hybrid model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Hybrid the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Hybrid::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
