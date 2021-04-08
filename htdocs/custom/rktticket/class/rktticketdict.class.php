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
 * \file    class/....class.php
 * \ingroup ...
 * \brief   Example CRUD (Create/Read/Update/Delete) class.
 *
 * Put detailed description here.
 */

/** Includes */
//require_once DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php";
//require_once DOL_DOCUMENT_ROOT."/societe/class/societe.class.php";
//require_once DOL_DOCUMENT_ROOT."/product/class/product.class.php";

/**
 * Put your class' description here
 */
class RktTicketDict // extends CommonObject
{

    /** @var DoliDb Database handler */
	private $db;
    /** @var string Error code or message */
	public $error;
    /** @var array Several error codes or messages */
	public $errors = array();
    /** @var string Id to identify managed object */
	//public $element='myelement';
    /** @var string Name of table without prefix where object is stored */
	//public $table_element='mytable';
    /** @var mixed An example property */
	public $lines = array();

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		return 1;
	}

	/**
	 * Create object into database
	 *
	 * @param User $user User that create
	 * @param int $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int <0 if KO, Id of created object if OK
	 */
	public function create($dict_name, $libelle, $desc, $active = 1, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters
		if (isset($dict_name)) {
			$dict_name = trim($dict_name);
		}
		if (isset($libelle)) {
			$libelle = trim($libelle);
		}
		if (isset($desc)) {
			$desc = trim($desc);
		}

		// Check parameters
		// Put here code to add control on parameters values
		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "rkt_ticket_dict(";
		$sql.= " dict_name,";
		$sql.= " libelle,";
		$sql.= " description,";
                $sql.= " active";

		$sql.= ") VALUES (";
		$sql.= " '" . $dict_name . "',";
		$sql.= " '" . $libelle . "',";
		$sql.= " '" . $desc . "',";
                $sql.= " '" . $active . "'";

		$sql.= ")";

		$this->db->begin();

		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}

                $id = 0; // initialisation
		if (! $error) {
			$id = $this->db->last_insert_id(MAIN_DB_PREFIX . "rkt_ticket_dict");

			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				//// Call triggers
				//include_once DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php";
				//$interface=new Interfaces($this->db);
				//$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
				//if ($result < 0) { $error++; $this->errors=$interface->errors; }
				//// End call triggers
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
				$this->error.=($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();

			return -1 * $error;
		} else {
			$this->db->commit();

			return $id;
		}
	}

	/**
	 * Load object in memory from database
	 *
	 * @param int $id Id object
	 * @return int <0 if KO, >0 if OK
	 */
	public function fetch($dict_name='',$sortfield='',$sortorder='')
	{
		global $langs;
                
                $this->lines = array();
                
		$sql = "SELECT";
		$sql.= " t.rowid,";
		$sql.= " t.dict_name,";
		$sql.= " t.libelle,";
                $sql.= " t.description,";
		$sql.= " t.active";
		//...
		$sql.= " FROM " . MAIN_DB_PREFIX . "rkt_ticket_dict as t";
		if (! empty($dict_name)) $sql.= " WHERE t.dict_name = '" . $dict_name."' AND t.active = 1";
                if (! empty($sortfield) && ! empty($sortorder)) $sql.= " ORDER BY t." . $sortfield.' '.$sortorder;

		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
                        $i = 0;
                        $num = $this->db->num_rows($resql);
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);

				$this->lines[$i] = $obj;
				//...
                                
                                $i++;
			}
			$this->db->free($resql);

			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);

			return -1;
		}
	}
        
        /**
	 * Load object in memory from database
	 *
	 * @param int $id Id object
	 * @return int <0 if KO, >0 if OK
	 */
	public function getdictnames()
	{
		global $langs;
                
                $dictnames = array();
                
		$sql = "SELECT DISTINCT dict_name FROM " . MAIN_DB_PREFIX . "rkt_ticket_dict";

		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
                        $i = 0;
                        $num = $this->db->num_rows($resql);
                        
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);

				$dictnames[$i] = $obj->dict_name;
				//...
                                
                                $i++;
			}
			$this->db->free($resql);
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);
		}
                
                return $dictnames;
	}
        
        public function getdictlibelles()
	{
		global $langs;
                
                $dictlibelles = array();
                
		$sql = "SELECT rowid, libelle FROM " . MAIN_DB_PREFIX . "rkt_ticket_dict";

		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
                        $i = 0;
                        $num = $this->db->num_rows($resql);
                        
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);

				$dictlibelles[$obj->rowid] = $obj->libelle;
				//...
                                
                                $i++;
			}
			$this->db->free($resql);
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);
		}
                
                return $dictlibelles;
	}
        
        /**
	 * Update object into database
	 *
	 * @param User $user User that modify
	 * @param int $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	public function setstate($id, $active = 1, $user = 0, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters

		// Check parameters
		// Put here code to add control on parameters values
		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "rkt_ticket_dict SET";
		$sql.= " active = " . $active;

		$sql.= " WHERE rowid=" . $id;

		$this->db->begin();

		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}

		if (! $error) {
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				//// Call triggers
				//include_once DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php";
				//$interface=new Interfaces($this->db);
				//$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
				//if ($result < 0) { $error++; $this->errors=$interface->errors; }
				//// End call triggers
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
				$this->error.=($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();

			return -1 * $error;
		} else {
			$this->db->commit();

			return 1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param User $user User that modify
	 * @param int $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	public function update($id, $dict_name, $libelle, $desc, $user = 0, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters
		if (isset($dict_name)) {
			$dict_name = trim($dict_name);
		}
		if (isset($libelle)) {
			$libelle = trim($libelle);
		}
		if (isset($desc)) {
			$desc = trim($desc);
		}

		// Check parameters
		// Put here code to add control on parameters values
		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "rkt_ticket_dict SET";
		$sql.= " dict_name = '" . $dict_name . "',";
                $sql.= " libelle = '" . $libelle . "',";
                $sql.= " description = '" . $desc . "'";

		$sql.= " WHERE rowid = " . $id;

		$this->db->begin();

		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}

		if (! $error) {
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				//// Call triggers
				//include_once DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php";
				//$interface=new Interfaces($this->db);
				//$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
				//if ($result < 0) { $error++; $this->errors=$interface->errors; }
				//// End call triggers
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
				$this->error.=($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();

			return -1 * $error;
		} else {
			$this->db->commit();

			return 1;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user User that delete
	 * @param int $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	public function delete($id, $user, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		$this->db->begin();

		if (! $error) {
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				//// Call triggers
				//include_once DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php";
				//$interface=new Interfaces($this->db);
				//$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
				//if ($result < 0) { $error++; $this->errors=$interface->errors; }
				//// End call triggers
			}
		}

		if (! $error) {
			$sql = "DELETE FROM " . MAIN_DB_PREFIX . "rkt_ticket_dict";
			$sql.= " WHERE rowid=" . $id;

			dol_syslog(__METHOD__ . " sql=" . $sql);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
				$this->error.=($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();

			return -1 * $error;
		} else {
			$this->db->commit();

			return 1;
		}
	}

}
