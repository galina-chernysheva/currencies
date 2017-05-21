<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use common\helpers\RatesLoader;

/**
 * CurrencyrateController implements the load of rates action.
 */
class CurrencyrateController extends Controller
{
    /**
     * Loads rates from Bank of Russia site.
     * The browser will be redirected to the referrer or Home page after loading.
     * @return mixed
     */
    public function actionLoad()
    {
        $log = RatesLoader::load();
        foreach ($log as $type => $messages) {
            if (!empty($messages)) {
                Yii::$app->session->setFlash($type, implode('<br/>', $messages));
            }
        }

        $url = Yii::$app->request->referrer;
        if (empty($url)) {
            $url = Yii::$app->getHomeUrl();
        }
        return $this->redirect($url);
    }
}
