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
if (false === (@include '../main.inc.php')) {  // From htdocs directory
	require '../../main.inc.php'; // From "custom" directory
        $mod_path = "/custom";
}

global $db, $conf, $langs, $user;

dol_include_once('/custom/rktticket/class/rktticket.class.php');
require_once DOL_DOCUMENT_ROOT.$mod_path.'/rktticket/class/rktticketdict.class.php';

// Load translation files required by the page
$langs->load("ticket@ticket");

// Get parameters
$search_ref=GETPOST('search_ref');
$search_customer = GETPOST('search_customer','alpha');
$userid=GETPOST('userid','int');
//$search_user = GETPOST('search_user','alpha');
$assignedtoid=GETPOST('assignedtoid','int');
$date=dol_mktime(0, 0, 0, GETPOST('datemonth'), GETPOST('dateday'), GETPOST('dateyear'));
$status = GETPOST('status','alpha');
$optioncss = GETPOST('optioncss','alpha');

$page  = GETPOST('page','int')?GETPOST('page','int'):0;
$socid = GETPOST('socid','int');
$sortorder = GETPOST('sortorder','alpha');
$sortfield = GETPOST('sortfield','alpha');
$limit = GETPOST("limit")?GETPOST("limit","int"):$conf->liste_limit;

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('ticketlist'));

// Access control
if ($user->socid > 0 || !$user->rights->rktticket->read) {
	// External user
	accessforbidden();
}

// Purge search criteria
if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter")) // Both test are required to be compatible with all browsers
{
    $search_ref='';
    $search_customer='';
    $userid=-1;
    //$search_user='';
    $assignedtoid=-1;
    $date='';
    $status='';
}

/*
 * VIEW
 *
 * Put here all code to build page
 */

$form=new Form($db);
$ticketstatic = new RktTicket($db);

$title = $langs->trans("Tickets");
if ($status != '' && $status != '-1') {
    $title.= ' - ' . $ticketstatic->labelstatut_short[$status];
}

llxHeader('', $title, '');

if ($sortorder == "") $sortorder="DESC";
if ($sortfield == "") $sortfield="t.creation_date";
$offset = $limit * $page;

/*
 * Mode list
 */

$sql = "SELECT t.rowid, t.ref, t.fk_soc, s.nom as soc_name, t.fk_type, t.fk_category, t.fk_severity";
$sql.= ", t.sujet, t.created_by, t.assigned_to, t.creation_date, t.status";
$sql.= " FROM " . MAIN_DB_PREFIX . "rkt_ticket as t";
$sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON t.fk_soc = s.rowid";
$sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "user as u ON t.created_by = u.rowid";
$sql.= " WHERE t.entity IN(".getEntity('user', 1).")"; // t.entity = $conf->entity;
if ($search_ref)
{
	$sql .= natural_search('t.ref', $search_ref);
}
if ($socid)
{
        $sql.= " AND t.fk_soc = ".$socid;
}
if ($search_customer)
{
        $sql .= natural_search('s.nom', $search_customer);
}
if ($userid > 0)
{
        $sql.= " AND t.created_by = ".$userid;
}
/*if ($search_user)
{
        $sql .= natural_search(array('u.firstname', 'u.lastname'), $search_user);
}*/
if ($assignedtoid > 0)
{
        $sql.= " AND t.assigned_to = ".$assignedtoid;
}
if ($date)
{
        $sql.= " AND date(t.creation_date) = date('".$db->idate($date)."')";
}
if ($status != '' && $status >= 0)
{
        if ($status == RktTicket::STATUS_ASSIGNED) {
            $sql .= " AND t.assigned_to IS NOT NULL";
        }
        else if ($status == RktTicket::STATUS_NOTASSIGNED) {
            $sql .= " AND t.assigned_to IS NULL";
        }
        else {
            $sql .= " AND t.status IN (".$status.")";
        }
}

$sql.= $db->order($sortfield,$sortorder);

$nbtotalofrecords = 0;
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
	$result = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($result);
}

$sql.= $db->plimit($limit+1, $offset);
// echo $sql;
$resql = $db->query($sql);
if ($resql)
{

	$num = $db->num_rows($resql);
	$i = 0;
        
    $param="";
    if ($limit > 0 && $limit != $conf->liste_limit) $param.='&limit='.urlencode($limit);
	if ($search_ref) $param.="&search_ref=".$search_ref;
    if ($search_customer) $param.="&search_customer=".$search_customer;
    if ($userid > 0) $param.="&userid=".$userid;
    if ($assignedtoid > 0) $param.="&assignedtoid=".$assignedtoid;
    if ($date) $param.="&date=".$date;
	if ($socid) $param.="&socid=".$socid;
	if ($status >= 0) $param.="&status=".$status;
	if ($optioncss != '') $param.='&optioncss='.$optioncss;
        
	print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
    if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="list">';
	print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
	print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
        
    print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_generic', 0, '', '', $limit);
        
    print '<table class="liste noborder" width="100%">';
	print '<tr class="liste_titre">';
	print_liste_field_titre($langs->trans("Ref"),$_SERVER["PHP_SELF"],"t.ref","",$param,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("ThirdParty"),$_SERVER["PHP_SELF"],"t.fk_soc","",$param,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("CreatedBy"),$_SERVER["PHP_SELF"],"t.created_by","",$param,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("AssignedTo"),$_SERVER["PHP_SELF"],"t.assigned_to","",$param,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Type"),$_SERVER["PHP_SELF"],"t.fk_type","",$param,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Category"),$_SERVER["PHP_SELF"],"t.fk_category","",$param,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans('Severity'),$_SERVER["PHP_SELF"],'t.fk_severity','',$param, '',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Date"),$_SERVER["PHP_SELF"],"t.creation_date","",$param,'align="center"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans('Subject'),$_SERVER["PHP_SELF"],'t.sujet','',$param,'align="center"',$sortfield,$sortorder,'');
    print_liste_field_titre($langs->trans('Status'),$_SERVER["PHP_SELF"],'t.status','',$param,'align="right"',$sortfield,$sortorder,'');
	print_liste_field_titre('',$_SERVER["PHP_SELF"],"",'','','',$sortfield,$sortorder,'maxwidthsearch ');
	print "</tr>\n";

	print '<tr class="liste_titre liste_titre_filter">';
        
    print '<td class="liste_titre"><input size="8" type="text" class="flat" name="search_ref" value="'.$search_ref.'"></td>';
	print '<td class="liste_titre"><input size="8" type="text" class="flat" name="search_customer" value="'.$search_customer.'"></td>';
	print '<td class="liste_titre">'.$form->select_dolusers($userid, 'userid', 1, '', 0, '', '', 0, 0, 0, '', 0, '', 'minwidth100" style="min-width: 100px;').'</td>'; // TODO: remove the css hack, this is just a fix for dolibarr 3.9
    //print '<td class="liste_titre"><input size="8" type="text" class="flat" name="search_user" value="'.$search_user.'"></td>';
    print '<td class="liste_titre">'.$form->select_dolusers($assignedtoid, 'assignedtoid', 1, '', 0, '', '', 0, 0, 0, '', 0, '', 'minwidth100" style="min-width: 100px;').'</td>';
    print '<td class="liste_titre"></td>';
    print '<td class="liste_titre"></td>';
    print '<td class="liste_titre"></td>';
    print '<td class="liste_titre" align="center" width="120">'.$form->select_date($date, 'date', 0, 0, 1, '', 1, 0, 1).'</td>';
    print '<td class="liste_titre"></td>';
    print '<td class="liste_titre" align="right">';
    print '<select class="flat" name="status">';
    print '<option value="-1">&nbsp;</option>';
    $selected = $status != '' ? $status : -1;
    foreach($ticketstatic->labelstatut_short as $key => $value)
    {
        print '<option value="'.$key.'"'.(($selected == $key || $selected == $value)?' selected':'').'>';
        print $value;
        print '</option>';
    }
    print '</select>';
    print '</td>';
	print '<td class="liste_titre" align="right"><input type="image" class="liste_titre" name="button_search" src="'.img_picto($langs->trans("Search"),'search.png','','',1).'" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
	print '<input type="image" class="liste_titre" name="button_removefilter" src="'.img_picto($langs->trans("Search"),'searchclear.png','','',1).'" value="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'" title="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'">';
	print "</td></tr>\n";
        
    $var=true;
    
    $userstatic = new User($db);
    $soc = new Societe($db);
    $dict = new RktTicketDict($db);
    $dictlibelles = $dict->getdictlibelles();
    
    while ($i < min($num,$limit))
	{
        $obj = $db->fetch_object($resql);
        
        $var=!$var;
        
        print "<tr ".$bc[$var].">";
        
        // Ref
        $ticketstatic->id = $obj->rowid;
        $ticketstatic->ref = $obj->ref;
        print '<td class="nobordernopadding nowrap">';
        print $ticketstatic->getNomUrl(1);
		print '</td>'."\n";
                
        // Thirdparty
		print '<td>';
        if ($obj->fk_soc > 0) {
    		$soc->id = $obj->fk_soc;
    		$soc->name = $obj->soc_name;
    		print $soc->getNomUrl(1);
        }
        else {
            print $langs->trans("None");
        }
		print '</td>'."\n";
                
        // Created by
        $userstatic->fetch($obj->created_by);
		print "<td>";
		if ($userstatic->id > 0) print $userstatic->getNomUrl(1);
		else print "&nbsp;";
		print "</td>";
                
        // Assigned to
        $userstatic->id = 0; // rénitialisation
        $userstatic->fetch($obj->assigned_to);
		print "<td>";
		if ($userstatic->id > 0) print $userstatic->getNomUrl(1);
		else print $langs->trans("None");
		print "</td>";
                
        // Type
		print '<td>'.$dictlibelles[$obj->fk_type].'</td>';
                
        // Category
		print '<td>'.$dictlibelles[$obj->fk_category].'</td>';
                
        // Severity
		print '<td>'.$dictlibelles[$obj->fk_severity].'</td>';
                
        // Date
		print "<td align=\"center\" width=\"100\">";
		if ($obj->creation_date)
		{
			print dol_print_date($db->jdate($obj->creation_date),"day");
		}
		else
		{
			print "-";
		}
		print '</td>';
                
        // Subject
		print '<td align="center">'.$obj->sujet.'</td>';
                
        // Status
		print '<td align="right">'.$ticketstatic->LibStatut($obj->status, 5).'</td>';
                
        print '<td></td>';
        print "</tr>\n";

        $i++;
    }
    
    print "</table>\n";
    print "</form>\n";
    
    $db->free($resql);
}
else
{
	dol_print_error($db);
}

// End of page
llxFooter();
$db->close();
