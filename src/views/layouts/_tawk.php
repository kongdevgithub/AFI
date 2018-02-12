<?php
/**
 * @var $this \yii\web\View
 */

use bedezign\yii2\audit\Audit;
use yii\helpers\Html;
use yii\helpers\Url;

return;

$auditEntry = Audit::getInstance()->getEntry();
?>

<!--Start of Tawk.to Script-->
<script type="text/javascript">
    var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
    <?php if (!Yii::$app->user->isGuest) { ?>
    Tawk_API.onLoad = function () {
        Tawk_API.setAttributes({
            'name': '<?= Html::encode(Yii::$app->user->identity->getLabel()) ?>',
            'email': '<?= Yii::$app->user->identity->email ?>',
            'audit': '<?= $auditEntry ? Url::to(['/audit/entry/view', 'id' => $auditEntry->id], 'https') : '' ?>',
            'hash': '<?= hash_hmac('sha256', Yii::$app->user->identity->email, 'a3bfab5a6f1bb01e0f2352e38e9ae162a6275e77'); ?>'
        }, function (error) {
        });
    };
    <?php }?>
    (function () {
        var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = 'https://embed.tawk.to/5909b3534ac4446b24a6cff8/default';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();
</script>
<!--End of Tawk.to Script-->
