<?php

class PluginGlpiAutoassignAssignment
{
    private static array $processing = [];

    public static function onTicketChange(Ticket $ticket): void
    {
        $ticketId = (int)$ticket->getID();
        if ($ticketId <= 0 || isset(self::$processing[$ticketId])) {
            return;
        }
        self::$processing[$ticketId] = true;
        try {
            self::assign($ticketId);
        } finally {
            unset(self::$processing[$ticketId]);
        }
    }

    private static function assign(int $ticketId): void
    {
        $groupId = self::getAssignedGroupId($ticketId);
        if ($groupId <= 0) {
            return;
        }

        $config = PluginGlpiAutoassignConfig::getConfig();
        if ((int)$config['only_if_no_technician'] === 1 && self::hasTechnician($ticketId)) {
            return;
        }

        $technicians = self::getGroupTechnicians($groupId);
        if (!$technicians) {
            return;
        }

        $eligible = [];
        foreach ($technicians as $userId) {
            $active = self::countActiveTickets($userId, (bool)$config['include_waiting']);
            if ((int)$config['max_active_tickets'] > 0 && $active >= (int)$config['max_active_tickets']) {
                continue;
            }
            $eligible[$userId] = $active;
        }
        if (!$eligible) {
            return;
        }

        if ($config['mode'] === 'round_robin') {
            $userId = self::roundRobin($groupId, array_keys($eligible));
        } else {
            asort($eligible, SORT_NUMERIC);
            $userId = (int)array_key_first($eligible);
        }

        if ($userId > 0) {
            self::assignTechnician($ticketId, $userId);
        }
    }

    private static function getAssignedGroupId(int $ticketId): int
    {
        global $DB;
        $result = $DB->request([
            'FROM' => Group_Ticket::getTable(),
            'WHERE' => [
                'tickets_id' => $ticketId,
                'type' => Group_Ticket::ASSIGN,
            ],
            'LIMIT' => 1,
        ]);
        foreach ($result as $row) {
            return (int)$row['groups_id'];
        }
        return 0;
    }

    private static function getGroupTechnicians(int $groupId): array
    {
        global $DB;
        $gu = Group_User::getTable();
        $u = User::getTable();
        $result = $DB->request([
            'SELECT' => [$gu . '.users_id'],
            'FROM' => $gu,
            'INNER JOIN' => [
                $u => ['ON' => [$gu => 'users_id', $u => 'id']],
            ],
            'WHERE' => [
                $gu . '.groups_id' => $groupId,
                $u . '.is_deleted' => 0,
                $u . '.is_active' => 1,
            ],
        ]);
        $ids = [];
        foreach ($result as $row) {
            $ids[] = (int)$row['users_id'];
        }
        return array_values(array_unique($ids));
    }

    private static function hasTechnician(int $ticketId): bool
    {
        global $DB;
        $result = $DB->request([
            'COUNT' => 'c',
            'FROM' => Ticket_User::getTable(),
            'WHERE' => [
                'tickets_id' => $ticketId,
                'type' => Ticket_User::ASSIGN,
            ],
        ]);
        $row = $result->current();
        return ((int)($row['c'] ?? 0)) > 0;
    }

    private static function countActiveTickets(int $userId, bool $includeWaiting): int
    {
        global $DB;
        $ticketTable = Ticket::getTable();
        $actorTable = Ticket_User::getTable();
        $statuses = [
            CommonITILObject::INCOMING,
            CommonITILObject::ASSIGNED,
            CommonITILObject::PLANNED,
            CommonITILObject::WAITING,
        ];
        if (!$includeWaiting) {
            $statuses = array_diff($statuses, [CommonITILObject::WAITING]);
        }
        $result = $DB->request([
            'COUNT' => 'c',
            'FROM' => $actorTable,
            'INNER JOIN' => [
                $ticketTable => ['ON' => [$actorTable => 'tickets_id', $ticketTable => 'id']],
            ],
            'WHERE' => [
                $actorTable . '.users_id' => $userId,
                $actorTable . '.type' => Ticket_User::ASSIGN,
                $ticketTable . '.is_deleted' => 0,
                $ticketTable . '.status' => $statuses,
            ],
        ]);
        $row = $result->current();
        return (int)($row['c'] ?? 0);
    }

    private static function roundRobin(int $groupId, array $technicians): int
    {
        sort($technicians, SORT_NUMERIC);
        $key = 'glpi_autoassign_rr_' . $groupId;
        $previous = (int)($_SESSION[$key] ?? 0);
        if ($previous && in_array($previous, $technicians, true)) {
            $index = array_search($previous, $technicians, true);
            $next = $technicians[($index + 1) % count($technicians)];
        } else {
            $next = $technicians[0];
        }
        $_SESSION[$key] = $next;
        return $next;
    }

    private static function assignTechnician(int $ticketId, int $userId): void
    {
        $ticketUser = new Ticket_User();
        $ticketUser->add([
            'tickets_id' => $ticketId,
            'users_id' => $userId,
            'type' => Ticket_User::ASSIGN,
        ]);
    }
}
