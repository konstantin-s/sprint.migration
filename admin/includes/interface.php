<?php

use Sprint\Migration\Locale;

$APPLICATION->SetTitle(Locale::getMessage('TITLE'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    CUtil::JSPostUnescape();
}

if (isset($_REQUEST['schema'])) {
    $versionConfig = new Sprint\Migration\VersionConfig($_REQUEST['schema']);
} elseif (isset($_REQUEST['config'])) {
    $versionConfig = new Sprint\Migration\VersionConfig($_REQUEST['config']);
} else {
    $versionConfig = new Sprint\Migration\VersionConfig();
}

if ($versionConfig->getVal('show_admin_interface')) {
    if (isset($_REQUEST['schema'])) {
        include __DIR__ . '/../steps/schema_list.php';
        include __DIR__ . '/../steps/schema_export.php';
        include __DIR__ . '/../steps/schema_import.php';
    } else {
        include __DIR__ . '/../steps/migration_execute.php';
        include __DIR__ . '/../steps/migration_list.php';
        include __DIR__ . '/../steps/migration_status.php';
        include __DIR__ . '/../steps/migration_create.php';
    }
}

/** @noinspection PhpIncludeInspection */
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
CUtil::InitJSCore(["jquery"]);

if ($versionConfig->getVal('show_admin_interface')) {
    if (isset($_REQUEST['schema'])) {
        include __DIR__ . '/../includes/schema.php';
        include __DIR__ . '/../assets/schema.php';
    } else {
        include __DIR__ . '/../includes/version.php';
        include __DIR__ . '/../assets/version.php';
    }
}

$sperrors = [];
if (!$versionConfig->getVal('show_admin_interface')) {
    $sperrors[] = Locale::getMessage('ADMIN_INTERFACE_HIDDEN');
}

include __DIR__ . '/../includes/errors.php';
include __DIR__ . '/../includes/help.php';
include __DIR__ . '/../assets/style.php';