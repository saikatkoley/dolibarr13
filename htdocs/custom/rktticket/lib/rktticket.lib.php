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


/**
 *
 * 		This function added by Saikat Koley on 9th April 2021
 *
 * 		Show html area for list of projects
 *
 *		@param	Conf		$conf			Object conf
 * 		@param	Translate	$langs			Object langs
 * 		@param	DoliDB		$db				Database handler
 * 		@param	Object		$object			Third party object
 *      @param  string		$backtopage		Url to go once contact is created
 *      @param  int         $nocreatelink   1=Hide create project link
 *      @param	string		$morehtmlright	More html on right of title
 *      @return	int
 */
function show_tickets($conf, $langs, $db, $object, $backtopage = '', $nocreatelink = 0, $morehtmlright = '')
{
    global $user;

    $i = -1;

    if (!empty($conf->rktticket->enabled) && $user->rights->rktticket->read)
    {
        $langs->load("ticket");

        $newcardbutton = '';
        if (!empty($conf->rktticket->enabled) && $user->rights->rktticket->creer && empty($nocreatelink))
        {
            $newcardbutton .= dolGetButtonTitle($langs->trans('AddTicket'), '', 'fa fa-plus-circle', DOL_URL_ROOT.'/custom/rktticket/ticket.php?socid='.$object->id.'&amp;action=create&amp;backtopage='.urlencode($backtopage));
        }

        print "\n";
        print load_fiche_titre($langs->trans("TicketAttachedWithThisThirdParty"), $newcardbutton.$morehtmlright, '');
        print '<div class="div-table-responsive">';
        print "\n".'<table class="noborder" width=100%>';

        $sql = "SELECT t.rowid, t.ref, t.fk_soc, s.nom as soc_name, t.fk_type, t.fk_category, t.fk_severity";
		$sql.= ", t.sujet, t.created_by, t.assigned_to, t.creation_date, t.status, t.message";
		$sql.= " FROM " . MAIN_DB_PREFIX . "rkt_ticket as t";
		$sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON t.fk_soc = s.rowid";
		$sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "user as u ON t.created_by = u.rowid";
		$sql.= " WHERE t.entity IN(".getEntity('user', 1).")"; // t.entity = $conf->entity;
		$sql.= " AND t.fk_soc = ".$object->id;

        $result = $db->query($sql);
        if ($result)
        {
            $num = $db->num_rows($result);

            print '<tr class="liste_titre">';
            print '<td>'.$langs->trans("Ref").'</td>';
            print '<td>'.$langs->trans("CreatedBy").'</td>';
            print '<td class="right">'.$langs->trans("AssignedTo").'</td>';
            print '<td class="center">'.$langs->trans("ThirdParty").'</td>';
            print '<td class="right">'.$langs->trans("CreationDate").'</td>';
            print '<td class="center">'.$langs->trans("Type").'</td>';
            print '<td class="right">'.$langs->trans("TagCategory").'</td>';
            print '<td class="right">'.$langs->trans("Severity").'</td>';
            print '<td class="center">'.$langs->trans("Subject").'</td>';
            print '<td class="center">'.$langs->trans("Message").'</td>';
            print '<td class="right">'.$langs->trans("Status").'</td>';
            print '</tr>';

            if ($num > 0)
            {
                require_once DOL_DOCUMENT_ROOT.'/custom/rktticket/class/rktticket.class.php';

                $tickettmp = new RktTicket($db);
                $userstatic = new User($db);
			    $soc = new Societe($db);
			    $dict = new RktTicketDict($db);
			    $dictlibelles = $dict->getdictlibelles();

                $i = 0;

                while ($i < $num)
                {
                    $obj = $db->fetch_object($result);
                    $tickettmp->fetch($obj->id);

                    // To verify role of users
                    // $userAccess = $tickettmp->restrictedProjectArea($user);
                    $userAccess = 1;

                    if ($user->rights->rktticket->read && $userAccess > 0)
                    {
                        print '<tr class="oddeven">';

                        // Ref
                        $tickettmp->id = $obj->rowid;
				        $tickettmp->ref = $obj->ref;
				        print '<td class="nobordernopadding nowrap">';
				        print $tickettmp->getNomUrl(1);
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

                        // Creation Date
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
                       	
                       	// Type
						print '<td>'.$dictlibelles[$obj->fk_type].'</td>';

						// Category
						print '<td>'.$dictlibelles[$obj->fk_category].'</td>';
				                
				        // Severity
						print '<td>'.$dictlibelles[$obj->fk_severity].'</td>';

						// Subject
						print '<td align="center">'.$obj->sujet.'</td>';

						// Message
						print '<td align="center">'.$obj->message.'</td>';
                
                        // Status
                        print '<td align="right">'.$tickettmp->LibStatut($obj->status, 5).'</td>';

                        print '</tr>';
                    }
                    $i++;
                }
            }
            else
			{
            	print '<tr class="oddeven"><td colspan="8" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
            }
            $db->free($result);
        }
        else
        {
            dol_print_error($db);
        }
        print "</table>";
        print '</div>';

        print "<br>\n";
    }

    return $i;
}



/**
 *  Show a javascript graph.
 *  Do not use this function anymore. Use DolGraph class instead.
 *
 * This function added by Saikat Koley on 9th April 2021 copied from Dolibarr v7
 *
 *
 *  @param		string	$htmlid			Html id name
 *  @param		int		$width			Width in pixel
 *  @param		int		$height			Height in pixel
 *  @param		array	$data			Data array
 *  @param		int		$showlegend		1 to show legend, 0 otherwise
 *  @param		string	$type			Type of graph ('pie', 'barline')
 *  @param		int		$showpercent	Show percent (with type='pie' only)
 *  @param		string	$url			Param to add an url to click values
 *  @param		int		$combineother	0=No combine, 0.05=Combine if lower than 5%
 *  @param      int     $shownographyet Show graph to say there is not enough data
 *  @return		void
 *  @deprecated
 *  @see DolGraph
 */
function custom_dol_print_graph($htmlid,$width,$height,$data,$showlegend=0,$type='pie',$showpercent=0,$url='',$combineother=0.05,$shownographyet=0)
{
	dol_syslog(__FUNCTION__ . " is deprecated", LOG_WARNING);

	global $conf,$langs;
	global $theme_datacolor;    // To have var kept when function is called several times

	if ($shownographyet)
	{
		print '<div class="nographyet" style="width:'.$width.'px;height:'.$height.'px;"></div>';
		print '<div class="nographyettext">'.$langs->trans("NotEnoughDataYet").'</div>';
		return;
	}

	if (empty($conf->use_javascript_ajax)) return;
	$jsgraphlib='flot';
	$datacolor=array();

	// Load colors of theme into $datacolor array
	$color_file = DOL_DOCUMENT_ROOT."/theme/".$conf->theme."/graph-color.php";
	if (is_readable($color_file))
	{
		include_once $color_file;
		if (isset($theme_datacolor))
		{
			$datacolor=array();
			foreach($theme_datacolor as $val)
			{
				$datacolor[]="#".sprintf("%02x",$val[0]).sprintf("%02x",$val[1]).sprintf("%02x",$val[2]);
			}
		}
	}
	print '<div id="'.$htmlid.'" style="width:'.$width.'px;height:'.$height.'px;"></div>';

	// We use Flot js lib
	if ($jsgraphlib == 'flot')
	{
		if ($type == 'pie')
		{
			// data is   array('series'=>array(serie1,serie2,...),
			//                 'seriestype'=>array('bar','line',...),
			//                 'seriescolor'=>array(0=>'#999999',1=>'#999999',...)
			//                 'xlabel'=>array(0=>labelx1,1=>labelx2,...));
			// serieX is array('label'=>'label', data=>val)
			print '
			<script type="text/javascript">
			$(function () {
				var data = '.json_encode($data['series']).';

				function plotWithOptions() {
					$.plot($("#'.$htmlid.'"), data,
					{
						series: {
							pie: {
								show: true,
								radius: 0.8,';
			if ($combineother)
			{
				print '
								combine: {
								 	threshold: '.$combineother.'
								},';
			}
			print '
								label: {
									show: true,
									radius: 0.9,
									formatter: function(label, series) {
										var percent=Math.round(series.percent);
										var number=series.data[0][1];
										return \'';
										print '<div style="font-size:8pt;text-align:center;padding:2px;color:black;">';
										if ($url) print '<a style="color: #FFFFFF;" border="0" href="'.$url.'">';
										print '\'+'.($showlegend?'number':'label+\' \'+number');
										if (! empty($showpercent)) print '+\'<br/>\'+percent+\'%\'';
										print '+\'';
										if ($url) print '</a>';
										print '</div>\';
									},
									background: {
										opacity: 0.0,
										color: \'#000000\'
									},
								}
							}
						},
						zoom: {
							interactive: true
						},
						pan: {
							interactive: true
						},';
						if (count($datacolor))
						{
							print 'colors: '.(! empty($data['seriescolor']) ? json_encode($data['seriescolor']) : json_encode($datacolor)).',';
						}
						print 'legend: {show: '.($showlegend?'true':'false').', position: \'ne\' }
					});
				}
				plotWithOptions();
			});
			</script>';
		}
		else if ($type == 'barline')
		{
			// data is   array('series'=>array(serie1,serie2,...),
			//                 'seriestype'=>array('bar','line',...),
			//                 'seriescolor'=>array(0=>'#999999',1=>'#999999',...)
			//                 'xlabel'=>array(0=>labelx1,1=>labelx2,...));
			// serieX is array('label'=>'label', data=>array(0=>y1,1=>y2,...)) with same nb of value than into xlabel
			print '
			<script type="text/javascript">
			$(function () {
				var data = [';
				$i=0; $outputserie=0;
				foreach($data['series'] as $serie)
				{
					if ($data['seriestype'][$i]=='line') { $i++; continue; };
					if ($outputserie > 0) print ',';
					print '{ bars: { stack: 0, show: true, barWidth: 0.9, align: \'center\' }, label: \''.dol_escape_js($serie['label']).'\', data: '.json_encode($serie['data']).'}'."\n";
					$outputserie++; $i++;
				}
				if ($outputserie) print ', ';
				//print '];
				//var datalines = [';
				$i=0; $outputserie=0;
				foreach($data['series'] as $serie)
				{
					if (empty($data['seriestype'][$i]) || $data['seriestype'][$i]=='bar') { $i++; continue; };
					if ($outputserie > 0) print ',';
					print '{ lines: { show: true }, label: \''.dol_escape_js($serie['label']).'\', data: '.json_encode($serie['data']).'}'."\n";
					$outputserie++; $i++;
				}
				print '];
				var dataticks = '.json_encode($data['xlabel']).'

				function plotWithOptions() {
					$.plot(jQuery("#'.$htmlid.'"), data,
					{
						series: {
							stack: 0
						},
						zoom: {
							interactive: true
						},
						pan: {
							interactive: true
						},';
						if (count($datacolor))
						{
							print 'colors: '.json_encode($datacolor).',';
						}
						print 'legend: {show: '.($showlegend?'true':'false').'},
						xaxis: {ticks: dataticks}
					});
				}
				plotWithOptions();
			});
			</script>';
		}
		else print 'BadValueForParameterType';
	}
}