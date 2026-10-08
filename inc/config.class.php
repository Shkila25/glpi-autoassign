<?php

class PluginGlpiAutoassignConfig extends CommonDBTM
{
    public static function getTable($classname = null)
    {
        return 'glpi_plugin_glpi_autoassign_configs';
    }

    public static function install(): bool
    {
        global $DB;
        $table = self::getTable();

        if (!$DB->tableExists($table)) {
            $query = "CREATE TABLE `$table` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `mode` varchar(32) NOT NULL DEFAULT 'least_loaded',
                `max_active_tickets` int unsigned NOT NULL DEFAULT 0,
                `include_waiting` tinyint(1) NOT NULL DEFAULT 0,
                `only_if_no_technician` tinyint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            if (!$DB->query($query)) {
                return false;
            }
        }

        $obj = new self();
        if (!$obj->find([], '', 1)) {
            $obj->add([
                'mode' => 'least_loaded',
                'max_active_tickets' => 0,
                'include_waiting' => 0,
                'only_if_no_technician' => 1,
            ]);
        }
        return true;
    }

    public static function uninstall(): bool
    {
        global $DB;
        return $DB->query('DROP TABLE IF EXISTS `' . self::getTable() . '`');
    }

    public static function getConfig(): array
    {
        $obj = new self();
        $rows = $obj->find([], '', 1);
        if ($rows) {
            return reset($rows);
        }
        return [
            'mode' => 'least_loaded',
            'max_active_tickets' => 0,
            'include_waiting' => 0,
            'only_if_no_technician' => 1,
        ];
    }

    public static function saveConfig(array $input): bool
    {
        $obj = new self();
        $rows = $obj->find([], '', 1);
        $data = [
            'mode' => in_array($input['mode'] ?? '', ['least_loaded', 'round_robin'], true)
                ? $input['mode'] : 'least_loaded',
            'max_active_tickets' => max(0, (int)($input['max_active_tickets'] ?? 0)),
            'include_waiting' => !empty($input['include_waiting']) ? 1 : 0,
            'only_if_no_technician' => !empty($input['only_if_no_technician']) ? 1 : 0,
        ];
        if ($rows) {
            $data['id'] = (int)array_key_first($rows);
            return (bool)$obj->update($data);
        }
        return (bool)$obj->add($data);
    }

    public static function showConfigForm(): void
    {
        $config = self::getConfig();
        echo '<div class="center"><form method="post">';
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        echo '<table class="tab_cadre_fixe">';
        echo '<tr><th colspan="2">GLPI AutoAssign</th></tr>';
        echo '<tr><td>Метод распределения</td><td>';
        Dropdown::showFromArray('mode', [
            'least_loaded' => 'Наименее загруженный',
            'round_robin' => 'Round-Robin',
        ], ['value' => $config['mode']]);
        echo '</td></tr>';
        echo '<tr><td>Максимум активных заявок на техника (0 = без лимита)</td><td>';
        echo Html::input('max_active_tickets', ['value' => (int)$config['max_active_tickets'], 'size' => 8]);
        echo '</td></tr>';
        echo '<tr><td>Учитывать заявки в ожидании</td><td>';
        Dropdown::showYesNo('include_waiting', (int)$config['include_waiting']);
        echo '</td></tr>';
        echo '<tr><td>Не менять уже назначенного техника</td><td>';
        Dropdown::showYesNo('only_if_no_technician', (int)$config['only_if_no_technician']);
        echo '</td></tr>';
        echo '<tr><td colspan="2" class="center">';
        echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
        echo '</td></tr></table></form></div>';
    }
}
