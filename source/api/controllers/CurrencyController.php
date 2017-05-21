<?php

namespace api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBasicAuth;
use api\models\ApiCurrency;
use common\models\RatesUpdate;

/**
 * Class CurrencyController
 * @package api\controllers
 */

class CurrencyController extends ActiveController
{
    public $modelClass = 'api\models\ApiCurrency';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::className(),
        ];
        return $behaviors;
    }

    /**
     * Finds the ApiCurrency model based on its primary key value.
     * @param integer $id
     * @return ApiCurrency the loaded model
     */
    protected function findModel($id)
    {
        $modelClass= $this->modelClass;
        return $modelClass::findOne($id);
    }

    /**
     * Returns rate info for currency with ID equal $id and date equal GET $date param.
     * If $date param missed then returns rate for latest successful update.
     * If no rate for specified $date then returns rate for nearest date.
     * Also returns rate and service state info.
     */
    public function actionRate($id)
    {
        global $result, $errors, $warnings;
        $result = [];
        $errors = [];
        $warnings = [];

        /**
         * Put all result parts together
         * @return mixed|string
         */
        function combineResult() {
            global $result, $errors, $warnings;

            if ($errors) {
                $result['error'] = implode('. ', $errors);
            }
            if ($warnings) {
                $result['warning'] = implode('. ', $warnings);
            }
            return $result;
        }

        // check if requested currency exists
        $currency = $this->findModel($id);
        if (!$currency) {
            $errors[] = Yii::t('app', 'No currency with requested ID ({id})', ['id' => $id]);
            return combineResult();
        }

        $date = Yii::$app->request->get('date');

        // check if date param is valid
        if (!is_null($date)) {
            $date = date('d.m.Y', strtotime($date));
            if (!$date) {
                $errors[] = Yii::t('app', 'Incorrect date param value. Define date according "d.m.Y" format');
                return combineResult();
            }
        }

        // check service state using latest and latest success Updates
        $latest = RatesUpdate::getLatest();
        if (!$latest) {
            $errors[] = Yii::t('app', 'No currencies rates data in database');
            return combineResult();
        } elseif (!$latest->success) {
            $errors[] = Yii::t(
                'app', 'Latest update failed ({requested_at})', ['requested_at' => $latest->requested_at]
            );
            $latestSuccessful = RatesUpdate::getLatestSuccessful();
            if ($latestSuccessful) {
                $errors[] = Yii::t(
                    'app', 'Latest successful update executed at {requested_at}',
                    ['requested_at' => $latestSuccessful->requested_at]
                );
            } else {
                $errors[] = Yii::t('app', 'No successful updates loaded');
                return combineResult();
            }
        }

        // get rate for specified currency and date (or nearest date), check if it exists
        $update = RatesUpdate::getByCurrency($id, $date);
        if (!$update) {
            $update = RatesUpdate::getNearestByCurrency($id, $date);
        }
        if (!$update) {
            $errors[] = Yii::t('app', 'No rates for requested currency');
        } else {
            if (strtotime($date) != strtotime($update->rate_date)) {
                $request_date = $date ? $date : date('Y-m-d');
                $warnings[] = Yii::t(
                    'app', 'No rate for requested date ({date}). Returned rate for nearest date ({nearest_date})',
                    ['date' => $request_date, 'nearest_date' => $update->rate_date]
                );
            }

            // checks if rate is actual
            if ($update->isExpired()) {
                $warnings[] = Yii::t(
                    'app', 'Rate is out of date (expired at {expired_at}). Use it at your own risk.',
                    ['expired_at' => date('Y-m-d H:i:s', $update->expireTime)]
                );
            }

            // get rate values (original and corrected with currency percent setting)
            $rate = $update->find()
                ->select(['r.cb_value', 'r.value'])
                ->joinWith('rates r', false)
                ->where(['r.currency_id' => $id])
                ->asArray()
                ->one();

            $result = [
                'rate_date' => $update->rate_date,
                'cb_value' => $rate['cb_value'],
                'value' => $rate['value']
            ];
        }
        return combineResult();
    }
}
