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
 * \file    lib/....lib.php
 * \ingroup ...
 * \brief   Example module library.
 *
 * Put detailed description here.
 */

/**
 * Prepare array with list of tabs
 *
 * @param   object	$object		Object related to tabs
 * @return  array				Array of tabs to show
 */
function rktticket_prepare_head($object, $mod_path='')
{
	global $langs, $conf, $user;
	$langs->load("ticket@ticket");

	$h = 0;
	$head = array();

	$head[$h][0] = DOL_URL_ROOT.$mod_path.'/rktticket/ticket.php?id='.$object->id;
	$head[$h][1] = $langs->trans('TicketCard');
	$head[$h][2] = 'ticket';
	$h++;

        // Show more tabs from modules
        // Entries must be declared in modules descriptor with line
        // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
        // $this->tabs = array('entity:-tabname);   												to remove a tab
        complete_head_from_modules($conf,$langs,$object,$head,$h,'rktticket');

	//complete_head_from_modules($conf,$langs,$object,$head,$h,'ticket','remove');

	return $head;
}

/**
 * Prepare admin pages header
 *
 * @return array
 */
function rktticketAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("ticket@ticket");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/custom/rktticket/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;
        $head[$h][0] = dol_buildpath("/custom/rktticket/admin/dict.php", 1);
	$head[$h][1] = $langs->trans("Dict");
	$head[$h][2] = 'dict';
	$h++;
	$head[$h][0] = dol_buildpath("/custom/rktticket/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@ticket:/rktticket/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@ticket:/rktticket/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'ticket');

	return $head;
}

function tolist($elements, $name, $value='')
{
    $out = "";
    
    // if empty elements
    if (count($elements) == 0) {
        $out.= '<input size="16" type="text" class="flat" name="'.$name.'" value="'.$value.'">';
    }
    else {
        $out.= '<select name="'.$name.'">';

        foreach ($elements as $element)
        {
            $out.= '<option value="'.$element.'"'.(! empty($value) && $value == $element ? " selected" : "").'>'.$element.'</option>';
        }

        $out.= '</select>';
    }
    
    return $out;
}

function printdictlist($dictlines, $name, $selected='')
{
    print '<select name="'.$name.'">';
    
    foreach ($dictlines as $dictline)
    {
        print '<option value="'.$dictline->rowid.'"'.(! empty($selected) && $selected == $dictline->rowid ? " selected" : "").'>'.$dictline->libelle.'</option>';
    }

    print '</select>';
}
