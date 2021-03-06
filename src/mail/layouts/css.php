<?php
/**
 * @var $this \yii\web\View view component instance
 */
?>
<style type="text/css">
    /*<![CDATA[*/
    /* everything in this node will be inlined */

    /* ==== Page Styles ==== */

    body, #backgroundTable {
        background-color: #f2f2f2; /* Use body to determine background color */
        font-family: sans-serif;
    }

    #templateTable {
        width: 600px;
        background-color: #ffffff;
        -webkit-font-smoothing: antialiased;
    }

    h1, .h1, h2, .h2, h3, .h3, h4, .h4, h5, .h5, h6, .h6 {
        color: #444444;
        display: block;
        font-family: sans-serif;
        font-weight: bold;
        line-height: 100%;
        margin-top: 0;
        margin-right: 0;
        margin-bottom: 10px;
        margin-left: 0;
        text-align: left;
    }

    h1, .h1 {
        font-size: 26px;
    }

    h2, .h2 {
        font-size: 20px;
    }

    h3, .h3 {
        font-size: 15px;
    }

    h4, .h4 {
        font-size: 13px;
    }

    h5, .h5 {
        font-size: 11px;
    }

    h6, .h6 {
        font-size: 10px;
    }

    /* ==== Header Styles ==== */

    #headerTable {
        background-color: #f2f2f2;
        color: #444444;
        font-family: sans-serif;
        font-size: 10px;
        line-height: 120%;
        text-align: right;
        border-collapse: separate !important;
        padding-right: 30px;
    }

    #headerTable a:link, #headerTable a:visited, /* Yahoo! Mail Override */
    #headerTable a .yshortcuts /* Yahoo! Mail Override */
    {
        font-weight: normal;
        text-decoration: underline;
    }

    /* ==== Template Wrapper Styles ==== */

    #contentCell {
        padding: 10px 20px;
        background-color: #f2f2f2;
    }

    #contentTableOuter {
        border-collapse: separate !important;

        background-color: #ffffff;

        box-shadow: 0 1px rgba(0, 0, 0, 0.1);

        padding: 30px;
    }

    #contentTableInner {
        width: 600px;
    }

    /* ==== Body Styles ==== */

    .bodyContent {
        color: #444444;
        font-family: sans-serif;
        font-size: 15px;
        line-height: 150%;
        text-align: left;
    }

    /* ==== Column Styles ==== */

    table.columnContentTable {
        border-collapse: separate !important;
        border-spacing: 0;

        background-color: #ffffff;
    }

    td[class~="columnContent"] {
        color: #444444;
        font-family: sans-serif;
        font-size: 15px;
        line-height: 120%;
        padding-top: 20px;
        padding-right: 20px;
        padding-bottom: 20px;
        padding-left: 20px;
    }

    /* ==== Footer Styles ==== */

    #footerTable {
        background-color: #f2f2f2;
    }

    #footerTable a {
        color: #999999;
    }

    #footerTable {
        color: #999999;
        font-family: sans-serif;
        font-size: 12px;
        line-height: 120%;
        padding-top: 20px;
        padding-right: 20px;
        padding-bottom: 20px;
        padding-left: 20px;
        text-align: center;
    }

    #footerTable a:link, #footerTable a:visited, /* Yahoo! Mail Override */
    #footerTable a .yshortcuts /* Yahoo! Mail Override */
    {
        font-weight: normal;
        text-decoration: underline;
    }

    /* ==== Standard Resets ==== */
    .ExternalClass {
        width: 100%;
    }

    /* Force Hotmail to display emails at full width */
    .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {
        line-height: 100%;
    }

    /* Force Hotmail to display normal line spacing */
    body, table, td, p, a, li, blockquote {
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
    }

    /* Prevent WebKit and Windows mobile changing default text sizes */
    table, td {
        mso-table-lspace: 0;
        mso-table-rspace: 0;
    }

    /* Remove spacing between tables in Outlook 2007 and up */
    img {
        vertical-align: bottom;
        -ms-interpolation-mode: bicubic;
    }

    /* Allow smoother rendering of resized image in Internet Explorer */

    /* Reset Styles */
    body {
        margin: 0;
        padding: 0;
    }

    table {
        border-collapse: collapse !important;
    }

    body, #backgroundTable, #bodyCell {
        height: 100% !important;
        margin: 0;
        padding: 0;
        width: 100% !important;
    }

    a:link, a:visited {
        border-bottom: none;
    }

    /* iOS automatically adds a link to addresses */
    /* Style the footer with the same color as the footer text */
    #footer a {
        color: #999999;;
        -webkit-text-size-adjust: none;
        text-decoration: underline;
        font-weight: normal
    }

    /*]]>*/
</style>

<style type="text/css">
    /*<![CDATA[*/
    /* ==== Mobile Styles ==== */

    /* Constrain email width for small screens */
    @media screen and (max-width: 650px) {
        table[id="backgroundTable"] {
            width: 95% !important;
        }

        table[id="templateTable"] {
            max-width: 600px !important;
            width: 100% !important;
        }

        table[id="contentTableInner"] {
            max-width: 600px !important;
            width: 100% !important;
        }

        /* Makes image expand to take 100% of width*/
        img {
            width: 100% !important;
            height: auto !important;
        }

        #contentCell {
            padding: 10px 10px !important;
        }

        #headerTable {
            padding-right: 15.0px !important;
        }

        #contentTableOuter {
            padding: 15.0px !important;
        }
    }

    @media only screen and (max-width: 480px) {
        /* ==== Client-Specific Mobile Styles ==== */
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: none !important;
        }

        /* Prevent Webkit platforms from changing default text sizes */
        body {
            width: 100% !important;
            min-width: 100% !important;
        }

        /* Prevent iOS Mail from adding padding to the body */
        /* ==== Mobile Reset Styles ==== */
        td[id="bodyCell"] {
            padding: 10px !important;
        }

        /* ==== Mobile Template Styles ==== */
        table[id="templateTable"] {
            max-width: 600px !important;
            width: 100% !important;
        }

        table[id="contentTableInner"] {
            max-width: 600px !important;
            width: 100% !important;
        }

        /* ==== Image Alignment Styles ==== */
        h1, .h1 {
            font-size: 26px !important;
            line-height: 125% !important;
        }

        h2, .h2 {
            font-size: 20px !important;
            line-height: 125% !important;
        }

        h3, .h3 {
            font-size: 15px !important;
            line-height: 125% !important;
        }

        h4, .h4 {
            font-size: 13px !important;
            line-height: 125% !important;
        }

        h5, .h5 {
            font-size: 11px !important;
            line-height: 125% !important;
        }

        h6, .h6 {
            font-size: 10px !important;
            line-height: 125% !important;
        }

        .hide {
            display: none !important;
        }

        /* Hide to save space */
        /* ==== Body Styles ==== */
        td[class="bodyContent"] {
            font-size: 16px !important;
            line-height: 145% !important;
        }

        /* ==== Footer Styles ==== */
        td[id="footerTable"] {
            padding-left: 0px !important;
            padding-right: 0px !important;
            font-size: 12px !important;
            line-height: 145% !important;
        }

        /* ==== Image Alignment Styles ==== */
        table[class="alignImageTable"] {
            width: 100% !important;
        }

        td[class="imageTableTop"] {
            display: none !important;
            /*padding-top: 10px !important;*/
        }

        td[class="imageTableRight"] {
            display: none !important;
        }

        td[class="imageTableBottom"] {
            padding-bottom: 10px !important;
        }

        td[class="imageTableLeft"] {
            display: none !important;
        }

        /* ==== Column Styles ==== */
        td[class~="column"] {
            display: block !important;
            width: 100% !important;
            padding-top: 0 !important;
            padding-right: 0 !important;
            padding-bottom: 0 !important;
            padding-left: 0 !important;
        }

        td[class~=columnContent] {
            font-size: 14px !important;
            line-height: 145% !important;

            padding-top: 10px !important;
            padding-right: 10px !important;
            padding-bottom: 10px !important;
            padding-left: 10px !important;
        }

        #contentCell {
            padding: 10px 0px !important;
        }

        #headerTable {
            padding-right: 15.0px !important;
        }

        #contentTableOuter {
            padding: 15.0px !important;
        }
    }

    /*]]>*/
</style>
