<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>System MOPS</h1>

        <p class="lead">You are now inside te rijkzwaan´s most powerfull aplication.</p>

        <p><a class="btn btn-lg btn-success" href="index.php?r=order%2Findex">See your orders</a></p>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4">
                <h2>Surface Planning</h2>
                <p>Do you want to know when the compartments will be free?</p>
                <p><a class="btn btn-lg btn-info" href="index.php?r=numcrop-has-compartment%2Findex">See the suface planning</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Excels</h2>

                <p>Still working on it</p>

                <p><a class="btn btn-lg btn-warning" href="index.php?r=order/pdf">Excel</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Compartments</h2>

                <p>Do you want to write a new report for the history of a compartment?</p>

                <p><a class="btn btn-lg btn-primary" href="index.php?r=historialcomp%2Findex">History of the compartments</a></p>
            </div>
        </div>

    </div>
</div>
