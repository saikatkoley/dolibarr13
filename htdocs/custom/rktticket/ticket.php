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
require_once DOL_DOCUMENT_ROOT.$mod_path.'/rktticket/class/rktticketdict.class.php';
require_once DOL_DOCUMENT_ROOT.$mod_path.'/rktticket/lib/rktticket.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

// Load translation files required by the page
$langs->load('companies');
$langs->load("errors");
$langs->load("ticket@ticket");

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$socid = GETPOST('socid', 'int');

// PDF
$hidedetails = (GETPOST('hidedetails', 'int') ? GETPOST('hidedetails', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS) ? 1 : 0));
$hidedesc = (GETPOST('hidedesc', 'int') ? GETPOST('hidedesc', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DESC) ? 1 : 0));
$hideref = (GETPOST('hideref', 'int') ? GETPOST('hideref', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_REF) ? 1 : 0));

// Access control
if ($user->socid > 0 || !$user->rights->rktticket->read) {
	// External user
	accessforbidden();
}

// Default action
if (empty($action) && empty($id) && empty($ref)) {
	$action='create';
}

// Load object if id or ref is provided as parameter
$object = new RktTicket($db);

if (($id > 0 || ! empty($ref)) && $action != 'add') {
	$result = $object->fetch($id, $ref);
	if ($result < 0) {
		dol_print_error($db);
	}
}

/*
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 */

// add
if ($action == 'add') {
        $error = 0;
        $subject = GETPOST('subject', 'alpha');
        $message = GETPOST('message', 'alpha');
        
        /*if ($socid < 1) {
            setEventMessage($langs->transnoentities("ErrorFieldRequired",$langs->transnoentities("Customer")), 'errors');
            $error++;
        }*/
        if (empty($subject)) {
            setEventMessage($langs->transnoentities("ErrorFieldRequired",$langs->transnoentities("Subject")), 'errors');
            $error++;
        }
        if (empty($message)) {
            setEventMessage($langs->transnoentities("ErrorFieldRequired",$langs->transnoentities("Message")), 'errors');
            $error++;
        }
        
        if (! $error)
        {
            $myobject = new RktTicket($db);
            $myobject->ref = $ref;
            $myobject->fk_soc = $socid;
            $myobject->fk_type = GETPOST('fk_type');
            $myobject->fk_category = GETPOST('fk_category');
            $myobject->fk_severity = GETPOST('fk_severity');
            $myobject->sujet = $subject;
            $myobject->message = $message;
            $myobject->creation_date = dol_mktime(12, 0, 0, GETPOST('remonth'), GETPOST('reday'), GETPOST('reyear'));
            $myobject->status = 0;

            $id = $myobject->create($user);
            if ($id > 0) {
                    // Creation OK
                    header('Location: ' . $_SERVER["PHP_SELF"] . '?id=' . $id);
                    exit();
            }
            else
            {
                    // Creation KO
                    setEventMessage($myobject->error, 'errors');
                    $action = 'create';
            }
        }
        else {
            $action = 'create';
        }
}
// Validation
else if ($action == 'confirm_validate' && $confirm == 'yes' && $user->rights->rktticket->create)
{
        $result = $object->valid($user);
        if ($result >= 0)
        {
                // Define output language
                if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
                {
                        $outputlangs = $langs;
                        $newlang = '';
                        if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id')) $newlang = GETPOST('lang_id','alpha');
                        if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $object->thirdparty->default_lang;
                        if (! empty($newlang)) {
                                $outputlangs = new Translate("", $conf);
                                $outputlangs->setDefaultLang($newlang);
                        }
                        $model=$object->modelpdf;
                        $ret = $object->fetch($id); // Reload to get new records

                        $object->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
                }
        }
        else
        {
                $langs->load("errors");
                if (count($object->errors) > 0) setEventMessages($object->error, $object->errors, 'errors');
                else setEventMessages($langs->trans($object->error), null, 'errors');
        }
}
// Assign to
else if ($action == 'setassignedto' && $user->rights->rktticket->assign)
{
        $result = $object->assign_to($user, GETPOST('assigned_to'));
        if ($result >= 0)
        {
                // OK
        } else {
                $langs->load("errors");
                if (count($object->errors) > 0) setEventMessages($object->error, $object->errors, 'errors');
                else setEventMessages($langs->trans($object->error), null, 'errors');
        }
}
// Update date
else if ($action == 'setdate' && $user->rights->rktticket->modify)
{
        $date = dol_mktime(12, 0, 0, $_POST['remonth'], $_POST['reday'], $_POST['reyear']);

        if (empty($date)) {
                $error ++;
                setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Date")), null, 'errors');
        }

        if (! $error) {
                $object->creation_date = $date;
                $result = $object->update($user);
                if ($result < 0) {
                        //dol_print_error($db, $object->error);
                        setEventMessages($object->error, $object->errors, 'errors');
                }
        }
}
// Update type
else if ($action == 'settype' && $user->rights->rktticket->modify)
{
        $fk_type = GETPOST('fk_type', 'int');
        
        if ($object->fk_type != $fk_type)
        {
            $object->fk_type = $fk_type;
            $result = $object->update($user);
            if ($result < 0) {
                    //dol_print_error($db, $object->error);
                    setEventMessages($object->error, $object->errors, 'errors');
            }
        }
}
// Update soc
else if ($action == 'setsoc' && $user->rights->rktticket->modify)
{
        $fk_soc = GETPOST('fk_soc', 'int');
        
        if ($object->fk_soc != $fk_soc)
        {
            $object->fk_soc = $fk_soc;
            $result = $object->update($user);
            if ($result < 0) {
                    //dol_print_error($db, $object->error);
                    setEventMessages($object->error, $object->errors, 'errors');
            }
        }
}
// Update category
else if ($action == 'setcategory' && $user->rights->rktticket->modify)
{
        $fk_category = GETPOST('fk_category', 'int');
        
        if ($object->fk_category != $fk_category)
        {
            $object->fk_category = $fk_category;
            $result = $object->update($user);
            if ($result < 0) {
                    //dol_print_error($db, $object->error);
                    setEventMessages($object->error, $object->errors, 'errors');
            }
        }
}
// Update severity
else if ($action == 'setseverity' && $user->rights->ticket->modify)
{
        $fk_severity = GETPOST('fk_severity', 'int');
        
        if ($object->fk_severity != $fk_severity)
        {
            $object->fk_severity = $fk_severity;
            $result = $object->update($user);
            if ($result < 0) {
                    //dol_print_error($db, $object->error);
                    setEventMessages($object->error, $object->errors, 'errors');
            }
        }
}
// Update subject
else if ($action == 'setsubject' && $user->rights->rktticket->modify)
{
        $subject = GETPOST('subject', 'alpha');

        if (empty($subject)) {
                $error ++;
                setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Subject")), null, 'errors');
        }

        if (! $error) {
                $object->sujet = $subject;
                $result = $object->update($user);
                if ($result < 0) {
                        //dol_print_error($db, $object->error);
                        setEventMessages($object->error, $object->errors, 'errors');
                }
        }
}
// Update message
else if ($action == 'setmessage' && $user->rights->rktticket->modify)
{
        $message = GETPOST('message', 'alpha');

        if (empty($message)) {
                $error ++;
                setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Message")), null, 'errors');
        }

        if (! $error) {
                $object->message = $message;
                $result = $object->update($user);
                if ($result < 0) {
                        //dol_print_error($db, $object->error);
                        setEventMessages($object->error, $object->errors, 'errors');
                }
        }
}
// Close ticket
else if ($action == 'confirm_close' && $confirm == 'yes' && $user->rights->rktticket->close)
{
        $object->status = Ticket::STATUS_CLOSED;
        $result = $object->update($user);
        if ($result < 0) {
                setEventMessages($object->error, $object->errors, 'errors');
        }
}
// Reopen ticket
else if ($action == 'confirm_reopen' && $confirm == 'yes' && $user->rights->rktticket->close)
{
        $object->status = RktTicket::STATUS_VALIDATED;
        $result = $object->update($user);
        if ($result < 0) {
                setEventMessages($object->error, $object->errors, 'errors');
        }
}
// Delete ticket
else if ($action == 'confirm_delete' && $confirm == 'yes' && $user->rights->rktticket->delete)
{
        $result = $object->delete($user);
        if ($result > 0) {
                header('Location: ' . DOL_URL_ROOT . $mod_path . '/rktticket/list.php');
                exit();
        } else {
                $langs->load("errors");
                setEventMessages($object->error, $object->errors, 'errors');
        }
}

// Build doc
else if ($action == 'builddoc')
{
        // Save last template used to generate document
        if (GETPOST('model')) $object->setDocModel($user, GETPOST('model', 'alpha'));
        
        // Define output language
        $outputlangs = $langs;
        $newlang = '';
        if ($conf->global->MAIN_MULTILANGS && empty($newlang) && ! empty($_REQUEST['lang_id']))
                $newlang = $_REQUEST['lang_id'];
        if ($conf->global->MAIN_MULTILANGS && empty($newlang))
                $newlang = $object->thirdparty->default_lang;
        if (! empty($newlang)) {
                $outputlangs = new Translate("", $conf);
                $outputlangs->setDefaultLang($newlang);
        }
        $result = $object->generateDocument($object->modelpdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
        if ($result <= 0)
        {
                setEventMessages($object->error, $object->errors, 'errors');
                $action='';
        }
}

// Remove file in doc form
if ($action == 'remove_file')
{
        if ($object->id > 0)
        {
                require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

                $langs->load("other");
                $upload_dir = $conf->rktticket->dir_output;
                $file = $upload_dir . '/' . GETPOST('file');
                $ret = dol_delete_file($file, 0, 0, 0, $object);
                if ($ret)
                        setEventMessages($langs->trans("FileWasRemoved", GETPOST('file')), null, 'mesgs');
                else
                        setEventMessages($langs->trans("ErrorFailToDeleteFile", GETPOST('file')), null, 'errors');
                $action = '';
        }
}

/*
 * Send mail
 */

// Actions to send emails
$actiontypecode='AC_COM';
$trigger_name='TICKET_SENTBYMAIL';
$paramname='id';
$mode='emailfromticket';
$trackid='tick'.$object->id;
include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';


/*
 * VIEW
 *
 * Put here all code to build page
 */

llxHeader('', $langs->trans('TicketIndexPageName'), '');

$form = new Form($db);
$formfile = new FormFile($db);

/**
 * *******************************************************************
 *
 * Mode creation
 *
 * *******************************************************************
 */
if ($action == 'create' && $user->rights->rktticket->create)
{
    print load_fiche_titre($langs->trans('NewTicket'));
    
    $soc = new Societe($db);
    if ($socid > 0) {
            $res = $soc->fetch($socid);
    }
    
    print '<form name="addticket" action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
    print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
    print '<input type="hidden" name="action" value="add">';

    dol_fiche_head();

    print '<table class="border" width="100%">';
    
    // Reference
    print '<tr><td width="25%">' . $langs->trans('Ref') . '</td><td colspan="2">' . $langs->trans("Draft") . '</td></tr>';
    
    // Third party
    print '<tr>';
    print '<td>' . $langs->trans('Customer') . '</td>';
    if ($socid > 0) {
            print '<td colspan="2">';
            print $soc->getNomUrl(1);
            print '<input type="hidden" name="socid" value="' . $soc->id . '">';
            print '</td>';
    } else {
            print '<td colspan="2">';
            print $form->select_company('', 'socid', '(s.client = 1 OR s.client = 2 OR s.client = 3) AND status=1', 1);
            // reload page to retrieve customer informations
            if (!empty($conf->global->RELOAD_PAGE_ON_CUSTOMER_CHANGE))
            {
                    print '<script type="text/javascript">
                    $(document).ready(function() {
                            $("#socid").change(function() {
                                    var socid = $(this).val();
                                    // reload page
                                    window.location.href = "'.$_SERVER["PHP_SELF"].'?action=create&socid="+socid+"&ref_client="+$("input[name=ref_client]").val();
                            });
                    });
                    </script>';
            }
            print '</td>';
    }
    print '</tr>' . "\n";
    
    // Date
    print '<tr><td class="fieldrequired">' . $langs->trans('Date') . '</td><td colspan="2">';
    $date = dol_mktime(12, 0, 0, GETPOST('remonth'), GETPOST('reday'), GETPOST('reyear'));
    $form->select_date($date, 're', '', '', '', "addticket", 1, 1);
    print '</td></tr>';
    
    $dict = new RktTicketDict($db);
    
    // Type
    $dict->fetch('type');
    print '<tr><td class="nowrap fieldrequired">' . $langs->trans('Type') . '</td><td colspan="2">';
    printdictlist($dict->lines, "fk_type", GETPOST('fk_type'));
    if ($user->admin) print ' '.info_admin($langs->trans("YouCanChangeValuesForThisListFromModuleDictionarySetup"),1);
    print '</td></tr>';
    
    // Category
    $dict->fetch('category');
    print '<tr><td class="nowrap fieldrequired">' . $langs->trans('Category') . '</td><td colspan="2">';
    printdictlist($dict->lines, "fk_category", GETPOST('fk_category'));
    if ($user->admin) print ' '.info_admin($langs->trans("YouCanChangeValuesForThisListFromModuleDictionarySetup"),1);
    print '</td></tr>';
    
    // Severity
    $dict->fetch('severity');
    print '<tr><td class="nowrap fieldrequired">' . $langs->trans('Severity') . '</td><td colspan="2">';
    printdictlist($dict->lines, "fk_severity", GETPOST('fk_severity'));
    if ($user->admin) print ' '.info_admin($langs->trans("YouCanChangeValuesForThisListFromModuleDictionarySetup"),1);
    print '</td></tr>';
    
    // Subject
    print '<tr><td class="fieldrequired">' . $langs->trans('Subject') . '</td><td colspan="2">';
    print '<input size="56" type="text" name="subject" value="'.GETPOST('subject').'"></td>';
    print '</tr>';
    
    // Message
    print '<tr>';
    print '<td class="fieldrequired" valign="top">' . $langs->trans('Message') . '</td>';
    print '<td valign="top" colspan="2">';
    $doleditor = new DolEditor('message', GETPOST('message'), '', 80, 'dolibarr_notes', 'In', 0, false, true, ROWS_5, '90%');
    print $doleditor->Create(1);
    print '</td></tr>';
    
    print '</table>';
    
    dol_fiche_end();
    
    print '<div class="center">';
    print '<input type="submit" class="button" value="' . $langs->trans("CreateTicket") . '">';
    print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    print '<input type="button" class="button" value="' . $langs->trans("Cancel") . '" onClick="javascript:history.go(-1)">';
    print '</div>';

    print "</form>";
}

/**
 * *******************************************************************
 *
 * Mode vue/édition
 *
 * *******************************************************************
 */
else if ($object->id > 0 || ! empty($object->ref))
{
    $soc = new Societe($db);
    $soc->fetch($object->fk_soc);
    
    $head = rktticket_prepare_head($object, $mod_path);
    dol_fiche_head($head, 'ticket', $langs->trans('Ticket'), 0, 'ticket@ticket');
    
    $formconfirm = '';
    
    /*
     * View actions
     */
    // Confirm delete
    if ($action == 'delete') {
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteTicket'), $langs->trans('ConfirmDeleteTicket', $object->ref), 'confirm_delete', '', 0, 1);
    }
    // Confirm close
    else if ($action == 'close') {
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('CloseTicket'), $langs->trans('ConfirmCloseTicket', $object->ref), 'confirm_close', '', 0, 1);
    }
    // Confirm reopen
    else if ($action == 'reopen') {
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ReopenTicket'), $langs->trans('ConfirmReopenTicket', $object->ref), 'confirm_reopen', '', 0, 1);
    }
    // Confirm validate
    else if ($action == 'validate') {
            $error = 0;

            // We verifie whether the object is provisionally numbering
            $ref = substr($object->ref, 1, 4);
            if ($ref == 'PROV') {
                    $numref = $object->getNextNumRef($soc);
                    if (empty($numref)) {
                            $error ++;
                            setEventMessages($object->error, $object->errors, 'errors');
                    }
            } else {
                    $numref = $object->ref;
            }
            
            $text = $langs->trans('ConfirmValidateTicket', $numref);

            if (! $error) {
                    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ValidateTicket'), $text, 'confirm_validate', '', 0, 1);
            }
    }
    
    // Print form confirm
    print $formconfirm;
    
    print '<table class="border" width="100%">';

    $linkback = '<a href="' . DOL_URL_ROOT . $mod_path . '/rktticket/list.php' . (! empty($socid) ? '?socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

    // Ref
    print '<tr><td class="titlefield">' . $langs->trans('Ref') . '</td><td>';
    print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref', '');
    print '</td></tr>';
    
    $userstatic = new User($db);
    
    // Created by
    $userstatic->fetch($object->created_by);
    print '<tr><td>' . $langs->trans('CreatedBy') . '</td><td>' . $userstatic->getNomUrl(1) . '</td>';
    print '</tr>';
    
    // Assigned to
    if ($object->assigned_to > 0) {
        $userstatic->fetch($object->assigned_to);
    }
    print '<tr>';
    print '<td>';
    print '<table class="nobordernopadding" width="100%"><tr><td>';
    print $langs->trans('AssignedTo') . '</td>';
    if ($action != 'editassignedto' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->assign) {
            print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editassignedto&amp;id=' . $object->id . '">' . img_edit($langs->trans('Modify'), 1) . '</a></td>';
    }
    print '</tr></table>';
    print '</td><td>';
    if ($action == 'editassignedto' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->assign) {
        print '<form name="editassignedto" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post">';
        print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
        print '<input type="hidden" name="action" value="setassignedto">';
        print $form->select_dolusers($object->assigned_to, 'assigned_to', 1, '', 0, '', '', 0, 0, 0, '', 0, '', 'maxwidth300');
        print ' <input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
        print '</form>';
    }
    else {
        print $object->assigned_to > 0 ? $userstatic->getNomUrl(1).(! empty($userstatic->email) ? " &lt;".$userstatic->email."&gt;" : "") : $langs->trans('None');
    }
    print '</td></tr>';
    
    // Company
    print '<tr>';
    print '<td>';
    print '<table class="nobordernopadding" width="100%"><tr><td>';
    print $langs->trans('Company');
    print '</td>';
    if ($action != 'editsoc' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->modify) {
            print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editsoc&amp;id=' . $object->id . '">' . img_edit($langs->trans('Modify'), 1) . '</a></td>';
    }
    print '</tr></table>';
    print '</td><td>';
    if ($action == 'editsoc' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->modify) {
            print '<form name="editsoc" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post">';
            print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
            print '<input type="hidden" name="action" value="setsoc">';
            print $form->select_company($object->fk_soc, 'fk_soc', '(s.client = 1 OR s.client = 2 OR s.client = 3) AND status=1', 1);
            print '<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
            print '</form>';
    } else {
            if ($object->fk_soc > 0) {
                print $soc->getNomUrl(1);
            }
            else {
                print $langs->trans("None");
            }
    }
    print '</td></tr>';

    // Date
    print '<tr>';
    print '<td>';
    print '<table class="nobordernopadding" width="100%"><tr><td>';
    print $langs->trans('Date');
    print '</td>';
    if ($action != 'editdate' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->modify) {
            print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editdate&amp;id=' . $object->id . '">' . img_edit($langs->trans('SetDate'), 1) . '</a></td>';
    }
    print '</tr></table>';
    print '</td><td>';
    if ($action == 'editdate' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->modify) {
            print '<form name="editdate" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post">';
            print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
            print '<input type="hidden" name="action" value="setdate">';
            $form->select_date($object->creation_date, 're', '', '', 0, "editdate");
            print '<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
            print '</form>';
    } else {
            if ($object->creation_date) {
                    print dol_print_date($object->creation_date, 'daytext');
            } else {
                    print '&nbsp;';
            }
    }
    print '</td></tr>';
    
    $dict = new RktTicketDict($db);
    $dictlibelles = $dict->getdictlibelles();
    
    // Type
    print '<tr>';
    print '<td>';
    print '<table class="nobordernopadding" width="100%"><tr><td>';
    print $langs->trans('Type');
    print '</td>';
    if ($action != 'edittype' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->modify) {
            print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=edittype&amp;id=' . $object->id . '">' . img_edit($langs->trans('Modify'), 1) . '</a></td>';
    }
    print '</tr></table>';
    print '</td><td>';
    if ($action == 'edittype' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->modify) {
            print '<form name="editdate" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post">';
            print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
            print '<input type="hidden" name="action" value="settype">';
            $dict->fetch('type');
            printdictlist($dict->lines, "fk_type", $object->fk_type);
            print '&nbsp;<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
            print '</form>';
    } else {
            print $dictlibelles[$object->fk_type];
    }
    print '</td></tr>';
    
    // Category
    print '<tr>';
    print '<td>';
    print '<table class="nobordernopadding" width="100%"><tr><td>';
    print $langs->trans('Category');
    print '</td>';
    if ($action != 'editcategory' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->modify) {
            print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editcategory&amp;id=' . $object->id . '">' . img_edit($langs->trans('Modify'), 1) . '</a></td>';
    }
    print '</tr></table>';
    print '</td><td>';
    if ($action == 'editcategory' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->modify) {
            print '<form name="editcategory" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post">';
            print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
            print '<input type="hidden" name="action" value="setcategory">';
            $dict->fetch('category');
            printdictlist($dict->lines, "fk_category", $object->fk_category);
            print '&nbsp;<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
            print '</form>';
    } else {
            print $dictlibelles[$object->fk_category];
    }
    print '</td></tr>';
    
    // Severity
    print '<tr>';
    print '<td>';
    print '<table class="nobordernopadding" width="100%"><tr><td>';
    print $langs->trans('Severity');
    print '</td>';
    if ($action != 'editseverity' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->modify) {
            print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editseverity&amp;id=' . $object->id . '">' . img_edit($langs->trans('Modify'), 1) . '</a></td>';
    }
    print '</tr></table>';
    print '</td><td>';
    if ($action == 'editseverity' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->modify) {
            print '<form name="editcategory" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post">';
            print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
            print '<input type="hidden" name="action" value="setseverity">';
            $dict->fetch('severity');
            printdictlist($dict->lines, "fk_severity", $object->fk_severity);
            print '&nbsp;<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
            print '</form>';
    } else {
            print $dictlibelles[$object->fk_severity];
    }
    print '</td></tr>';
    
    // Subject
    print '<tr>';
    print '<td>';
    print '<table class="nobordernopadding" width="100%"><tr><td>';
    print $langs->trans('Subject');
    print '</td>';
    if ($action != 'editsubject' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->modify) {
            print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editsubject&amp;id=' . $object->id . '">' . img_edit($langs->trans('Modify'), 1) . '</a></td>';
    }
    print '</tr></table>';
    print '</td><td>';
    if ($action == 'editsubject' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->modify) {
            print '<form name="editsubject" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post">';
            print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
            print '<input type="hidden" name="action" value="setsubject">';
            print '<input size="56" name="subject" value="'.$object->sujet.'">';
            print '&nbsp;<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
            print '</form>';
    } else {
            print $object->sujet;
    }
    print '</td></tr>';
    
    // Message
    print '<tr>';
    print '<td>';
    print '<table class="nobordernopadding" width="100%"><tr><td>';
    print $langs->trans('Message');
    print '</td>';
    if ($action != 'editmessage' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->modify) {
            print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=editmessage&amp;id=' . $object->id . '">' . img_edit($langs->trans('Modify'), 1) . '</a></td>';
    }
    print '</tr></table>';
    print '</td><td>';
    if ($action == 'editmessage' && ($object->brouillon || $object->status == RktTicket::STATUS_VALIDATED) && $user->rights->rktticket->modify) {
            print '<form name="editmessage" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="post">';
            print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
            print '<input type="hidden" name="action" value="setmessage">';
            $doleditor = new DolEditor('message', $object->message, '', 80, 'dolibarr_notes', 'In', 0, false, true, ROWS_3, '90%');
            print $doleditor->Create(1);
            print '<br><input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
            print '</form>';
    } else {
            $message = preg_replace(
                '#((https?|ftp)://(\S*?\.\S*?))([\s)\[\]{},"\':<]|\.\s|$)#i',
                "<a href=\"$1\" target=\"_blank\">$1</a>$4",
                $object->message
            );
            print $message;
    }
    print '</td></tr>';
    
    // Statut
    print '<tr><td height="10">' . $langs->trans('Status') . '</td><td align="left" colspan="2">' . $object->getLibStatut(4) . '</td></tr>';
    
    print '</table>';
    
    dol_fiche_end();
    
    /*
     * Boutons Actions
     */
    if ($action != 'presend')
    {
        print '<div class="tabsAction">';
        // Send
        if ($object->status > RktTicket::STATUS_DRAFT) {
                if ($user->rights->rktticket->send) {
                        print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=presend&amp;mode=init">' . $langs->trans('SendByMail') . '</a></div>';
                } else {
                        print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">' . $langs->trans('SendByMail') . '</a></div>';
                }
        }
        // Validate
        if ($object->status == RktTicket::STATUS_DRAFT)
        {
            if ($user->rights->rktticket->create)
            {
                    print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=validate">' . $langs->trans('Validate') . '</a></div>';
            }
            else
            {
                    print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">' . $langs->trans('Validate') . '</a></div>';
            }
        }
        // Close
        if ($object->status == RktTicket::STATUS_VALIDATED && $user->rights->rktticket->close) {
                print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=close">' . $langs->trans('Close') . '</a></div>';
        }
        // Re-open
        if ($object->status == RktTicket::STATUS_CLOSED && $user->rights->rktticket->close) {//&& $user->rights->ticket->create) {
                print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=reopen">' . $langs->trans('Reopen') . '</a></div>';
        }
        // Delete
        if ($user->rights->rktticket->delete) {
                print '<div class="inline-block divButAction"><a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=delete">' . $langs->trans('Delete') . '</a></div>';
        }
        print '</div>';
    }
    
    // Select mail models is same action as presend
    if (GETPOST('modelselected')) {
        $action = 'presend';
    }
    
    // Documents block
    if ($action != 'presend')
    {
            print '<div class="fichecenter"><div class="fichehalfleft">';

            // Documents
            $ticref = dol_sanitizeFileName($object->ref);
            $file = $conf->rktticket->dir_output . '/' . $ticref . '/' . $ticref . '.pdf';
            $relativepath = $ticref . '/' . $ticref . '.pdf';
            $filedir = $conf->rktticket->dir_output . '/' . $ticref;
            $urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
            $genallowed = $user->rights->rktticket->create;
            $delallowed = $user->rights->rktticket->delete;
            print $formfile->showdocuments('rktticket', $ticref, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);

            print '</div></div>';

            /*
             * Related objects
             */

            print '<div class="fichecenter"><div class="fichehalfleft">';

            $permissiondellink = $user->rights->rktticket->create;    // Used by the include of actions_dellink.inc.php

            include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';      // Must be include, not include_once

            // Show links to link elements
            $linktoelem = $form->showLinkToObjectBlock($object, null, array('ticket'));
            $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);

            print '</div></div>';
    }
    
    /*
     * Action presend
     */
    if ($action == 'presend')
    {
            $object->thirdparty = new Societe($db);
            $object->thirdparty->fetch($object->fk_soc);
            
            $ref = dol_sanitizeFileName($object->ref);
            include_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
            $fileparams = dol_most_recent_file($conf->ticket->dir_output . '/' . $ref, preg_quote($ref, '/').'[^\-]+');
            $file = $fileparams['fullname'];

            // Define output language
            $outputlangs = $langs;
            $newlang = '';
            if ($conf->global->MAIN_MULTILANGS && empty($newlang) && ! empty($_REQUEST['lang_id']))
                    $newlang = $_REQUEST['lang_id'];
            if ($conf->global->MAIN_MULTILANGS && empty($newlang))
                    $newlang = $object->thirdparty->default_lang;

            if (!empty($newlang))
            {
                    $outputlangs = new Translate('', $conf);
                    $outputlangs->setDefaultLang($newlang);
                    $outputlangs->load('ticket');
            }

            // Build document if it not exists
            if (! $file || ! is_readable($file)) {
                    $result = $object->generateDocument(GETPOST('model') ? GETPOST('model') : $object->modelpdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
                    if ($result <= 0) {
                            dol_print_error($db, $object->error, $object->errors);
                            exit();
                    }
                    $fileparams = dol_most_recent_file($conf->rktticket->dir_output . '/' . $ref, preg_quote($ref, '/').'[^\-]+');
                    $file = $fileparams['fullname'];
            }

            print '<div class="clearboth"></div>';
            print '<br>';
            print load_fiche_titre($langs->trans('SendTicketByMail'));

            dol_fiche_head('');
            
            // Cree l'objet formulaire mail
            include_once DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php';
            $formmail = new FormMail($db);
            $formmail->param['langsmodels']=(empty($newlang)?$langs->defaultlang:$newlang);
            $formmail->fromtype = (GETPOST('fromtype')?GETPOST('fromtype'):(!empty($conf->global->MAIN_MAIL_DEFAULT_FROMTYPE)?$conf->global->MAIN_MAIL_DEFAULT_FROMTYPE:'user'));
            if($formmail->fromtype === 'user'){
                $formmail->fromid = $user->id;
                $formmail->frommail = $user->email; // fix for dolibarr 3.9
            }
            $formmail->trackid='tick'.$object->id;
            if (! empty($conf->global->MAIN_EMAIL_ADD_TRACK_ID) && ($conf->global->MAIN_EMAIL_ADD_TRACK_ID & 2))	// If bit 2 is set
            {
                    include DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
                    $formmail->frommail=dolAddEmailTrackId($formmail->frommail, 'tick'.$object->id);
            }
            $formmail->withfrom = 1;
            $liste = array();
            if ($object->fk_soc > 0) {
                foreach ($object->thirdparty->thirdparty_and_contact_email_array(1) as $key => $value) {
                        $liste [$key] = $value;
                }
            }
            // Add Assigned user email
            if ($object->assigned_to > 0) {
                $assigned_to_name = $userstatic->getFullName($langs,'');
                $liste['assigneduser'] = $langs->trans("AssignedTo").': '.dol_trunc($assigned_to_name,16)." &lt;".$userstatic->email."&gt;";
            }
            $formmail->withto = GETPOST('sendto') ? GETPOST('sendto') : $liste;
            $formmail->withtocc = $liste;
            $formmail->withtoccc = $conf->global->MAIN_EMAIL_USECCC;
            $formmail->withtopic = $outputlangs->trans('SendTicketRef', '__TICKETREF__');
            $formmail->withfile = 2;
            $formmail->withbody = $langs->trans('TicketMailTemplate');
            $formmail->withdeliveryreceipt = 1;
            $formmail->withcancel = 1;
            // Tableau des substitutions
            if (method_exists($formmail,"setSubstitFromObject")) { // fix for dolibarr 3.9
                $formmail->setSubstitFromObject($object, $langs);
                $formmail->substit ['__CONTACTCIVNAME__'] = '';
                $formmail->substit ['__PERSONALIZED__'] = '';
            }
            else {
                $formmail->substit ['__CONTACTCIVNAME__'] = '';
                $formmail->substit ['__PERSONALIZED__'] = '';
                $formmail->substit ['__SIGNATURE__'] = $user->signature;
            }
            $formmail->substit ['__TICKETREF__'] = $object->ref;

            $custcontact = '';
            $contactarr = array();
            $contactarr = $object->liste_contact(- 1, 'external');

            if (is_array($contactarr) && count($contactarr) > 0)
            {
                    foreach ($contactarr as $contact)
                    {
                            if ($contact['libelle'] == $langs->trans('TypeContact_ticket_external_CUSTOMER')) {	// TODO Use code and not label
                                    $contactstatic = new Contact($db);
                                    $contactstatic->fetch($contact['id']);
                                    $custcontact = $contactstatic->getFullName($langs, 1);
                            }
                    }

                    if (! empty($custcontact)) {
                            $formmail->substit['__CONTACTCIVNAME__'] = $custcontact;
                    }
            }

            // Tableau des parametres complementaires
            $formmail->param['action'] = 'send';
            $formmail->param['models'] = 'ticket_send';
            $formmail->param['models_id']=GETPOST('modelmailselected','int');
            $formmail->param['ticketid'] = $object->id;
            $formmail->param['returnurl'] = $_SERVER["PHP_SELF"] . '?id=' . $object->id;
            $formmail->param['fileinit'] = array($file);

            // Init list of files
            if (GETPOST("mode") == 'init') {
                $formmail->clear_attached_files();
                $formmail->add_attached_files($file, basename($file), dol_mimetype($file));
            }

            // Show form
            print $formmail->get_form();

            dol_fiche_end();
    }
}

// End of page
llxFooter();
