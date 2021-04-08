<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (c) 2005-2013 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (c) 2011      Juanjo Menent		<jmenent@2byte.es>
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
 *	\file       htdocs/comm/propal/class/propalestats.class.php
 *	\ingroup    propales
 *	\brief      File of class to manage proposals statistics
 */

include_once DOL_DOCUMENT_ROOT . '/core/class/stats.class.php';
include_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
dol_include_once('/custom/rktticket/class/rktticket.class.php');


/**
 *	Class to manage proposals statistics
 */
class RktTicketStats extends Stats
{
    public $table_element;

    var $socid;
    var $userid;

    var $from;
    var $field;
    var $where;


	/**
	 * Constructor
	 *
	 * @param 	DoliDB	$db		   Database handler
	 * @param 	int		$socid	   Id third party for filter. This value must be forced during the new to external user company if user is an external user.
     * @param   int		$userid    Id user for filter (creation user)
	 */
	function __construct($db, $socid=0, $userid=0)
	{
		global $user, $conf;

		$this->db = $db;
                $this->socid = ($socid > 0 ? $socid : 0);
                $this->userid = $userid;

		$object=new RktTicket($this->db);

		$this->from = MAIN_DB_PREFIX.$object->table_element." as p";

		$this->field='';

		//$this->where.= " AND p.fk_soc = s.rowid AND p.entity = ".$conf->entity;
		$this->where.= " p.entity IN (".getEntity('user', 1).")";
                if (!$user->rights->societe->client->voir && !$this->socid) $this->where .= " AND sc.fk_user = " .$user->id;
                if ($this->userid > 0) $this->where.=' AND created_by = '.$this->userid;
	}


	/**
	 * Return propals number by month for a year
	 *
	 * @param	int		$year		Year to scan
	 * @return	array				Array with number by month
	 */
	function getNbByMonth($year)
	{
		global $user;

		$sql = "SELECT date_format(p.creation_date,'%m') as dm, COUNT(*) as nb";
		$sql.= " FROM ".$this->from;
                if (!$user->rights->societe->client->voir && !$this->socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
		$sql.= " WHERE p.creation_date BETWEEN '".$this->db->idate(dol_get_first_day($year))."' AND '".$this->db->idate(dol_get_last_day($year))."'";
		$sql.= " AND ".$this->where;
		$sql.= " GROUP BY dm";
        $sql.= $this->db->order('dm','DESC');

		$res=$this->_getNbByMonth($year, $sql);
		return $res;
	}

	/**
	 * Return propals number per year
	 *
	 * @return	array	Array with number by year
	 *
	 */
	function getNbByYear()
	{
		global $user;

		$sql = "SELECT date_format(p.creation_date,'%Y') as dm, COUNT(*) as nb, SUM(p.".$this->field.")";
		$sql.= " FROM ".$this->from;
                if (!$user->rights->societe->client->voir && !$this->socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
		$sql.= " WHERE ".$this->where;
		$sql.= " GROUP BY dm";
                $sql.= $this->db->order('dm','DESC');

		return $this->_getNbByYear($sql);
	}
        
        /**
	 *	Return nb, total and average
	 *
	 *	@return	array	Array of values
	 */
	function getAllByYear()
	{
		global $user;

		$sql = "SELECT date_format(p.creation_date,'%Y') as year, COUNT(*) as nb";//, SUM(".$this->field.") as total";//, AVG(".$this->field.") as avg";
		$sql.= " FROM ".$this->from;
                if (!$user->rights->societe->client->voir && !$this->socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
		$sql.= " WHERE ".$this->where;
		$sql.= " GROUP BY year";
                $sql.= $this->db->order('year','DESC');
                
		return $this->_getAllByYear($sql);
	}
}
