<?php
namespace GlpiPlugin\Glpiautoassign;

class Config {
    public static function configTable(): string { global $DB; return $DB->prefix . 'plugin_glpiautoassign_config'; }
    public static function rrTable(): string { global $DB; return $DB->prefix . 'plugin_glpiautoassign_rr'; }

    public static function install(): bool {
        global $DB;
        $c=self::configTable(); $r=self::rrTable();
        if (!$DB->doQuery("CREATE TABLE IF NOT EXISTS `$c` (`id` int unsigned NOT NULL AUTO_INCREMENT, `mode` varchar(32) NOT NULL DEFAULT 'least_loaded', `max_active_tickets` int unsigned NOT NULL DEFAULT 0, `include_waiting` tinyint(1) NOT NULL DEFAULT 0, `only_if_no_technician` tinyint(1) NOT NULL DEFAULT 1, `date_mod` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")) return false;
        if (!$DB->doQuery("CREATE TABLE IF NOT EXISTS `$r` (`groups_id` int unsigned NOT NULL, `users_id` int unsigned NOT NULL, `date_mod` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`groups_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")) return false;
        if (!self::getConfig()) $DB->doQuery("INSERT INTO `$c` (`mode`,`max_active_tickets`,`include_waiting`,`only_if_no_technician`) VALUES ('least_loaded',0,0,1)");
        return true;
    }
    public static function uninstall(): bool {
        global $DB; $DB->doQuery("DROP TABLE IF EXISTS `".self::rrTable()."`"); $DB->doQuery("DROP TABLE IF EXISTS `".self::configTable()."`"); return true;
    }
    public static function getConfig(): ?array {
        global $DB; $res=$DB->request(['FROM'=>self::configTable(),'LIMIT'=>1]); foreach($res as $row) return $row; return null;
    }
    public static function saveConfig(array $input): bool {
        global $DB; $table=self::configTable(); $old=self::getConfig();
        $data=['mode'=>in_array($input['mode']??'', ['least_loaded','round_robin'], true)?$input['mode']:'least_loaded','max_active_tickets'=>max(0,(int)($input['max_active_tickets']??0)),'include_waiting'=>!empty($input['include_waiting'])?1:0,'only_if_no_technician'=>!empty($input['only_if_no_technician'])?1:0];
        return $old ? (bool)$DB->update($table,$data,['id'=>(int)$old['id']]) : (bool)$DB->insert($table,$data);
    }
}
