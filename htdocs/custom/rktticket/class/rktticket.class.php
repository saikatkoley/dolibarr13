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

/**
 * \file    class/....class.php
 * \ingroup ...
 * \brief   Example CRUD (Create/Read/Update/Delete) class.
 *
 * Put detailed description here.
 */

/** Includes */
require_once DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php";
//require_once DOL_DOCUMENT_ROOT."/societe/class/societe.class.php";
//require_once DOL_DOCUMENT_ROOT."/product/class/product.class.php";
// require_once DOL_DOCUMENT_ROOT.'/core/class/commonincoterm.class.php'; // Use only in Dolibarr v13
/**
 * Put your class' description here
 */
class RktTicket extends CommonObject
{
    // use CommonIncoterm; // Use only in Dolibarr v13
    /** @var DoliDb Database handler */
	//public $db;
    /** @var string Error code or message */
	//public $error;
    /** @var array Several error codes or messages */
	//public $errors = array();
    /** @var string Id to identify managed object */
	public $element='rktticket';
    /** @var string Name of table without prefix where object is stored */
	public $table_element='rkt_ticket';
    /** @var int An example ID */
	public $id;
    /** @var mixed An example property */
	public $entity;
    /** @var mixed An example property */
	public $ref;
	/** @var mixed An example property */
	public $fk_soc;
        /** @var mixed An example property */
	public $fk_type;
        /** @var mixed An example property */
	public $fk_category;
        /** @var mixed An example property */
	public $fk_severity;
        /** @var mixed An example property */
	public $sujet;
        /** @var mixed An example property */
	public $message;
        /** @var mixed An example property */
	public $created_by;
        /** @var mixed An example property */
	public $assigned_to;
        /** @var mixed An example property */
	public $creation_date;
        /** @var mixed An example property */
	public $status;
    /** @var mixed An example property */
    public $brouillon;
    /** @var mixed An example property */
    public $labelstatut;
    /** @var mixed An example property */
    public $labelstatut_short;


    public $fields = array(
        'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'position'=>1, 'visible'=>-2, 'enabled'=>1, 'position'=>1, 'notnull'=>1, 'index'=>1, 'comment'=>"Id"),
        'entity' => array('type'=>'integer', 'label'=>'Entity', 'visible'=>0, 'enabled'=>1, 'position'=>5, 'notnull'=>1, 'index'=>1),
        'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'visible'=>1, 'enabled'=>1, 'position'=>10, 'notnull'=>1, 'index'=>1, 'searchall'=>1, 'comment'=>"Reference of object", 'css'=>''),
        // 'track_id' => array('type'=>'varchar(255)', 'label'=>'TicketTrackId', 'visible'=>-2, 'enabled'=>1, 'position'=>11, 'notnull'=>-1, 'searchall'=>1, 'help'=>"Help text"),
        'created_by' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'Author', 'visible'=>1, 'enabled'=>1, 'position'=>15, 'notnull'=>1, 'css'=>'tdoverflowmax150 maxwidth150onsmartphone'),
        // 'origin_email' => array('type'=>'mail', 'label'=>'OriginEmail', 'visible'=>-2, 'enabled'=>1, 'position'=>16, 'notnull'=>1, 'index'=>1, 'searchall'=>1, 'comment'=>"Reference of object"),
        'sujet' => array('type'=>'varchar(255)', 'label'=>'Subject', 'visible'=>1, 'enabled'=>1, 'position'=>18, 'notnull'=>-1, 'searchall'=>1, 'help'=>"", 'css'=>'maxwidth75'),
        'fk_type' => array('type'=>'varchar(32)', 'label'=>'Type', 'visible'=>1, 'enabled'=>1, 'position'=>20, 'notnull'=>-1, 'searchall'=>1, 'help'=>"", 'css'=>'maxwidth100'),
        'fk_category' => array('type'=>'varchar(32)', 'label'=>'RktTicketCategory', 'visible'=>-1, 'enabled'=>1, 'position'=>21, 'notnull'=>-1, 'help'=>"", 'css'=>'maxwidth100'),
        'fk_severity' => array('type'=>'varchar(32)', 'label'=>'Severity', 'visible'=>1, 'enabled'=>1, 'position'=>22, 'notnull'=>-1, 'help'=>"", 'css'=>'maxwidth100'),
        'fk_soc' => array('type'=>'integer:Societe:societe/class/societe.class.php', 'label'=>'ThirdParty', 'visible'=>1, 'enabled'=>1, 'position'=>50, 'notnull'=>-1, 'index'=>1, 'searchall'=>1, 'help'=>"LinkToThirparty", 'css'=>'tdoverflowmax150 maxwidth150onsmartphone'),
        // 'notify_tiers_at_create' => array('type'=>'integer', 'label'=>'NotifyThirdparty', 'visible'=>-1, 'enabled'=>0, 'position'=>51, 'notnull'=>1, 'index'=>1),
        // 'fk_project' => array('type'=>'integer:Project:projet/class/project.class.php', 'label'=>'Project', 'visible'=>-1, 'enabled'=>1, 'position'=>52, 'notnull'=>-1, 'index'=>1, 'help'=>"LinkToProject"),
        // 'timing' => array('type'=>'varchar(20)', 'label'=>'Timing', 'visible'=>-1, 'enabled'=>1, 'position'=>42, 'notnull'=>-1, 'help'=>""),
        'creation_date' => array('type'=>'datetime', 'label'=>'DateCreation', 'visible'=>1, 'enabled'=>1, 'position'=>500, 'notnull'=>1),
        // 'date_read' => array('type'=>'datetime', 'label'=>'TicketReadOn', 'visible'=>1, 'enabled'=>1, 'position'=>500, 'notnull'=>1),
        'assigned_to' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'AssignedTo', 'visible'=>1, 'enabled'=>1, 'position'=>505, 'notnull'=>1),
        // 'date_close' => array('type'=>'datetime', 'label'=>'TicketCloseOn', 'visible'=>-1, 'enabled'=>1, 'position'=>510, 'notnull'=>1),
        'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'visible'=>-1, 'enabled'=>1, 'position'=>520, 'notnull'=>1),
        'message' => array('type'=>'text', 'label'=>'Message', 'visible'=>-2, 'enabled'=>1, 'position'=>540, 'notnull'=>-1,),
        // 'progress' => array('type'=>'varchar(100)', 'label'=>'Progression', 'visible'=>-1, 'enabled'=>1, 'position'=>540, 'notnull'=>-1, 'css'=>'right', 'help'=>""),
        // 'resolution' => array('type'=>'integer', 'label'=>'Resolution', 'visible'=>-1, 'enabled'=>1, 'position'=>550, 'notnull'=>1),
        'status' => array('type'=>'integer', 'label'=>'Status', 'visible'=>1, 'enabled'=>1, 'position'=>600, 'notnull'=>1, 'index'=>1, 'arrayofkeyval'=>array(0 => 'Unread', 1 => 'Read', 3 => 'Answered', 4 => 'Assigned', 5 => 'InProgress', 6 => 'Waiting', 8 => 'Closed', 9 => 'Deleted'))
    );

    /**
	 * Draft status
	 */
	const STATUS_DRAFT = 0;
	/**
	 * Validated status
	 */
	const STATUS_VALIDATED = 1;
	/**
	 * Signed quote
	 */
	const STATUS_ASSIGNED = 2;
	/**
	 * Not signed quote
	 */
	const STATUS_NOTASSIGNED = 3;
	/**
	 * Billed quote
	 */
	const STATUS_CLOSED = 4;
        

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct($db)
	{
                global $langs;
                
                $this->db = $db;
                
                $langs->load("rkticket@rkticket");
                
                $this->labelstatut[0]=$langs->trans("TicketStatusDraft");
                $this->labelstatut[1]=$langs->trans("TicketStatusValidated");
                $this->labelstatut[2]=$langs->trans("TicketStatusAssigned");
                $this->labelstatut[3]=$langs->trans("TicketStatusNotAssigned");
                $this->labelstatut[4]=$langs->trans("TicketStatusClosed");
                
                $this->labelstatut_short[0]=$langs->trans("TicketStatusDraftShort");
                $this->labelstatut_short[1]=$langs->trans("TicketStatusValidatedShort");
                $this->labelstatut_short[2]=$langs->trans("TicketStatusAssignedShort");
                $this->labelstatut_short[3]=$langs->trans("TicketStatusNotAssignedShort");
                $this->labelstatut_short[4]=$langs->trans("TicketStatusClosedShort");

		return 1;
	}
        
        /**
        *  Returns the reference to the following non used Proposal used depending on the active numbering module
        *  defined into PROPALE_ADDON
        *
        *  @param	Societe		$soc  	Object thirdparty
        *  @return string      		Reference libre pour la propale
        */
        function getNextNumRef($soc)
        {
            global $conf, $db, $langs;
            $langs->load("rktticket@rktticket");

            if (! empty($conf->global->TICKET_ADDON))
            {
                $mybool=false;

                $file = $conf->global->TICKET_ADDON.".php";
                $classname = $conf->global->TICKET_ADDON;

                // Include file with class
                $dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
                foreach ($dirmodels as $reldir) {

                    $dir = dol_buildpath($reldir."/custom/rktticket/core/modules/rktticket/");

                    // Load file with numbering class (if found)
                    $mybool|=@include_once $dir.$file;
                }

                if (! $mybool)
                {
                    dol_print_error('',"Failed to include file ".$file);
                    return '';
                }

                $obj = new $classname();
                $numref = "";
                $numref = $obj->getNextValue($soc,$this);

                if ($numref != "")
                {
                    return $numref;
                }
                else
                            {
                    $this->error=$obj->error;
                    //dol_print_error($db,"Propale::getNextNumRef ".$obj->error);
                    return "";
                }
            }
            else
            {
                $langs->load("errors");
                print $langs->trans("Error")." ".$langs->trans("ErrorModuleSetupNotComplete");
                return "";
            }
        }
        
        /**
        *  Set status to validated
        *
        *  @param	User	$user       Object user that validate
        *  @param	int		$notrigger	1=Does not execute triggers, 0= execuete triggers
        *  @return int         		<0 if KO, >=0 if OK
        */
        function valid($user, $notrigger=0)
        {
            global $conf,$langs;

            $error=0;

            if ($user->rights->rktticket->create)
            {
                $this->db->begin();

                // Numbering module definition
                $soc = new Societe($this->db);
                $soc->fetch($this->fk_soc);

                // Define new ref
                if (! $error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) // empty should not happened, but when it occurs, the test save life
                {
                    $num = $this->getNextNumRef($soc);
                }
                else
                {
                    $num = $this->ref;
                }

                $sql = "UPDATE ".MAIN_DB_PREFIX."rkt_ticket";
                $sql.= " SET ref = '".$num."',";
                $sql.= " status = ".self::STATUS_VALIDATED;
                $sql.= " WHERE rowid = ".$this->id." AND status = ".self::STATUS_DRAFT;

                dol_syslog(get_class($this)."::valid", LOG_DEBUG);
                $resql=$this->db->query($sql);
                if (! $resql)
                {
                        dol_print_error($this->db);
                        $error++;
                }

                // Trigger calls
                if (! $error && ! $notrigger)
                {
                    // Call trigger
                    //$result=$this->call_trigger('TICKET_VALIDATE',$user);
                    //if ($result < 0) { $error++; }
                    // End call triggers
                }

                if (! $error)
                {
                    $this->ref=$num;
                    $this->brouillon=0;
                    $this->status = self::STATUS_VALIDATED;

                    $this->db->commit();
                    return 1;
                }
                else
                            {
                    $this->db->rollback();
                    return -1;
                }
            }
        }
        
        /**
        *  Assign user to ticket
        *
        *  @param	User	$user       Object user that validate
        *  @param	int		$notrigger	1=Does not execute triggers, 0= execuete triggers
        *  @return int         		<0 if KO, >=0 if OK
        */
        function assign_to($user, $user_id, $notrigger=0)
        {
            global $conf,$langs;

            $error=0;

            if ($user->rights->rktticket->assign)
            {
                $this->db->begin();

                $sql = "UPDATE ".MAIN_DB_PREFIX."rkt_ticket";
                $sql.= " SET assigned_to = ".($user_id > 0 ? $user_id : "null");
                $sql.= " WHERE rowid = ".$this->id;

                dol_syslog(get_class($this)."::assign_to", LOG_DEBUG);
                $resql=$this->db->query($sql);
                if (! $resql)
                {
                        dol_print_error($this->db);
                        $error++;
                }

                // Trigger calls
                if (! $error && ! $notrigger)
                {
                    // Call trigger
                    //$result=$this->call_trigger('TICKET_ASSIGN',$user);
                    //if ($result < 0) { $error++; }
                    // End call triggers
                }

                if (! $error)
                {
                    $this->assigned_to = $user_id;
                    
                    $this->db->commit();
                    return 1;
                }
                else
                            {
                    $this->db->rollback();
                    return -1;
                }
            }
        }

	/**
	 * Create object into database
	 *
	 * @param User $user User that create
	 * @param int $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int <0 if KO, Id of created object if OK
	 */
	public function create($user, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters
		if (isset($this->ref)) {
			$this->ref = trim($this->ref);
		}
		if (isset($this->sujet)) {
			$this->sujet = trim($this->sujet);
		}
		if (isset($this->message)) {
			$this->message = trim($this->message);
		}

		// Check parameters
		// Put here code to add control on parameters values
		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "rkt_ticket(";
		$sql.= " entity,";
		$sql.= " ref,";
        $sql.= " fk_soc,";
        $sql.= " fk_type,";
        $sql.= " fk_category,";
        $sql.= " fk_severity,";
        $sql.= " sujet,";
        $sql.= " message,";
        $sql.= " created_by,";
        $sql.= " creation_date,";
		$sql.= " status";

		$sql.= ") VALUES (";
		$sql.= " '" . $conf->entity . "',";
		$sql.= " '(PROV)',";
        $sql.= " " . ($this->fk_soc > 0 ? "'".$this->fk_soc."'" : "null") . ",";
        $sql.= " '" . $this->fk_type . "',";
        $sql.= " '" . $this->fk_category . "',";
        $sql.= " '" . $this->fk_severity . "',";
        $sql.= " '" . $this->db->escape($this->sujet) . "',";
        $sql.= " '" . $this->db->escape($this->message) . "',";
        $sql.= " '" . $user->id . "',";
        $sql.= " '" . $this->db->idate($this->creation_date) . "',";
		$sql.= " '" . $this->status . "'";

		$sql.= ")";

		$this->db->begin();

		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}

		if (! $error) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . "ticket");
                        
            // update ref
            if ($this->id)
            {
                $this->ref='(PROV'.$this->id.')';
                $sql = 'UPDATE '.MAIN_DB_PREFIX."rkt_ticket SET ref='".$this->ref."' WHERE rowid=".$this->id;

                dol_syslog(get_class($this)."::create", LOG_DEBUG);
                $resql=$this->db->query($sql);
                if (! $resql) $error++;
            }

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

			return $this->id;
		}
	}

	/**
	 * Load object in memory from database
	 *
	 * @param int $id Id object
	 * @return int <0 if KO, >0 if OK
	 */
	public function fetch($id, $ref='')
	{
		global $langs;
                
		$sql = "SELECT t.rowid, t.entity, t.ref, t.fk_soc, t.fk_type, t.fk_category, t.fk_severity";
                $sql.= ", t.sujet, t.message, t.created_by, t.assigned_to, t.creation_date, t.status";
		$sql.= " FROM " . MAIN_DB_PREFIX . "rkt_ticket as t";
                $sql.= " WHERE t.entity IN (".getEntity('user', 1).")";
		if ($id) $sql.= " AND t.rowid = " . $id;
                if ($ref) $sql.= " AND t.ref = '" . $ref . "'";

		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				$this->id = $obj->rowid;
				$this->entity = $obj->entity;
				$this->ref = $obj->ref;
                                $this->fk_soc = $obj->fk_soc;
                                $this->fk_type = $obj->fk_type;
                                $this->fk_category = $obj->fk_category;
                                $this->fk_severity = $obj->fk_severity;
                                $this->sujet = $obj->sujet;
                                $this->message = $obj->message;
                                $this->created_by = $obj->created_by;
                                $this->assigned_to = $obj->assigned_to;
                                $this->creation_date = $obj->creation_date;
                                $this->status = $obj->status;
                                
                                if ($this->status == self::STATUS_DRAFT) $this->brouillon = 1;
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
        *    	Return label of status of proposal (draft, validated, ...)
        *
        *    	@param      int			$mode        0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto
        *    	@return     string		Label
        */
        function getLibStatut($mode=0)
        {
            return $this->LibStatut($this->status,$mode);
        }

        /**
         *    	Return label of a status (draft, validated, ...)
         *
         *    	@param      int			$statut		id statut
         *    	@param      int			$mode      	0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto
         *    	@return     string		Label
         */
        function LibStatut($statut,$mode=1)
        {
            global $langs;
            $langs->load("ticket@ticket");

            if ($statut==self::STATUS_DRAFT) $statuttrans='statut0';
            if ($statut==self::STATUS_VALIDATED) $statuttrans='statut1';
            if ($statut==self::STATUS_ASSIGNED) $statuttrans='statut3';
            if ($statut==self::STATUS_NOTASSIGNED) $statuttrans='statut5';
            if ($statut==self::STATUS_CLOSED) $statuttrans='statut6';

            if ($mode == 0)	return $this->labelstatut[$statut];
            if ($mode == 1)	return $this->labelstatut_short[$statut];
            if ($mode == 2)	return img_picto($this->labelstatut_short[$statut], $statuttrans).' '.$this->labelstatut_short[$statut];
            if ($mode == 3)	return img_picto($this->labelstatut[$statut], $statuttrans);
            if ($mode == 4)	return img_picto($this->labelstatut[$statut],$statuttrans).' '.$this->labelstatut[$statut];
            if ($mode == 5)	return '<span class="hideonsmartphone">'.$this->labelstatut_short[$statut].' </span>'.img_picto($this->labelstatut_short[$statut],$statuttrans);
        }
        
        /**
        *	Return clicable name (with picto eventually)
        *
        *	@param		int		$withpicto		0=No picto, 1=Include picto into link, 2=Only picto
        *	@param		string	$option			On what the link points
        *	@return		string					Chain with URL
        */
        function getNomUrl($withpicto=0,$option='')
        {
            global $langs, $conf;

            $result='';
            $label = '<u>' . $langs->trans("ShowTicket") . '</u>';
            if (! empty($this->ref)) {
                $label .= '<br><b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;
            }

            $link = '<a href="'.dol_buildpath('/custom/rktticket/ticket.php', 1).'?id='.$this->id.'" title="'.dol_escape_htmltag($label, 1).'" class="classfortooltip">';
            $linkend='</a>';

            $picto='ticket@ticket';

            if ($withpicto) $result.=($link.img_object($label, $picto, 'class="classfortooltip"').$linkend);
            if ($withpicto && $withpicto != 2) $result.=' ';
            $result.=$link.$this->ref.$linkend;
            
            return $result;
        }

	/**
	 * Update object into database
	 *
	 * @param User $user User that modify
	 * @param int $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	public function update($user = 0, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters
		if (isset($this->ref)) {
			$this->ref = trim($this->ref);
		}
		if (isset($this->sujet)) {
			$this->sujet = trim($this->sujet);
		}
		if (isset($this->message)) {
			$this->message = trim($this->message);
		}

		// Check parameters
		// Put here code to add control on parameters values
		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "rkt_ticket SET";
		$sql.= " entity = " . (isset($this->entity) ? "'" . $this->entity . "'" : "null") . ",";
		$sql.= " fk_soc = " . (isset($this->fk_soc) && $this->fk_soc > 0 ? "'" . $this->fk_soc . "'" : "null") . ",";
                $sql.= " fk_type = " . (isset($this->fk_type) ? "'" . $this->fk_type . "'" : "null") . ",";
                $sql.= " fk_category = " . (isset($this->fk_category) ? "'" . $this->fk_category . "'" : "null") . ",";
                $sql.= " fk_severity = " . (isset($this->fk_severity) ? "'" . $this->fk_severity . "'" : "null") . ",";
                $sql.= " sujet = " . (isset($this->sujet) ? "'" . $this->db->escape($this->sujet) . "'" : "null") . ",";
                $sql.= " message = " . (isset($this->message) ? "'" . $this->db->escape($this->message) . "'" : "null") . ",";
                $sql.= " created_by = " . (isset($this->created_by) ? "'" . $this->created_by . "'" : "null") . ",";
                $sql.= " creation_date = " . (isset($this->creation_date) ? "'" . $this->db->idate($this->creation_date) . "'" : "null") . ",";
		$sql.= " status = " . (isset($this->status) ? "'" . $this->status . "'" : "null") . "";

		$sql.= " WHERE rowid=" . $this->id;

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
                        
                        // Define output language
                        if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
                        {
                                $outputlangs = $langs;
                                $newlang = '';
                                if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id')) $newlang = GETPOST('lang_id','alpha');
                                if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $this->thirdparty->default_lang;
                                if (! empty($newlang)) {
                                        $outputlangs = new Translate("", $conf);
                                        $outputlangs->setDefaultLang($newlang);
                                }
                                $model=$this->modelpdf;
                                //$ret = $this->fetch($this->id); // Reload to get new records

                                $this->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
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
	public function delete($user, $notrigger = 0)
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
			$sql = "DELETE FROM " . MAIN_DB_PREFIX . "rkt_ticket";
			$sql.= " WHERE rowid=" . $this->id;

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
        
        /**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force template to use ('' to not force)
	 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails=0, $hidedesc=0, $hideref=0)
	{
		global $conf,$langs;

		$langs->load("ticket");

		// Positionne le modele sur le nom du modele a utiliser
		if (! dol_strlen($modele))
		{
			if (! empty($conf->global->TICKET_ADDON_PDF))
			{
				$modele = $conf->global->TICKET_ADDON_PDF;
			}
			else
			{
				$modele = 'einstein';
			}
		}

		$modelpath = "/custom/rktticket/core/modules/rktticket/doc/";

		return $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref);
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
                global $langs;
                
                $this->id=0;
                $this->ref = 'SPECIMEN';
                $this->specimen=1;
                $this->fk_soc = 1;
                $this->creation_date = time();
                $this->fk_type = 1;
                $this->fk_category = 5;
                $this->fk_severity = 8;
                $this->sujet = $langs->trans('Subject').'...';
                $this->message = $langs->trans('Message').'...';
	}
}
