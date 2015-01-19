<?php
/**
 * Author: metal
 * Email: metal
 */

namespace metalguardian\fileProcessor\components;

use metalguardian\fileProcessor\helpers\FPM;
use yii\web\UrlManager;
use yii\web\Request;

/**
 * Class FPMUrlRule
 *
 * @package components
 */
class FPMUrlRule extends \yii\base\Object implements \yii\web\UrlRuleInterface
{
    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        \yii\helpers\VarDumper::dump($manager, 10, true);
        exit();

        if ($request->getMethod() !== 'GET') {
            return false;
        }

        $pathInfo = $request->getPathInfo();

        if (!preg_match('#^' . FPM::m()->thumbnailDirectory . '\/#', $pathInfo)) {
            return false;
        }

        if (!preg_match($this->getPattern(), $pathInfo, $matches)) {
            return false;
        }

        $params = [];
        foreach ($matches as $name => $value) {
            if (isset($this->_routeParams[$name])) {
                $tr[$this->_routeParams[$name]] = $value;
                unset($params[$name]);
            } elseif (isset($this->_paramRules[$name])) {
                $params[$name] = $value;
            }
        }

        $route = 'fileProcessor/image/process';

        return [$route, $params];
    }

    public function getPattern()
    {
        return '#^' . FPM::m()->thumbnailDirectory . '\/(\d+)\/(\w+)_(\w+)\/(\d+)-(.*)\.(png|gif|jpg|jpeg|PNG|GIF|JPG|JPEG)$#';
    }

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        return false;
    }
}
