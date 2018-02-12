<?php
/**
 * @var $this \yii\web\View view component instance
 * @var $message \yii\mail\MessageInterface the message being composed
 * @var $content string main view render result
 */

use yii\helpers\Html;

?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width"/>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>"/>
    <?php $this->head() ?>
    <?= $this->render('css') ?>
    <!-- http://www.emailon@cid.com/blog/details/C13/ensure_that_your_entire_email_is_rendered_by_default_in_the_iphone_ipad -->
    <!--                                                                                                                     -->
    <!--                                                                                                                     -->
    <!--                                                                                                                     -->
    <!--                                                                                                                     -->
    <!--                                                                                                                     -->
    <!--                                                                                                                     -->
    <!--                                                                                                                     -->
    <!--                                                                                                                     -->
    <!--                                                                                                                     -->
    <!--                                                                                                                     -->
    <!--                                                 Extra White Space!                                                  -->
    <!--                                                                                                                     -->
    <!-- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -->

    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="" style="background-color:#f2f2f2; font-family:sans-serif; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; height:100% !important; margin:0; padding:0; width:100% !important" leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" bgcolor="#f2f2f2" height="100% !important" width="100% !important">
<?php $this->beginBody() ?>

<!-- Preview text (text which appears right after subject) -->

<!--  The  backgroundTable table manages the color of the background and then the templateTable maintains the body of
the email template, including preheader & footer. This is the only table you set the width of to, everything else is set to
100% and in the CSS above. Having the width here within the table is just a small win for Lotus Notes. -->

<!-- Begin backgroundTable -->
<table align="center" bgcolor="#f2f2f2" border="0" cellpadding="0" cellspacing="0" height="100% !important" width="100% !important" id="backgroundTable" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0; border-collapse:collapse !important; background-color:#f2f2f2; font-family:sans-serif; height:100% !important; margin:0; padding:0; width:100% !important">
    <tbody>
    <tr>
        <td align="center" valign="top" id="bodyCell" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0; height:100% !important; margin:0; padding:0; width:100% !important" height="100% !important" width="100% !important">
            <!-- When nesting tables within a TD, align center keeps it well, centered. -->
            <!-- Begin Template Container -->
            <!-- This holds everything together in a nice container -->
            <table border="0" cellpadding="0" cellspacing="0" id="templateTable" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0; border-collapse:collapse !important; width:600px; background-color:#ffffff; -webkit-font-smoothing:antialiased" width="600" bgcolor="#ffffff">
                <tbody>
                <tr>
                    <td align="center" valign="top" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0">
                        <!-- Begin Template Preheader -->
                        <div class="header-container-wrapper">
                        </div>
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" id="headerTable" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0; background-color:#f2f2f2; color:#444444; font-family:sans-serif; font-size:10px; line-height:120%; text-align:right; border-collapse:separate !important; padding-right:30px" bgcolor="#f2f2f2" align="right">
                            <tbody>
                            <tr>
                                <td align="left" valign="top" class="bodyContent" width="100%" colspan="12" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0; color:#444444; font-family:sans-serif; font-size:14px; line-height:150%; text-align:left">
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%" class="templateColumnWrapper" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0; border-collapse:collapse !important">
                                        <tbody>
                                        <tr>
                                            <td valign="top" colspan="12" width="100.0%" class=" column" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0; width:100.0%; text-align:left; padding:0; font-family:sans-serif; font-size:14px; line-height:1.5em; color:#444444" align="left">
                                                <div class="widget-span widget-type-email_view_as_web_page " style="" data-widget-type="email_view_as_web_page">
                                                    <div style="padding-top: 14px; font-family: Geneva, Verdana, Arial, Helvetica, sans-serif; text-align: right; font-size: 9px; line-height: 1.34em; color: #999999">
                                                        <?php if (isset($this->params['htmlUrl'])) { ?>
                                                            Not rendering correctly? View this email as a web page
                                                            <a style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; color:#999999; text-decoration:underline; white-space:nowrap" data-viewaswebpage="true" href="<?= $this->params['htmlUrl'] ?>" target="_blank">here</a>.
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <!-- End Template Preheader -->
                    </td>
                </tr>
                <tr>
                    <td align="center" valign="top" id="contentCell" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0; padding:10px 20px; background-color:#f2f2f2" bgcolor="#f2f2f2">
                        <!-- Begin Template Wrapper -->
                        <!-- This separates the preheader which usually contains the "open in browser, etc" content
                        from the actual body of the email. Can alternatively contain the footer too, but I choose not
                        to so that it stays outside of the border. -->
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" id="contentTableOuter" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0; border-collapse:separate !important; background-color:#ffffff; box-shadow:0px 1px rgba(0, 0, 0, 0.1); padding:5px; border:1px solid #cccccc; border-bottom:1px solid #acacac;font-size: 14px;" bgcolor="#ffffff">
                            <tbody>
                            <tr>
                                <td align="left" valign="top" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0">
                                    <?= isset($this->params['headerImage']) ? Html::img($this->params['headerImage']) . '<br>' : '' ?>
                                    <div style="padding: 5px 14px; max-width: 600px; margin: 0 auto; display: block;">
                                        <?= $content ?>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <!-- End Template Wrapper -->
                    </td>
                </tr>
                <tr>
                    <td align="center" valign="top" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0">
                        <!-- Begin Template Footer -->
                        <div class="footer-container-wrapper">
                        </div>
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" id="footerTable" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0; border-collapse:collapse !important; background-color:#f2f2f2; color:#999999; font-family:sans-serif; font-size:12px; line-height:120%; padding-top:20px; padding-right:20px; padding-bottom:20px; padding-left:20px; text-align:center" bgcolor="#f2f2f2" align="center">
                            <tbody>
                            <tr>
                                <td align="left" valign="top" class="bodyContent" width="100%" colspan="12" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0; color:#444444; font-family:sans-serif; font-size:14px; line-height:150%; text-align:left">
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%" class="templateColumnWrapper" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0; border-collapse:collapse !important">
                                        <tbody>
                                        <tr>
                                            <td valign="top" colspan="12" width="100.0%" class=" column" style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0; width:100.0%; text-align:left; padding:0; font-family:sans-serif; font-size:14px; line-height:1.5em; color:#444444" align="left">
                                                <div class="widget-span widget-type-email_can_spam " style="" data-widget-type="email_can_spam">
                                                    <p id="footer" style="margin-bottom: 1em; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; font-family:Geneva, Verdana, Arial, Helvetica, sans-serif; text-align:center; font-size:12px; line-height:1.34em; color:#999999; display:block" align="center">
                                                        AFI Branding&nbsp;&nbsp;33 Lakewood Blvd&nbsp;&nbsp;Carrum Downs&nbsp;Vic&nbsp;&nbsp;3201
                                                        <?php if (isset($this->params['unsubscribeUrl'])) { ?>
                                                            <br><br>
                                                            <a data-unsubscribe="true" href="<?= $this->params['unsubscribeUrl'] ?>" style="-ms-text-size-adjust:100%; -webkit-text-size-adjust:none; font-weight:normal; text-decoration:underline; whitespace:nowrap; color:#999999; font-size: smaller;" target="_blank">Unsubscribe from these emails</a>
                                                        <?php } ?>
                                                    </p>

                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; mso-table-lspace:0; mso-table-rspace:0"></td>
                            </tr>
                            </tbody>
                        </table>
                        <!-- End Template Footer -->
                    </td>
                </tr>
                </tbody>
            </table>
            <!-- End Template Container -->
        </td>
    </tr>
    </tbody>
</table>
<!-- End backgroundTable -->

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
