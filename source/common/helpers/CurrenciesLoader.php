<?php

namespace common\helpers;

use Yii;
use common\models\Currency;

/**
 * Helper for loading currencies list from Bank of Russia website
 */
class CurrenciesLoader
{
    /**
     * Saves currencies from request to Bank, logs errors.
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

        // check if request URL defined in settings
        $host = Yii::$app->settings->get('SettingsForm', 'currencies_request_url');
        if (!$host) {
            $message = Yii::t('app', 'Not defined "Request currencies URL" application setting');
            $errorLog[] = $message;
            Yii::error($message, 'currencies');
            return $log;
        }

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $host);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new \Exception(
                    Yii::t('app', 'Currencies request failed: {error}', ['error' => curl_error($ch)])
                );
            }

            curl_close($ch);

            // TODO: check if response is valid XML
            $xml = new \SimpleXMLElement($response);

            $hasWarnings = false;
            $newCount = 0;

            // Get existing currencies IDs for response currencies filtering
            $curIdsHash = Currency::find()
                ->select(['id'])
                ->indexBy('cb_id')
                ->orderBy('cb_id')
                ->column();
            $curErrors = [];

            // process currencies from response
            /**
             * @var \SimpleXMLElement $curXmlEl
             */
            foreach ($xml as $curXmlEl) {
                $data = (array) $curXmlEl;

                $cbId = $data['@attributes']['ID'];

                unset($data['@attributes']);
                $data = array_map('trim', $data);

                if (array_key_exists($cbId, $curIdsHash))
                    continue;

                $newCurrency = new Currency();
                $newCurrency->attributes = [
                    'cb_id' => $cbId,
                    'iso_num_code' => $data['ISO_Num_Code'],
                    'iso_char_code' => $data['ISO_Char_Code'],
                    'nominal' => $data['Nominal'],
                    'name' => $data['Name'],
                    'en_name' => $data['EngName']
                ];
                if (!$newCurrency->save()) {
                    $firstError = reset($newCurrency->getFirstErrors());
                    $message = Yii::t(
                        'app', 'Unable save currency with Bank internal ID "{cb_id}": {error}',
                        ['cb_id' => $cbId, 'error' => $firstError]
                    );
                    $warningLog[] = $message;
                    Yii::warning($message, 'currencies');
                    if (!$hasWarnings) {
                        $hasWarnings = true;
                    }
                } else {
                    $newCount++;
                }
            }

            if ($newCount == 0 && !$hasWarnings) {
                $message = Yii::t('app', 'No currencies added. You have actual currencies list');
            } else {
                $message = Yii::t('app', '{new_count} currency(-ies) added', ['new_count' => $newCount]);
            }

            $successLog[] = $message;
            Yii::info($message, 'currencies');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $errorLog[] = $message;
            Yii::error($message, 'currencies');
        }

        return $log;
    }
}
