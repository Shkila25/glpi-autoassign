<?php
define('PLUGIN_GLPIAUTOASSIGN_VERSION', '0.1.0');
define('PLUGIN_GLPIAUTOASSIGN_MIN_GLPI_VERSION', '12.0.0');

function plugin_init_glpiautoassign(): void {
    global $PLUGIN_HOOKS;
    $PLUGIN_HOOKS['csrf_compliant']['glpiautoassign'] = true;
    $PLUGIN_HOOKS['item_add']['glpiautoassign'] = ['Ticket' => ['GlpiPlugin\\Glpiautoassign\\Assignment', 'onTicketChange']];
    $PLUGIN_HOOKS['item_update']['glpiautoassign'] = ['Ticket' => ['GlpiPlugin\\Glpiautoassign\\Assignment', 'onTicketChange']];
    $PLUGIN_HOOKS['config_page']['glpiautoassign'] = 'front/config.form.php';
}
function plugin_version_glpiautoassign(): array {
    return ['name'=>'GLPI AutoAssign','version'=>PLUGIN_GLPIAUTOASSIGN_VERSION,'author'=>'Shkila25','license'=>'GPL-3.0-or-later','homepage'=>'https://github.com/Shkila25/glpi-autoassign','minGlpiVersion'=>PLUGIN_GLPIAUTOASSIGN_MIN_GLPI_VERSION];
}
function plugin_glpiautoassign_check_prerequisites(): bool { return version_compare(GLPI_VERSION, PLUGIN_GLPIAUTOASSIGN_MIN_GLPI_VERSION, '>='); }
function plugin_glpiautoassign_check_config(bool $verbose = false): bool { return true; }
function plugin_glpiautoassign_install(): bool { return \GlpiPlugin\Glpiautoassign\Config::install(); }
function plugin_glpiautoassign_uninstall(): bool { return \GlpiPlugin\Glpiautoassign\Config::uninstall(); }
