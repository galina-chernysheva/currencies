<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use common\helpers\CurrenciesLoader;

class CurrenciesController extends Controller
{
    /**
     * Loads currencies from Bank of Russia site.
     */
    public function actionIndex()
    {
        $labels = [
            'warning' => $this->ansiFormat('Warning: ', [Console::FG_YELLOW, Console::BOLD]),
            'error' => $this->ansiFormat('Error: ', [Console::FG_RED, Console::BOLD]),
            'success' => $this->ansiFormat('Success: ', [Console::FG_GREEN, Console::BOLD])
        ];

        $log = CurrenciesLoader::load();

        foreach ($log as $type => $messages) {
            if (!empty($messages)) {
                $this->stdout("\n");
                foreach ($messages as $message) {
                    $this->stdout($labels[$type] . $message . "\n");
                }
                $this->stdout("\n");
            }
        }

        $exitCode = empty($log['success']) ? static::EXIT_CODE_ERROR : static::EXIT_CODE_NORMAL;
        return $exitCode;
    }
}
