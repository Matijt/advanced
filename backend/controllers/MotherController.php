<?php

namespace backend\controllers;

use backend\models\Germination;
use Yii;
use backend\models\Mother;
use backend\models\MotherSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MotherController implements the CRUD actions for Mother model.
 */
class MotherController extends Controller
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
     * Lists all Mother models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MotherSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'delete', '0']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Mother model.
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
     * Creates a new Mother model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Mother();
        $germination = new Germination();

        if ($model->load(Yii::$app->request->post())) {
            $germination->maleOrfemale = "F";
            $germination->description = "Created Female: Germination percentage = ".$model->germination."% Created for the user: ".Yii::$app->user->identity->username;
            $germination->variety = $model->variety;
            if($model->save() && $germination->save()) {
                return $this->actionIndex();
            }else{
                return $this->renderAjax('create', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Mother model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $modeltest = $this->findModel($id);
        $germination = new Germination();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if($modeltest->tsw){
                $model->tsw = (($model->tsw)+($modeltest->tsw))/2;
            }
            if($modeltest->gP){
                $model->gP = (($model->gP)+($modeltest->gP))/2;
            }
            $germination->maleOrfemale = "F";
            $germination->description = "Updated Female: Germination percentage = ".$model->germination."% Updated for the user: ".Yii::$app->user->identity->username;
            $germination->variety = $model->variety;
            if($model->save() && $germination->save()){
                return $this->actionIndex();
            }else{
                return $this->renderAjax('update', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Mother model.
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
     * Finds the Mother model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Mother the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Mother::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
