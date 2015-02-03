<?php

namespace metalguardian\fileProcessor\controllers;

use Imagine\Image\ManipulatorInterface;
use metalguardian\fileProcessor\components\Image;
use metalguardian\fileProcessor\helpers\FPM;
use metalguardian\fileProcessor\Module;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
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
        if ($sub !== floor($id / FPM::getFilesPerDirectory())) {
            throw new NotFoundHttpException(Module::t('exception', 'Wrong generated link'));
        }

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

            if (isset($config['action'])) {
                switch($config['action'])
                {
                    case 'adaptiveThumbnail':
                        Image::thumbnail($fileName, $config['width'], $config['height'])
                            ->save($thumbnailFile)
                            ->show($extension);
                        break;
                    case 'thumbnail':
                        Image::thumbnail($fileName, $config['width'], $config['height'], ManipulatorInterface::THUMBNAIL_INSET)
                            ->save($thumbnailFile)
                            ->show($extension);
                        break;
                    case 'crop':
                        Image::crop($fileName, $config['width'], $config['height'], $config['startX'], $config['startY'])
                            ->save($thumbnailFile)
                            ->show($extension);
                        break;
                    case 'canvasThumbnail':
                        Image::canvasThumbnail($fileName, $config['width'], $config['height'])
                            ->save($thumbnailFile)
                            ->show($extension);
                        break;
                    case 'frame':
                        Image::frame($fileName, 50, 'F00')
                            ->save($thumbnailFile)
                            ->show($extension);
                        break;
                    case 'copy':
                        if (FPM::m()->symLink) {
                            symlink($fileName, $thumbnailFile);
                        } else {
                            copy($fileName, $thumbnailFile);
                        }
                        \Yii::$app->response->sendFile($thumbnailFile);
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
