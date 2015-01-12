Yii2 file processor module
==========================
Extension to upload and store files

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist metalguardian/yii2-file-processor-module "*"
```

or add

```
"metalguardian/yii2-file-processor-module": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \metalguardian\src\AutoloadExample::widget(); ?>
./yii migrate --migrationPath=@backend/runtime/tmp-extensions/yii2-file-processor-module/src/migrations
```
