<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <year>  <name of author>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Load Dolibarr environment
$mod_path = "";
if (false === (@include '../main.inc.php')) {  // From htdocs directory
	require '../../main.inc.php'; // From "custom" directory
        $mod_path = "/custom";
}

global $db, $langs, $user;

dol_include_once('/custom/rktticket/class/rktticket.class.php');
dol_include_once('/custom/rktticket/lib/rktticket.lib.php');

// Load translation files required by the page
$langs->load("rktticket@rktticket");

// Get parameters
$socid = GETPOST('socid','int');

// Access control
if ($user->socid > 0 || !$user->rights->rktticket->read) {
	// External user
	accessforbidden();
}

/*
 * VIEW
 *
 * Put here all code to build page
 */

$ticketstatic=new RktTicket($db);
$companystatic=new Societe($db);

llxHeader('', $langs->trans('TicketIndexPageName'), '');

print load_fiche_titre($langs->trans("TicketsArea"));

print '<div class="fichecenter"><div class="fichethirdleft">';

// Search ticket
$var=false;
print '<form method="post" action="'.DOL_URL_ROOT.$mod_path.'/rktticket/list.php">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<table class="noborder nohover" width="100%">';
print '<tr class="liste_titre"><td colspan="3">'.$langs->trans("Search").'</td></tr>';
print '<tr '.$bc[$var].'><td>';
print $langs->trans("Ticket").':</td><td><input type="text" class="flat" name="search_ref" size=18></td><td><input type="submit" value="'.$langs->trans("Search").'" class="button"></td></tr>';
print "</table></form><br>\n";

/*
 * Statistics
 */

$sql = "SELECT count(t.rowid), t.status";
$sql.= " FROM ".MAIN_DB_PREFIX."rkt_ticket as t";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON t.fk_soc = s.rowid";
//$sql.= " WHERE t.entity IN (".getEntity('societe', 1).")";
$sql.= " GROUP BY t.status";
// echo $sql;
$resql = $db->query($sql);
if ($resql)
{
    $num = $db->num_rows($resql);
    $i = 0;

    $total=0;
    $totalinprocess=0;
    $dataseries=array();
    $vals=array();
    // 0=Draft, 1=Validated, 4=Closed
    while ($i < $num)
    {
        $row = $db->fetch_row($resql);
        if ($row)
        {
            //if ($row[1]!=-1 && ($row[1]!=3 || $row[2]!=1))
            {
                $vals[$row[1]]=$row[0];
                $totalinprocess+=$row[0];
            }
            $total+=$row[0];
        }
        $i++;
    }
    $db->free($resql);

    print '<table class="noborder nohover" width="100%">';
    print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("Statistics").' - '.$langs->trans("Tickets").'</td></tr>'."\n";
    $var=true;
    $listofstatus=array(0,1,4);
    foreach ($listofstatus as $status)
    {
        $dataseries[]=array('label'=>$ticketstatic->LibStatut($status,1),'data'=>(isset($vals[$status])?(int) $vals[$status]:0));
        if (! $conf->use_javascript_ajax)
        {
            $var=!$var;
            print "<tr ".$bc[$var].">";
            print '<td>'.$ticketstatic->LibStatut($status,0).'</td>';
            print '<td align="right"><a href="list.php?status='.$status.'">'.(isset($vals[$status])?$vals[$status]:0).'</a></td>';
            print "</tr>\n";
        }
    }
    if ($conf->use_javascript_ajax)
    {
        print '<tr class="impair"><td align="center" colspan="2">';
        $data=array('series'=>$dataseries);
        custom_dol_print_graph('stats',300,180,$data,1,'pie',1);
        /*include_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';
        $dolgraph=new DolGraph();
        $dolgraph->SetTitle($langs->transnoentities('Ticket').'<br>'.$langs->transnoentities('Ticket').'%');
        $dolgraph->SetMaxValue(50);
        $dolgraph->SetData($data);
        $dolgraph->setShowLegend(1);
        $dolgraph->setShowPercent(1);
        $dolgraph->SetType(array('pie'));
        $dolgraph->setWidth('100%');
        $dolgraph->draw('idofgraph');
        print $dolgraph->show($total?0:1);*/
        print '</td></tr>';
    }
    
    print '<tr class="liste_total"><td>'.$langs->trans("Total").'</td><td align="right">'.$total.'</td></tr>';
    print "</table><br>";
}
else
{
    dol_print_error($db);
}

//print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


$max=10;

/*
 * Last created tickets
 */

$sql = "SELECT t.rowid, t.ref, t.creation_date, t.status, s.rowid as socid, s.nom as socname, s.client, s.canvas";
$sql.= " FROM ".MAIN_DB_PREFIX."rkt_ticket as t";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON t.fk_soc = s.rowid";
//$sql.= " WHERE c.entity IN (".getEntity('ticket', 1).")";
$sql.= " ORDER BY t.creation_date DESC";
$sql.= $db->plimit($max, 0);
// echo $sql;
$resql=$db->query($sql);
if ($resql)
{
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td colspan="4">'.$langs->trans("LastCreatedTickets",$max).'</td></tr>';

	$num = $db->num_rows($resql);
	if ($num)
	{
		$i = 0;
		$var = True;
		while ($i < $num)
		{
			$var=!$var;
			$obj = $db->fetch_object($resql);

			print "<tr ".$bc[$var].">";
			print '<td width="20%" class="nowrap">';

			$ticketstatic->id=$obj->rowid;
			$ticketstatic->ref=$obj->ref;

			print '<table class="nobordernopadding"><tr class="nocellnopadd">';
			print '<td width="96" class="nobordernopadding nowrap">';
			print $ticketstatic->getNomUrl(1);
			print '</td>';

			print '<td width="16" class="nobordernopadding nowrap">';
			print '&nbsp;';
			print '</td>';
                        
			print '</tr></table>';

			print '</td>';

            if ($obj->socid > 0) {
    			$companystatic->id=$obj->socid;
    			$companystatic->name=$obj->socname;
    			$companystatic->client=$obj->client;
    			$companystatic->canvas=$obj->canvas;
    			print '<td align="center">'.$companystatic->getNomUrl(1,'customer').'</td>';
            }
            else {
                print '<td align="center">-</td>';
            }

			print '<td align="center">'.dol_print_date($db->jdate($obj->creation_date),'day').'</td>';
			print '<td align="right">'.$ticketstatic->LibStatut($obj->status,5).'</td>';
			print '</tr>';
			$i++;
		}
	}
	print "</table><br>";
}
else
{
    dol_print_error($db);
}

print '</div></div></div>';

// End of page
llxFooter();
