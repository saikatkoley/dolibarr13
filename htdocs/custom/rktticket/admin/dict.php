<?php
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

/**
 * \file    admin/dict.php
 * \ingroup ...
 * \brief   Example module setup page.
 *
 * Put detailed description here.
 */

// Load Dolibarr environment
if (false === (@include '../../main.inc.php')) {  // From htdocs directory
	require '../../../main.inc.php'; // From "custom" directory
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
dol_include_once("/custom/rktticket/class/rktticketdict.class.php");
require_once DOL_DOCUMENT_ROOT . '/custom/rktticket/lib/rktticket.lib.php';

// Translations
$langs->load("admin");
$langs->load("errors");
$langs->load("ticket@ticket");

// Access control
if (! $user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action','alpha');

$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');

$dict_name = GETPOST('dict_name','alpha');
$libelle = GETPOST('libelle','alpha');
$desc = GETPOST('desc','alpha');

$id = GETPOST('id','int');

// vars
$acts[0] = "activate";
$acts[1] = "disable";
$actl[0] = img_picto($langs->trans("Disabled"),'switch_off');
$actl[1] = img_picto($langs->trans("Activated"),'switch_on');

// object
$object = new RktTicketDict($db);
$dictnames = $object->getdictnames();

/*
 * Actions
 */

// add || modify
if ($action == 'add' || GETPOST('actionmodify'))
{
    $error = 0;
    
    if (empty($dict_name))
    {
        setEventMessages($langs->transnoentities("ErrorFieldRequired",$langs->transnoentities("DictName")), null, 'errors');
        $error++;
    }
    if (empty($libelle))
    {
        setEventMessages($langs->transnoentities("ErrorFieldRequired",$langs->transnoentities("DictLabel")), null, 'errors');
        $error++;
    }
//    if (empty($desc))
//    {
//        setEventMessages($langs->transnoentities("ErrorFieldRequired",$langs->transnoentities("DictDesc")), null, 'errors');
//        $error++;
//    }
    
    if (! $error)
    {
        $result = $action == 'add' ? $object->create($dict_name, $libelle, $desc) : $object->update($id, $dict_name, $libelle, $desc);

        if ($result > 0)
        {
            if ($action == 'add') {
                setEventMessages($langs->transnoentities("RecordSaved"), null, 'mesgs');
            }
        }
        else
        {
            setEventMessages($object->error, $object->errors, 'errors');
        }
    }
}

// activate
else if ($action == $acts[0])
{
    $result = $object->setstate($id, 1);
    
    if ($result < 0)
    {
        setEventMessages($object->error, $object->errors, 'errors');
    }
}

// disable
else if ($action == $acts[1])
{
    $result = $object->setstate($id, 0);
    
    if ($result < 0)
    {
        setEventMessages($object->error, $object->errors, 'errors');
    }
}

// confirm_delete
else if ($action == 'confirm_delete')
{
    $result = $object->delete($id, $user);
    
    if ($result < 0)
    {
        setEventMessages($object->error, $object->errors, 'errors');
    }
}

/*
 * View
 */

$page_name = "TicketDict";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
	. $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = rktticketAdminPrepareHead();
dol_fiche_head(
	$head,
	'dict',
	$langs->trans("Module600001Name"),
	0,
	"ticket@ticket"
);

// Confirmation de la suppression de la ligne
if ($action == 'delete')
{
    $form = new Form($db);
    print $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$id, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_delete','',0,1);
}

// Setup page goes here
$var=false;
print '<table class="noborder" width="100%">';

print '<tr class="liste_titre">';
print '<td align="left">'.$langs->trans("DictName").'*</td>';
print '<td align="left">'.$langs->trans("DictLabel").'*</td>';
print '<td align="left">'.$langs->trans("DictDesc").'</td>';
print '<td colspan="3"></td>';
print '</tr>';

print '<tr '.$bcnd[$var].'>';
print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="add">';

print '<td align="left">';
print tolist($dictnames, "dict_name");
print '</td>';
print '<td align="left"><input type="text" class="flat" name="libelle" value=""></td>';
print '<td align="left"><input size="32" type="text" class="flat" name="desc" value=""></td>';
print '<td align="right" colspan="3">';
print '<input type="submit" class="button" name="actionadd" value="'.$langs->trans("Add").'">';
print '</td>';

print '</form>';
print '</tr>';

print '<tr><td colspan="6">&nbsp;</td></tr>'; // Keep &nbsp; to have a line with enough height

print '<tr class="liste_titre">';
//print '<th align="left">'.$langs->trans("DictName").'*</th>';
//print '<th align="left">'.$langs->trans("DictLabel").'*</th>';
//print '<th align="left">'.$langs->trans("DictDesc").'</th>';
//print '<th align="center">'.$langs->trans("DictState").'</th>';
//print '<th></th>';
//print '<th></th>';
print getTitleFieldOfList($langs->trans("DictName").'*',0,$_SERVER["PHP_SELF"],"dict_name","","",'align="left"',$sortfield,$sortorder);
print getTitleFieldOfList($langs->trans("DictLabel").'*',0,$_SERVER["PHP_SELF"],"libelle","","",'align="left"',$sortfield,$sortorder);
print getTitleFieldOfList($langs->trans("DictDesc"),0,$_SERVER["PHP_SELF"],"description","","",'align="left"',$sortfield,$sortorder);
print getTitleFieldOfList($langs->trans("DictState"),0,$_SERVER["PHP_SELF"],"active","","",'align="center"',$sortfield,$sortorder);
print getTitleFieldOfList('');
print getTitleFieldOfList('');
print '</tr>';

$object->fetch('',$sortfield,$sortorder);

$var = true;

// Dict Line with values
foreach ($object->lines as $obj)
{
    $var=!$var;
    print '<tr '.$bc[$var].' id="rowid-'.$obj->rowid.'">';
    if ($action == 'edit' && (! empty($id) && $obj->rowid == $id)) {
        print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
        print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
        print '<input type="hidden" name="id" value="'.$obj->rowid.'">';
    }
    print '<td align="left">';
    if ($action == 'edit' && (! empty($id) && $obj->rowid == $id)) {
        print tolist($dictnames, "dict_name", $obj->dict_name);
    }
    else {
        print $obj->dict_name;
    }
    print '</td>';
    print '<td align="left">';
    if ($action == 'edit' && (! empty($id) && $obj->rowid == $id)) {
        print '<input type="text" class="flat" name="libelle" value="'.$obj->libelle.'">';
    }
    else {
        print $obj->libelle;
    }
    print '</td>';
    print '<td align="left">';
    if ($action == 'edit' && (! empty($id) && $obj->rowid == $id)) {
        print '<input size="32" type="text" class="flat" name="desc" value="'.$obj->description.'">';
    }
    else {
        print $obj->description;
    }
    print '</td>';
    if ($action == 'edit' && (! empty($id) && $obj->rowid == $id)) {
        print '<td colspan="3" align="right">';
        print '<input type="submit" class="button" name="actionmodify" value="'.$langs->trans("Modify").'">';
        print '&nbsp;<input type="submit" class="button" name="actioncancel" value="'.$langs->trans("Cancel").'">';
        print '</td>';
        print '</form>'; // close form
    }
    else {
        print '<td align="center" class="nowrap">';
        print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$obj->rowid.'&action='.$acts[$obj->active].'">'.$actl[$obj->active].'</a>';
        print "</td>";
        print '<td align="center"><a class="reposition" href="'.$_SERVER["PHP_SELF"].'?action=edit&id='.$obj->rowid.'">'.img_edit().'</a></td>';
        print '<td align="center"><a href="'.$_SERVER["PHP_SELF"].'?action=delete&id='.$obj->rowid.'">'.img_delete().'</a></td>';
    }
    print '</tr>';
}

print '</table>';

// Page end
dol_fiche_end();
llxFooter();

$db->close();
