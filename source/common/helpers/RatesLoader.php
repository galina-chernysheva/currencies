<?php

namespace common\helpers;

use Yii;
use yii2mod\settings\models\enumerables\SettingType;
use common\models\Currency;
use common\models\CurrencyRate;
use common\models\RatesUpdate;

/**
 * Helper for loading rates list from Bank of Russia website
 */
class RatesLoader
{
    /**
     * Saves rates from request to Bank for existing currencies and specified date (latest or specified by GET param), logs errors.
     * Returns log messages grouped by status.
     * @return array
     */
    public static function load()
    {
        $log = [
            'warning' => [],
            'error' => [],
            'success' => []
        ];
        $errorLog = &$log['error'];
        $warningLog = &$log['warning'];
        $successLog = &$log['success'];

        // check if exists at least one currency in DB
        if (Currency::find()->count() == 0) {
            $message = Yii::t('app', 'No currencies defined');
            $errorLog[] = $message;
            Yii::error($message, 'rates');
            return $log;
        }

        // get latest request state info from settings
        $latestTime = Yii::$app->settings->get('LatestRatesRequestInfo', 'latest_time');
        $latestIsSuccessful = Yii::$app->settings->get('LatestRatesRequestInfo', 'latest_is_successful');
        $failsCount = Yii::$app->settings->get('LatestRatesRequestInfo', 'latest_fails_count');

        // check frequency request conditions, exit if it is too early
        if (isset($latestTime) && isset($latestIsSuccessful)) {
            $settingName = $latestIsSuccessful ? 'frequency_success_hrs' : 'frequency_fail_hrs';
            $frequencyHrs = Yii::$app->settings->get('SettingsForm', $settingName);
            $nextTime = $latestTime + round($frequencyHrs * 3600);

            if ($nextTime >= time()) {
                $message = Yii::t(
                    'app', 'There is too early to repeat update. Try again after {time}',
                    ['time' => date('Y-m-d H:i:s', $nextTime)]
                );
                $warningLog[] = $message;
                Yii::warning($message, 'rates');
                return $log;
            };
        }

        // check if request URL defined in settings
        $host = Yii::$app->settings->get('SettingsForm', 'rates_request_url');
        if (!$host) {
            $message = Yii::t('app', 'Not defined "Request rates URL" application setting');
            $errorLog[] = $message;
            Yii::error($message, 'rates');
            return $log;
        }

        // create Update instance
        // It will be saved later with status (anyway) and rate_date (if response from bank is valid XML)
        // Exclution: not be saved if rates on requested date are already loaded
        $update = new RatesUpdate();

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $host);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new \Exception(
                    Yii::t('app', 'Rates request failed: {error}', ['error' => curl_error($ch)])
                );
            }

            curl_close($ch);

            // TODO: check if response is valid XML
            $xml = new \SimpleXMLElement($response);
            $curListAttrs = (array)$xml->attributes();
            $date = $curListAttrs['@attributes']['Date'];

            // check if rates for requested date do not exist
            // TODO: if rates exist check relations with all existing currencies (whether missed elements should be loaded?)
            $sameDateUpdateQuery = RatesUpdate::find()
                ->alias('ru')
                ->joinWith('rates r', false, 'INNER JOIN')
                ->groupBy('ru.id')
                ->having('COUNT(r.id) > 0 AND ru.success=:success', [':success' => true])
                ->where(['rate_date' => $date, 'success' => true]);

            if ($sameDateUpdateQuery->exists()) {
                // Exception with empty message to avoid error logging: no error, we just need to go to [finally]
                // No saving Update
                // TODO: custom Exception class
                $message = Yii::t('app', 'Rates for date "{date}" already loaded', ['date' => $date]);
                $successLog[] = $message;
                Yii::info($message, 'rates');
                throw new \Exception();
            }

            // Save update to use it ID later for relation with rates
            $update->rate_date = $date;
            if (!$update->save()) {
                $firstError = reset($update->getFirstErrors());
                throw new \Exception(
                    Yii::t('app', 'Unable save Rates Update: {error}', ['error' => $firstError])
                );
            };

            // Get existing currencies for response rates filtering
            $curData = Currency::find()
                ->select(['id', 'cb_id', 'rate_divergence_pct', 'name', 'en_name'])
                ->orderBy('cb_id')
                ->indexBy('cb_id')
                ->all();

            $updatedCurIds = [];
            $errorCurIds = [];

            // process rates from response
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                /**
                 * @var \SimpleXMLElement $rateXmlEl
                 */
                foreach ($xml as $rateXmlEl) {
                    $rateAttrs = (array)$rateXmlEl->attributes();
                    $cbId = $rateAttrs['@attributes']['ID'];
                    $value = floatval(str_replace(',', '.', $rateXmlEl->Value));

                    // create and save rate if its currency exists in DB
                    if (array_key_exists($cbId, $curData)) {
                        $currencyId = $curData[$cbId]['id'];

                        $newRate = new CurrencyRate();
                        $newRate->currency_id = $currencyId;
                        $newRate->update_id = $update->id;
                        $newRate->cb_value = $value;
                        $newRate->value = $value * (1 + $curData[$cbId]['rate_divergence_pct'] / 100);

                        if (!$newRate->save()) {
                            $errors = $newRate->getFirstErrors();
                            $firstError = reset($errors);
                            $message = Yii::t(
                                'app', 'Unable save rate for currency with ID "{currency_id}": {error}',
                                ['currency_id' => $currencyId, 'error' => $firstError]
                            );
                            $warningLog[] = $message;
                            Yii::warning($message, 'rates');

                            $errorCurIds[] = $cbId;

                            if (!$hasWarnings) {
                                $hasWarnings = true;
                            }
                        } else {
                            $updatedCurIds[] = $cbId;
                        }
                    }
                }
                if ($hasWarnings) {
                    $transaction->rollBack();
                } else {
                    $transaction->commit();
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
            }

            // Collect currencies IDs which missed in rates response
            $missedCurIds = array_filter(
                array_diff(array_keys($curData), $updatedCurIds, $errorCurIds)
            );

            if (empty($errorCurIds) && empty($missedCurIds)) {
                // Success! No errors and missed rates, so just change Update status
                $update->success = true;
                if (!$update->save()) {
                    $firstError = reset($update->getFirstErrors());
                    throw new \Exception(Yii::t('app', 'Unable save Rates Update: {error}', ['error' => $firstError]));
                };
                $message = Yii::t('app', 'Rates updated successfully');
                $successLog[] = $message;
                Yii::info($message, 'rates');
                $failsCount = 0;
            } elseif (!empty($missedCurIds)) {
                // TODO: logs message about missed currencies rates ($missedCurIds) with db IDs, not Bank internal IDs
                 throw new \Exception(Yii::t('app', 'Missed rates for some currencies'));
            } elseif (!empty($errorCurIds)) {
                // We already logged this errors as warnings, but in common it is fail, so log result general error
                throw new \Exception(Yii::t('app', 'Rates update failed'));
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if ($message) {
                $failsCount++;
                if ($failsCount > 1) {
                    $message .=
                        '. ' . Yii::t('app', 'Update failed {count} times in a row!' , ['count' => $failsCount]);
                }
                $errorLog[] = $message;
                Yii::error($message, 'rates');
            }
        } finally {
            // update latest request state info
            Yii::$app->settings->set('LatestRatesRequestInfo', 'latest_time', strtotime($update->requested_at));
            Yii::$app->settings->set(
                'LatestRatesRequestInfo', 'latest_is_successful', (int)$update->success
            );
            Yii::$app->settings->set('LatestRatesRequestInfo', 'latest_fails_count', $failsCount);
        }

        return $log;
    }
}
