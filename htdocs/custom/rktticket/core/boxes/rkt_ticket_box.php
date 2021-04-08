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
 * \file    core/boxes/mybox.php
 * \ingroup mymodule
 * \brief   Example box definition.
 *
 * Put detailed description here.
 */

/** Includes */
include_once DOL_DOCUMENT_ROOT . "/core/boxes/modules_boxes.php";

/**
 * Class to manage the box
 *
 * Warning: for the box to be detected correctly by dolibarr,
 * the filename should be the lowercase classname
 */
class rkt_ticket_box extends ModeleBoxes
{
	/**
	 * @var string Alphanumeric ID. Populated by the constructor.
	 */
	public $boxcode = "lasttickets";

	/**
	 * @var string Box icon (in configuration page)
	 * Automatically calls the icon named with the corresponding "object_" prefix
	 */
	public $boximg = "ticket@rktticket";

	/**
	 * @var string Box label (in configuration page)
	 */
	public $boxlabel;

	/**
	 * @var string[] Module dependencies
	 */
	public $depends = array('rktticket'); // underscores in module name '_' not work 

	/**
	 * @var DoliDb Database handler
	 */
	public $db;

	/**
	 * @var mixed More parameters
	 */
	public $param;

	/**
	 * @var array Header informations. Usually created at runtime by loadBox().
	 */
	public $info_box_head = array();

	/**
	 * @var array Contents informations. Usually created at runtime by loadBox().
	 */
	public $info_box_contents = array();

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 * @param string $param More parameters
	 */
	public function __construct(DoliDB $db, $param = '')
	{
		global $langs;
		$langs->load("boxes");
		$langs->load('ticket@ticket');

		parent::__construct($db, $param);

		$this->boxlabel = $langs->transnoentitiesnoconv("BoxLastTickets");

		$this->param = $param;
	}

	/**
	 * Load data into info_box_contents array to show array later. Called by Dolibarr before displaying the box.
	 *
	 * @param int $max Maximum number of records to load
	 * @return void
	 */
	public function loadBox($max = 5)
	{
		global $user, $langs, $db, $conf;

		// Use configuration value for max lines count
		$this->max = $max;

		//include_once DOL_DOCUMENT_ROOT . "/mymodule/class/mymodule.class.php";
                dol_include_once('/custom/rktticket/class/rktticket.class.php');
                include_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
                
                $ticketstatic = new RktTicket($db);
                $societestatic = new Societe($db);

		// Populate the head at runtime
		$text = $langs->trans("BoxTitleLastTickets", $max);
		$this->info_box_head = array(
			// Title text
			'text' => $text,
			// Add a link
			'sublink' => dol_buildpath('/custom/rktticket/ticket.php?mainmenu=rktticket&leftmenu=rkttickets&action=create', 1),
			// Sublink icon placed after the text
			'subpicto' => 'filenew',
			// Sublink icon HTML alt text
			'subtext' => $langs->trans('NewTicket'),
			// Sublink HTML target
			'target' => '',
			// HTML class attached to the picto and link
			'subclass' => 'center'.'" style="margin-right: 5px;', // @TODO: remove the style hack
			// Limit and truncate with "…" the displayed text lenght, 0 = disabled
			'limit' => 0,
			// Adds translated " (Graph)" to a hidden form value's input (?)
			'graph' => false
		);
                
                if ($user->rights->rktticket->read)
                {
                    $sql = "SELECT t.rowid, t.ref, t.fk_soc, s.nom as soc_name, t.fk_type, t.fk_category, t.fk_severity";
                    $sql.= ", t.sujet, t.created_by, t.assigned_to, t.creation_date, t.status";
                    $sql.= ", s.rowid as socid, s.nom as socname, s.canvas";
                    $sql.= " FROM " . MAIN_DB_PREFIX . "rkt_ticket as t";
                    $sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON t.fk_soc = s.rowid";
                    //$sql.= " WHERE t.entity IN(".getEntity('user', 1).")"; // t.entity = $conf->entity;
                    $sql.= " ORDER BY t.creation_date DESC";
                    $sql.= $db->plimit($max, 0);

                    $result = $db->query($sql);
                    if ($result) {
                        $num = $db->num_rows($result);

                        $line = 0;

                        while ($line < $num) {
                            $objp = $db->fetch_object($result);
                            
                            $ticketstatic->id = $objp->rowid;
                            $ticketstatic->ref = $objp->ref;
                            $date = $db->jdate($objp->creation_date);
                            
                            $societestatic->id = $objp->socid;
                            $societestatic->name = $objp->socname;
                            $societestatic->canvas = $objp->canvas;

                            $this->info_box_contents[$line][] = array(
                                'td' => 'align="left"',
                                'text' => $ticketstatic->getNomUrl(1),
                                'asis' => 1,
                            );

                            $this->info_box_contents[$line][] = array(
                                'td' => 'align="left"',
                                'text' => ($objp->fk_soc > 0 ? $societestatic->getNomUrl(1,'customer') : '-'),
                                'asis' => 1,
                            );

                            $this->info_box_contents[$line][] = array(
                                'td' => 'align="left"',
                                'text' => dol_print_date($date,'day'),
                            );

                            $this->info_box_contents[$line][] = array(
                                'td' => 'align="right" width="18"',
                                'text' => $ticketstatic->LibStatut($objp->status,3),
                            );

                            $line++;
                        }

                        if ($num==0) $this->info_box_contents[$line][0] = array('td' => 'align="center"','text'=>$langs->trans("NoRecordedTickets"));

                        $db->free($result);
                    } else {
                        $this->info_box_contents[0][0] = array(
                            'td' => 'align="left"',
                            'maxlength'=>500,
                            'text' => ($db->error().' sql='.$sql),
                        );
                    }
                } else {
                    $this->info_box_contents[0][0] = array(
                        'align' => 'left',
                        'text' => $langs->trans("ReadPermissionNotAllowed"),
                    );
                }
	}

	/**
	 * Method to show box. Called by Dolibarr eatch time it wants to display the box.
	 *
	 * @param array $head Array with properties of box title
	 * @param array $contents Array with properties of box lines
	 * @return void
	 */
	public function showBox($head = null, $contents = null, $nooutput = 0)
	{
		// You may make your own code here…
		// … or use the parent's class function using the provided head and contents templates
		parent::showBox($this->info_box_head, $this->info_box_contents);
	}
}
