<?php

namespace metalguardian\fileProcessor\controllers;

use metalguardian\fileProcessor\helpers\FPM;
use metalguardian\fileProcessor\Module;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\imagine\Image;
use yii\web\NotFoundHttpException;

/**
 * Class ImageController
 *
 * @package metalguardian\fileProcessor\controllers
 */
class FileController extends \yii\web\Controller
{
    /**
     * @param $sub
     * @param $module
     * @param $size
     * @param $id
     * @param $baseName
     * @param $extension
     *
     * @return int
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionProcess($sub, $module, $size, $id, $baseName, $extension)
    {
        $fileName = FPM::getOriginalDirectory($id) . DIRECTORY_SEPARATOR . FPM::getOriginalFileName($id, $baseName, $extension);

        if (file_exists($fileName)) {
            $data = FPM::transfer()->getData($id);

            if ($baseName !== $data->base_name) {
                throw new NotFoundHttpException(Module::t('exception', 'File not found'));
            }

            $config = isset(FPM::m()->imageSections[$module][$size]) ? FPM::m()->imageSections[$module][$size] : null;
            if (!is_array($config)) {
                throw new NotFoundHttpException(Module::t('exception', 'Incorrect request'));
            }

            $thumbnailFile = FPM::getThumbnailDirectory($id, $module, $size) . DIRECTORY_SEPARATOR . FPM::getThumbnailFileName($id, $baseName, $extension);
            FileHelper::createDirectory(FPM::getThumbnailDirectory($id, $module, $size));

            $imagine = \metalguardian\fileProcessor\components\Image::getImagine();
            $file = $imagine->open($fileName);
            if (isset($config['do'])) {
                switch($config['do'])
                {
                    case 'adaptiveResize':
                        $filter = new \Imagine\Filter\Basic\WebOptimization(null, ['quality' => 100]);
                        $file = $filter->apply($file);
                        //$file->save($thumbnailFile, ['quality' => 100]);
                        $file->show($extension, ['quality' => 100]);
                        break;
                    case 'resize':

                        break;
                    case 'resizeCanvas':

                        break;
                    case 'copy':
                        //copy($fileName, $thumbnailFile);
                        symlink($fileName, $thumbnailFile);

                        header('Content-type: ' . FileHelper::getMimeType($fileName));

                        readfile($thumbnailFile);
                        break;
                    default:
                        throw new InvalidConfigException(Module::t('exception', 'Action is incorrect'));
                        break;
                }
            } else {
                throw new InvalidConfigException(Module::t('exception', 'Action not defined'));
            }
        } else {
            throw new NotFoundHttpException(Module::t('exception', 'File not found'));
        }
    }
}
