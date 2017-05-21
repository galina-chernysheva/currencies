<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Currency */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="currency-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'cb_id')->textInput(['maxlength' => 10]) ?>

    <?= $form->field($model, 'iso_num_code')->textInput(['type' => 'number', 'min' => 1]) ?>

    <?= $form->field($model, 'iso_char_code')->textInput(['maxlength' => 3]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true,]) ?>

    <?= $form->field($model, 'en_name')->textInput(['maxlength' => true,]) ?>

    <?= $form->field($model, 'nominal')->textInput(['type' => 'number', 'min' => 1]) ?>

    <?= $form->field($model, 'rate_divergence_pct')->textInput(['type' => 'number', 'min' => 0, 'max' => 50]) ?>

    <div class="form-group">
        <?= Html::submitButton(
            $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'),
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
