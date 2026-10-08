<?php

use Glpi\Plugin\Hooks;

function plugin_init_glpi_autoassign(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['glpi_autoassign'] = true;
    $PLUGIN_HOOKS[Hooks::ITEM_ADD]['glpi_autoassign'] = [
        'Ticket' => [PluginGlpiAutoassignAssignment::class, 'onTicketChange'],
    ];
    $PLUGIN_HOOKS[Hooks::ITEM_UPDATE]['glpi_autoassign'] = [
        'Ticket' => [PluginGlpiAutoassignAssignment::class, 'onTicketChange'],
    ];
    $PLUGIN_HOOKS['config_page']['glpi_autoassign'] = 'front/config.form.php';
}

function plugin_version_glpi_autoassign(): array
{
    return [
        'name'           => 'GLPI AutoAssign',
        'version'        => '0.1.0',
        'author'         => 'Shkila25',
        'license'        => 'GPLv3+',
        'homepage'       => 'https://github.com/Shkila25/glpi-autoassign',
        'minGlpiVersion' => '12.0.0',
    ];
}

function plugin_glpi_autoassign_check_prerequisites(): bool
{
    return version_compare(GLPI_VERSION, '12.0.0', '>=');
}

function plugin_glpi_autoassign_check_config(bool $verbose = false): bool
{
    return true;
}

function plugin_glpi_autoassign_install(): bool
{
    return PluginGlpiAutoassignConfig::install();
}

function plugin_glpi_autoassign_uninstall(): bool
{
    return PluginGlpiAutoassignConfig::uninstall();
}
