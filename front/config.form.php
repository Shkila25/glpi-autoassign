<?php
use GlpiPlugin\Glpiautoassign\Config;
include '../../../inc/includes.php';
Session::checkRight('config', UPDATE);
if(isset($_POST['update'])){Session::checkCSRF();Config::saveConfig($_POST);Html::back();}
Html::header('GLPI AutoAssign',$_SERVER['PHP_SELF'],'config','plugins');
$c=Config::getConfig()??['mode'=>'least_loaded','max_active_tickets'=>0,'include_waiting'=>0,'only_if_no_technician'=>1];
echo '<div class="center"><form method="post">';
echo Html::hidden('_glpi_csrf_token',['value'=>Session::getNewCSRFToken()]);
echo '<table class="tab_cadre_fixe"><tr><th colspan="2">GLPI AutoAssign</th></tr>';
echo '<tr><td>Метод распределения</td><td>';Dropdown::showFromArray('mode',['least_loaded'=>'Наименее загруженный','round_robin'=>'Round-Robin'],['value'=>$c['mode']]);echo '</td></tr>';
echo '<tr><td>Максимум активных заявок</td><td><input type="number" name="max_active_tickets" min="0" value="'.(int)$c['max_active_tickets'].'"><br><small>0 = без ограничения</small></td></tr>';
echo '<tr><td>Учитывать заявки в ожидании</td><td>';Dropdown::showYesNo('include_waiting',(int)$c['include_waiting']);echo '</td></tr>';
echo '<tr><td>Не менять уже назначенного техника</td><td>';Dropdown::showYesNo('only_if_no_technician',(int)$c['only_if_no_technician']);echo '</td></tr>';
echo '<tr><td colspan="2" class="center">'.Html::submit(_sx('button','Save'),['name'=>'update']).'</td></tr></table>';
Html::closeForm();echo '</div>';Html::footer();
