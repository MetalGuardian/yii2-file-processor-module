<?php
/**
 * Author: metalguardian
 * Email: metalguardian
 */

namespace metalguardian\fileProcessor\components;

use metalguardian\fileProcessor\helpers\FPM;

/**
 * Class ThumbnailCache
 *
 * @package metalguardian\fileProcessor\components
 */
class ThumbnailCache extends \yii\base\Component
{
    /**
     * @param $id
     *
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function delete($id)
    {
        $model = FPM::transfer()->getData($id);

        $config = FPM::m()->imageSections;
        foreach ($config as $moduleKey => $module) {
            foreach ($module as $sizeKey => $size) {
                $fileName = FPM::getThumbnailDirectory($id, $moduleKey, $sizeKey)
                    . FPM::getThumbnailFileName($id, $model->base_name, $model->extension);

                if (is_file($fileName)) {
                    unlink($fileName);
                }
            }
        }
    }
}
