<?php

include '../../../inc/includes.php';

Session::checkRight('config', UPDATE);
require_once __DIR__ . '/../hook.php';

if (isset($_POST['update'])) {
    Session::checkCSRF();
    PluginGlpiAutoassignConfig::saveConfig($_POST);
    Html::back();
}

Html::header('GLPI AutoAssign', $_SERVER['PHP_SELF'], 'config', 'plugins');
PluginGlpiAutoassignConfig::showConfigForm();
Html::footer();
