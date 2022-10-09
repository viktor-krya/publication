<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    "Akticom_Web_Services",
    array(
        "Akticom_Log\\Akticom_Web_log" => "/bitrix/web-class-log/class.php",
    )
);