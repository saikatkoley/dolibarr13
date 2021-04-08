<?php
/* Copyright (C) 2016-2017 AXeL dev <contact.axel.dev@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\defgroup   ...     Module de ...
 *	\brief      Module pour gerer ...
 *	\file       htdocs/.../core/modules/mod....class.php
 *	\ingroup    ...
 *	\brief      Fichier de description et activation du module ...
 */

include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


/**
 *	Class to describe and enable module Expedition
 */
class modRktTicket extends DolibarrModules
{

	/**
	 *   Constructor. Define names, constants, directories, boxes, permissions
	 *
	 *   @param      DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
		global $conf;

		$this->db = $db;
		$this->editor_name = 'AXeL';
		$this->numero = 600001;
		// key to reference module (for permissions, menus, etc.)
		$this->rights_class = 'rktticket';

		// Can be one of 'crm', 'financial', 'hr', 'projects', 'products', 'ecm', 'technic', 'other'
		$this->family = "other";
		$this->module_position = 100;
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		$this->description = "Ticket management";

		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '1.5.4';

		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->special = 0;
		$this->picto = "ticket@ticket";

		// Module parts (css, js, ...)
		$this->module_parts = array();

		// Data directories to create when module is enabled
		$this->dirs = array("/custom/rktticket/temp");

		// Config pages
		$this->config_page_url = array("setup.php@rktticket");

		// Dependencies
		$this->depends = array("modSociete");
		$this->requiredby = array();
		$this->conflictwith = array();
		$this->langfiles = array('ticket@ticket');

		// Constants
		$this->const = array();
        $r=0;
                
		$this->const[$r][0] = "TICKET_ADDON";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "mod_rktticket_marbre";
		$this->const[$r][3] = 'Name of numbering numerotation rules of ticket';
		$this->const[$r][4] = 0;
        $r++;
                
        $this->const[$r][0] = "TICKET_ADDON_PDF";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "einstein";
		$this->const[$r][3] = 'Name of PDF model of ticket';
		$this->const[$r][4] = 0;
        $r++;
                
        $this->const[$r][0] = "TICKET_ALWAYS_SHOW_LIST_MENU";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "1";
		$this->const[$r][3] = 'Always show ticket list menu';
		$this->const[$r][4] = 0;
        $r++;
		
		// Boxes
		$this->boxes = array(
			0 => array(
				'file' => 'rkt_ticket_box@rktticket',
				'note' => '',
				'enabledbydefaulton' => 'Home'
			)
		);

		// Permissions
		$this->rights = array();

		$r=0;
		
		$this->rights[$r][0] = 600002;
		$this->rights[$r][1] = 'Read tickets';
		$this->rights[$r][2] = 'r';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'read';
        $r++;
                
        $this->rights[$r][0] = 600003;
		$this->rights[$r][1] = 'Create ticket';
		$this->rights[$r][2] = 'c';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'create';
        $r++;
                
        $this->rights[$r][0] = 600004;
		$this->rights[$r][1] = 'Edit tickets';
		$this->rights[$r][2] = 'm';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'modify';
        $r++;
                
        $this->rights[$r][0] = 600005;
		$this->rights[$r][1] = 'Delete tickets';
		$this->rights[$r][2] = 'd';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'delete';
        $r++;
                
        $this->rights[$r][0] = 600006;
		$this->rights[$r][1] = 'Assign tickets';
		$this->rights[$r][2] = 'a';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'assign';
        $r++;
                
        $this->rights[$r][0] = 600007;
		$this->rights[$r][1] = 'Close tickets';
		$this->rights[$r][2] = 'c';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'close';
        $r++;
                
        $this->rights[$r][0] = 600008;
		$this->rights[$r][1] = 'Send tickets';
		$this->rights[$r][2] = 's';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'send';
        $r++;

		// Main menu entries
		$this->menu = array();
		$r=0;

		// Top Menu entry:
		$this->menu[$r]=array('fk_menu'=>0,
					'type'=>'top',
					'titre'=>'Ticket',
					'mainmenu'=>'rktticket',
					'leftmenu'=>'',
					'url'=>'/custom/rktticket/index.php',
					'langs'=>'ticket@ticket',
					'position'=>100,
					'enabled'=>'1',
					'perms'=>'$user->rights->rktticket->read',
					'target'=>'',
					'user'=>2);
		$r++;

		// Left Menu entry: (r=0)
		$this->menu[$r]=array('fk_menu'=>'fk_mainmenu=rktticket',
					'type'=>'left',
					'titre'=>'Tickets',
					'mainmenu'=>'rktticket',
					'leftmenu'=>'rkttickets',
					'url'=>'/custom/rktticket/index.php?mainmenu=rktticket&leftmenu=rkttickets',
					'langs'=>'ticket@ticket',
					'position'=>100,
					'enabled'=>'1',
					'perms'=>'$user->rights->rktticket->read',
					'target'=>'',
					'user'=>2);
		$r++;

		// Left Menu sub menu entry: (r=1)
		$this->menu[$r]=array('fk_menu'=>'fk_mainmenu=rktticket,fk_leftmenu=rkttickets',
					'type'=>'left',
					'titre'=>'NewTicket',
					'mainmenu'=>'rktticket',
					'leftmenu'=>'',
					'url'=>'/custom/rktticket/ticket.php?mainmenu=rktticket&leftmenu=rkttickets&action=create',
					'langs'=>'ticket@ticket',
					'position'=>100,
					'enabled'=>'1',
					'perms'=>'$user->rights->rktticket->create',
					'target'=>'',
					'user'=>2);
		$r++;

		// Left Menu sub menu entry: (r=1)
		$this->menu[$r]=array('fk_menu'=>'fk_mainmenu=rktticket,fk_leftmenu=rkttickets',
					'type'=>'left',
					'titre'=>'TicketList',
					'mainmenu'=>'rktticket',
					'leftmenu'=>'rktticketlist',
					'url'=>'/custom/rktticket/list.php?mainmenu=rktticket&leftmenu=rktticketlist',
					'langs'=>'ticket@ticket',
					'position'=>100,
					'enabled'=>'1',
					'perms'=>'$user->rights->rktticket->read',
					'target'=>'',
					'user'=>2);
		$r++;

		// Left Menu sub menu entry: (r=2)
		$this->menu[$r]=array('fk_menu'=>'fk_mainmenu=rktticket,fk_leftmenu=rktticketlist',
					'type'=>'left',
					'titre'=>'TicketStatusDraftShort',
					'mainmenu'=>'ticket',
					'leftmenu'=>'',
					'url'=>'/custom/rktticket/list.php?mainmenu=rktticket&leftmenu=rktticketlist&status=0',
					'langs'=>'ticket@ticket',
					'position'=>100,
					'enabled'=>'$conf->global->TICKET_ALWAYS_SHOW_LIST_MENU || $leftmenu == \'rktticketlist\'',
					'perms'=>'$user->rights->rktticket->read',
					'target'=>'',
					'user'=>2);
		$r++;

		// Left Menu sub menu entry: (r=2)
		$this->menu[$r]=array('fk_menu'=>'fk_mainmenu=rktticket,fk_leftmenu=rktticketlist',
					'type'=>'left',
					'titre'=>'TicketStatusValidatedShort',
					'mainmenu'=>'rktticket',
					'leftmenu'=>'',
					'url'=>'/custom/rktticket/list.php?mainmenu=rktticket&leftmenu=rktticketlist&status=1',
					'langs'=>'ticket@ticket',
					'position'=>100,
					'enabled'=>'$conf->global->TICKET_ALWAYS_SHOW_LIST_MENU || $leftmenu == \'rktticketlist\'',
					'perms'=>'$user->rights->rktticket->read',
					'target'=>'',
					'user'=>2);
		$r++;

		// Left Menu sub menu entry: (r=2)
		$this->menu[$r]=array('fk_menu'=>'fk_mainmenu=rktticket,fk_leftmenu=rktticketlist',
					'type'=>'left',
					'titre'=>'TicketStatusAssignedShort',
					'mainmenu'=>'rktticket',
					'leftmenu'=>'',
					'url'=>'/custom/rktticket/list.php?mainmenu=rktticket&leftmenu=rktticketlist&status=2',
					'langs'=>'ticket@ticket',
					'position'=>100,
					'enabled'=>'$conf->global->TICKET_ALWAYS_SHOW_LIST_MENU || $leftmenu == \'rktticketlist\'',
					'perms'=>'$user->rights->rktticket->read',
					'target'=>'',
					'user'=>2);
		$r++;

		// Left Menu sub menu entry: (r=2)
		$this->menu[$r]=array('fk_menu'=>'fk_mainmenu=rktticket,fk_leftmenu=rktticketlist',
					'type'=>'left',
					'titre'=>'TicketStatusNotAssignedShort',
					'mainmenu'=>'rktticket',
					'leftmenu'=>'',
					'url'=>'/custom/rktticket/list.php?mainmenu=rktticket&leftmenu=rktticketlist&status=3',
					'langs'=>'ticket@ticket',
					'position'=>100,
					'enabled'=>'$conf->global->TICKET_ALWAYS_SHOW_LIST_MENU || $leftmenu == \'rktticketlist\'',
					'perms'=>'$user->rights->rktticket->read',
					'target'=>'',
					'user'=>2);
		$r++;

		// Left Menu sub menu entry: (r=2)
		$this->menu[$r]=array('fk_menu'=>'fk_mainmenu=rktticket,fk_leftmenu=rktticketlist',
					'type'=>'left',
					'titre'=>'TicketStatusClosedShort',
					'mainmenu'=>'rktticket',
					'leftmenu'=>'',
					'url'=>'/custom/rktticket/list.php?mainmenu=rktticket&leftmenu=rktticketlist&status=4',
					'langs'=>'ticket@ticket',
					'position'=>100,
					'enabled'=>'$conf->global->TICKET_ALWAYS_SHOW_LIST_MENU || $leftmenu == \'rktticketlist\'',
					'perms'=>'$user->rights->rktticket->read',
					'target'=>'',
					'user'=>2);
		$r++;

		// Left Menu sub menu entry: (r=1)
		$this->menu[$r]=array('fk_menu'=>'fk_mainmenu=rktticket,fk_leftmenu=rkttickets',
					'type'=>'left',
					'titre'=>'TicketStatistics',
					'mainmenu'=>'rktticket',
					'leftmenu'=>'',
					'url'=>'/custom/rktticket/stats/index.php?mainmenu=rktticket&leftmenu=rkttickets',
					'langs'=>'ticket@ticket',
					'position'=>100,
					'enabled'=>'1',
					'perms'=>'$user->rights->rktticket->create',
					'target'=>'',
					'user'=>2);
		$r++;

		// Exports
		//--------
		
	}


	/**
	 * Function called when module is enabled.
	 * The init function add constants, boxes, permissions and menus
	 * (defined in constructor) into Dolibarr database.
	 * It also creates data directories
	 *
	 * @param string $options Options when enabling module ('', 'noboxes')
	 * @return int 1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		$sql = array();

		$result = $this->loadTables();

		return $this->_init($sql, $options);
	}

	/**
	 * Create tables, keys and data required by module
	 * Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * and create data commands must be stored in directory /chat/sql/
	 * This function is called by this->init
	 *
	 * @return int <=0 if KO, >0 if OK
	 */
	private function loadTables()
	{
		return $this->_load_tables('/rktticket/sql/');
	}

	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * @param string $options Options when enabling module ('', 'noboxes')
	 * @return int 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}
}
