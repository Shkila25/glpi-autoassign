<?php
namespace GlpiPlugin\Glpiautoassign;

use CommonITILObject;
use Group_Ticket;
use Group_User;
use Ticket;
use Ticket_User;
use User;

class Assignment {
    private static array $processing=[];
    public static function onTicketChange(Ticket $ticket): void {
        $id=(int)$ticket->getID(); if($id<=0||isset(self::$processing[$id])) return;
        self::$processing[$id]=true; try { self::assign($id); } finally { unset(self::$processing[$id]); }
    }
    private static function assign(int $ticketId): void {
        $cfg=Config::getConfig(); if(!$cfg) return;
        if((int)$cfg['only_if_no_technician']===1 && self::hasTech($ticketId)) return;
        $group=self::group($ticketId); if($group<=0) return;
        $users=self::groupUsers($group); if(!$users) return;
        $eligible=[];
        foreach($users as $uid){$n=self::active($uid,(bool)$cfg['include_waiting']); $limit=(int)$cfg['max_active_tickets']; if($limit>0&&$n>=$limit)continue; $eligible[$uid]=$n;}
        if(!$eligible)return;
        if($cfg['mode']==='round_robin') $uid=self::rr($group,array_keys($eligible)); else {asort($eligible,SORT_NUMERIC);$uid=(int)array_key_first($eligible);}
        if($uid>0){self::assignUser($ticketId,$uid); if($cfg['mode']==='round_robin')self::saveRr($group,$uid);}
    }
    private static function group(int $id): int { global $DB; $r=$DB->request(['FROM'=>Group_Ticket::getTable(),'WHERE'=>['tickets_id'=>$id,'type'=>Group_Ticket::ASSIGN],'LIMIT'=>1]); foreach($r as $x)return (int)$x['groups_id']; return 0; }
    private static function groupUsers(int $gid): array { global $DB; $gu=Group_User::getTable();$u=User::getTable();$r=$DB->request(['SELECT'=>[$gu.'.users_id'],'FROM'=>$gu,'INNER JOIN'=>[$u=>['ON'=>[$gu=>'users_id',$u=>'id']]],'WHERE'=>[$gu.'.groups_id'=>$gid,$u.'.is_deleted'=>0,$u.'.is_active'=>1]]);$out=[];foreach($r as $x)$out[]=(int)$x['users_id'];return array_values(array_unique($out)); }
    private static function hasTech(int $id): bool { global $DB;$r=$DB->request(['COUNT'=>'count','FROM'=>Ticket_User::getTable(),'WHERE'=>['tickets_id'=>$id,'type'=>Ticket_User::ASSIGN]]);$x=$r->current();return (int)($x['count']??0)>0; }
    private static function active(int $uid,bool $waiting): int { global $DB;$tu=Ticket_User::getTable();$t=Ticket::getTable();$s=[CommonITILObject::INCOMING,CommonITILObject::ASSIGNED,CommonITILObject::PLANNED];if($waiting)$s[]=CommonITILObject::WAITING;$r=$DB->request(['COUNT'=>'count','FROM'=>$tu,'INNER JOIN'=>[$t=>['ON'=>[$tu=>'tickets_id',$t=>'id']]],'WHERE'=>[$tu.'.users_id'=>$uid,$tu.'.type'=>Ticket_User::ASSIGN,$t.'.is_deleted'=>0,$t.'.status'=>$s]]);$x=$r->current();return (int)($x['count']??0); }
    private static function rr(int $gid,array $users): int { global $DB;sort($users,SORT_NUMERIC);$last=0;$r=$DB->request(['FROM'=>Config::rrTable(),'WHERE'=>['groups_id'=>$gid],'LIMIT'=>1]);foreach($r as $x)$last=(int)$x['users_id'];if(!$last||!in_array($last,$users,true))return $users[0];$i=array_search($last,$users,true);return $users[($i+1)%count($users)]; }
    private static function saveRr(int $gid,int $uid): void { global $DB;$t=Config::rrTable();$r=$DB->request(['FROM'=>$t,'WHERE'=>['groups_id'=>$gid],'LIMIT'=>1]);$exists=false;foreach($r as $x){$exists=true;break;}if($exists)$DB->update($t,['users_id'=>$uid],['groups_id'=>$gid]);else$DB->insert($t,['groups_id'=>$gid,'users_id'=>$uid]); }
    private static function assignUser(int $tid,int $uid): void { (new Ticket_User())->add(['tickets_id'=>$tid,'users_id'=>$uid,'type'=>Ticket_User::ASSIGN]); }
}
