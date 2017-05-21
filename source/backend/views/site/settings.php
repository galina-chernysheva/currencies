<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $model \backend\models\SettingsForm */
/* @var $this \yii\web\View */

$this->title = Yii::t('app', 'Settings');
?>

<div class="settings-form">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <fieldset>
        <legend><?= Yii::t('app', 'Updates') ?></legend>
        <?= $form->field($model, 'currencies_request_url')->textInput() ?>
        <?= $form->field($model, 'rates_request_url')->textInput() ?>
        <?= $form->field($model, 'frequency_success_hrs')->textInput(['type' => 'number', 'step' => 0.01]) ?>
        <?= $form->field($model, 'frequency_fail_hrs')->textInput(['type' => 'number', 'step' => 0.01]) ?>
        <?= $form->field($model, 'lifetime_hrs')->textInput(['type' => 'number', 'step' => 0.01]) ?>
    </fieldset>

    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>

    <?php ActiveForm::end(); ?>

</div>
