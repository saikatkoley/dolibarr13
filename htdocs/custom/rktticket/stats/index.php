<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
if (false === (@include '../../main.inc.php')) {  // From htdocs directory
	require '../../../main.inc.php'; // From "custom" directory
        $mod_path = "/custom";
}

global $db, $langs, $user;

dol_include_once('/custom/rktticket/class/rktticketStats.class.php');
//dol_include_once('/ticket/class/ticket.class.php');
// require_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/rktticket/core/class/customdolgraph.class.php';

$WIDTH=CustomDolGraph::getDefaultGraphSizeForStats('width');
$HEIGHT=CustomDolGraph::getDefaultGraphSizeForStats('height');

// Load translation files required by the page
$langs->load("ticket@ticket");
$langs->load('other');

// Get parameters
$status=GETPOST('status','int');
$userid=GETPOST('userid','int');

// Access control
if ($user->socid > 0 || !$user->rights->rktticket->read) {
	// External user
	accessforbidden();
}

$nowyear=strftime("%Y", dol_now());
$year = GETPOST('year')>0?GETPOST('year'):$nowyear;
//$startyear=$year-2;
$startyear=$year-1;
$endyear=$year;

$mode=GETPOST('mode');

/*
 * VIEW
 *
 * Put here all code to build page
 */

$form=new Form($db);

llxHeader('', $langs->trans('TicketStatistics'), '');

print load_fiche_titre($langs->trans("TicketStatistics"));

$dir=$conf->ticket->dir_temp;

dol_mkdir($dir);


$stats = new RktTicketStats($db, $socid, ($userid>0?$userid:0));
if ($status != '' && $status >= 0)
{
        if ($status == RktTicket::STATUS_ASSIGNED) {
            $stats->where .= " AND p.assigned_to IS NOT NULL";
        }
        else if ($status == RktTicket::STATUS_NOTASSIGNED) {
            $stats->where .= " AND p.assigned_to IS NULL";
        }
        else {
            $stats->where .= " AND p.status IN (".$status.")";
        }
}

// Build graphic number of object
$data = $stats->getNbByMonthWithPrevYear($endyear,$startyear);
// $data = array(array('Lib',val1,val2,val3),...)


if (!$user->rights->societe->client->voir || $user->societe_id)
{
    $filenamenb = $dir.'/ticketsnbinyear-'.$user->id.'-'.$year.'.png';
    $fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=ticketstats&file=ticketsnbinyear-'.$user->id.'-'.$year.'.png';
}
else
{
    $filenamenb = $dir.'/ticketsnbinyear-'.$year.'.png';
    $fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=ticketstats&file=ticketsnbinyear-'.$year.'.png';
}

$px1 = new CustomDolGraph();
$mesg = $px1->isGraphKo();
if (! $mesg)
{
    $px1->SetData($data);
    $px1->SetPrecisionY(0);
    $i=$startyear;$legend=array();
    while ($i <= $endyear)
    {
        $legend[]=$i;
        $i++;
    }
    $px1->SetLegend($legend);
    $px1->SetMaxValue($px1->GetCeilMaxValue());
    $px1->SetMinValue(min(0,$px1->GetFloorMinValue()));
    $px1->SetWidth($WIDTH);
    $px1->SetHeight($HEIGHT);
    $px1->SetYLabel($langs->trans("NbOfTickets"));
    $px1->SetShading(3);
    $px1->SetHorizTickIncrement(1);
    $px1->SetPrecisionY(0);
    $px1->mode='depth';
    $px1->SetTitle($langs->trans("NumberOfTicketsByMonth"));

    $px1->draw($filenamenb,$fileurlnb);
}

// Show array
$data = $stats->getAllByYear();
$arrayyears=array();
foreach($data as $val) {
	if (! empty($val['year'])) {
		$arrayyears[$val['year']]=$val['year'];
	}
}
if (! count($arrayyears)) $arrayyears[$nowyear]=$nowyear;


$h=0;
$head = array();
$head[$h][0] = DOL_URL_ROOT . $mod_path . '/rktticket/stats/index.php';
$head[$h][1] = $langs->trans("ByMonthYear");
$head[$h][2] = 'byyear';
$h++;

complete_head_from_modules($conf,$langs,null,$head,$h,'ticket_stats');

dol_fiche_head($head,'byyear',$langs->trans("Statistics"));


print '<div class="fichecenter"><div class="fichethirdleft">';


//if (empty($socid))
//{
	// Show filter box
	print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="mode" value="'.$mode.'">';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';
	// User
	print '<tr><td align="left">'.$langs->trans("CreatedBy").'</td><td align="left">';
	print $form->select_dolusers($userid, 'userid', 1, '', 0, '', '', 0, 0, 0, '', 0, '', 'maxwidth300');
	print '</td></tr>';
        // Status
	print '<tr><td align="left">'.$langs->trans("Status").'</td><td align="left">';
	print '<select name="status">';
        print '<option value="">&nbsp;</option>';
        $ticketstatic = new RktTicket($db);
        for ($s = 0; $s < 5; $s++)
        {
            print '<option value="'.$s.'"'.( $status != '' && $status == $s ? " selected" : "").'>'.$ticketstatic->LibStatut($s, 0).'</option>';
        }
        print '</select>';
	print '</td></tr>';
	// Year
	print '<tr><td align="left">'.$langs->trans("Year").'</td><td align="left">';
	if (! in_array($year,$arrayyears)) $arrayyears[$year]=$year;
	if (! in_array($nowyear,$arrayyears)) $arrayyears[$nowyear]=$nowyear;
	arsort($arrayyears);
	print $form->selectarray('year',$arrayyears,$year,0);
	print '</td></tr>';
	print '<tr><td align="center" colspan="2"><input type="submit" name="submit" class="button" value="'.$langs->trans("Refresh").'"></td></tr>';
	print '</table>';
	print '</form>';
	print '<br><br>';
//}

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre" height="24">';
print '<td align="center">'.$langs->trans("Year").'</td>';
print '<td align="center">'.$langs->trans("NbOfTickets").'</td>';
print '<td align="center">%</td>';
print '</tr>';

$oldyear=0;
$var=true;
foreach ($data as $val)
{
    $year = $val['year'];
    while (! empty($year) && $oldyear > $year+1)
    {	// If we have empty year
        $oldyear--;
        $var=!$var;
        print '<tr '.$bc[$var].' height="24">';
        print '<td align="center"><a href="'.$_SERVER["PHP_SELF"].'?year='.$oldyear.'&amp;mode='.$mode.($socid>0?'&socid='.$socid:'').($userid>0?'&userid='.$userid:'').'">'.$oldyear.'</a></td>';
        print '<td align="center">0</td>';
        print '<td align="center"></td>';
        print '</tr>';
    }
    print '<tr '.$bc[$var].' height="24">';
    print '<td align="center"><a href="'.$_SERVER["PHP_SELF"].'?year='.$year.($socid>0?'&socid='.$socid:'').($userid>0?'&userid='.$userid:'').'">'.$year.'</a></td>';
    print '<td align="center">'.$val['nb'].'</td>';
    print '<td align="center" style="'.(($val['nb_diff'] >= 0) ? 'color: green;':'color: red;').'">'.round($val['nb_diff']).'</td>';
    print '</tr>';
    $oldyear=$year;
}

print '</table>';


print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


// Show graphs
print '<table class="border" width="100%"><tr valign="top"><td align="center">';
if ($mesg) { print $mesg; }
else {
    print $px1->show();
}
print '</td></tr></table>';


print '</div></div></div>';
print '<div style="clear:both"></div>';


dol_fiche_end();

// End of page
llxFooter();

$db->close();
