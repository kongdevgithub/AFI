<?php

// Define application aliases
Yii::setAlias('@app', __DIR__.'/..');
Yii::setAlias('@root', '@app/..');
Yii::setAlias('@runtime', '@root/runtime');
Yii::setAlias('@web', '@root/web');
Yii::setAlias('@webroot', '@root//web');

// Load $merge configuration files
$applicationType = (empty($applicationType) && php_sapi_name() == 'cli') ? 'console' : 'web';
$env = YII_ENV;
$configDir = __DIR__;

return \yii\helpers\ArrayHelper::merge(
    require("{$configDir}/common.php"),
    require("{$configDir}/{$applicationType}.php"),
    (file_exists("{$configDir}/common-{$env}.php")) ? require("{$configDir}/common-{$env}.php") : [],
    (file_exists("{$configDir}/{$applicationType}-{$env}.php")) ? require("{$configDir}/{$applicationType}-{$env}.php") : [],
    (file_exists(getenv('APP_CONFIG_FILE'))) ? require(getenv('APP_CONFIG_FILE')) : []
);
