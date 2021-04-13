<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
/* Copyright (c) 2003-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (c) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/core/class/dolgraph.class.php
 *  \ingroup    core
 *	\brief      File for class to generate graph
 */
include_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

class CustomFormFile extends FormFile
{
	private $db;

	public $error;
	public $numoffiles;
	public $infofiles;			// Used to return informations by function getDocumentsLink


	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
		$this->db = $db;
		$this->numoffiles=0;
		return 1;
	}


	function custom_show_documents($modulepart,$modulesubdir,$filedir,$urlsource,$genallowed,$delallowed=0,$modelselected='',$allowgenifempty=1,$forcenomultilang=0,$iconPDF=0,$notused=0,$noform=0,$param='',$title='',$buttonlabel='',$codelang='')
	{
		$this->numoffiles=0;
		print $this->customshowdocuments($modulepart,$modulesubdir,$filedir,$urlsource,$genallowed,$delallowed,$modelselected,$allowgenifempty,$forcenomultilang,$iconPDF,$notused,$noform,$param,$title,$buttonlabel,$codelang);
		return $this->numoffiles;
	}


	/**
	 *      Return a string to show the box with list of available documents for object.
	 *      This also set the property $this->numoffiles
	 *
	 *      @param      string				$modulepart         Module the files are related to ('propal', 'facture', 'facture_fourn', 'mymodule', 'mymodule_temp', ...)
	 *      @param      string				$modulesubdir       Existing (so sanitized) sub-directory to scan (Example: '0/1/10', 'FA/DD/MM/YY/9999'). Use '' if file is not into subdir of module.
	 *      @param      string				$filedir            Directory to scan
	 *      @param      string				$urlsource          Url of origin page (for return)
	 *      @param      int					$genallowed         Generation is allowed (1/0 or array list of templates)
	 *      @param      int					$delallowed         Remove is allowed (1/0)
	 *      @param      string				$modelselected      Model to preselect by default
	 *      @param      integer				$allowgenifempty	Allow generation even if list of template ($genallowed) is empty (show however a warning)
	 *      @param      integer				$forcenomultilang	Do not show language option (even if MAIN_MULTILANGS defined)
	 *      @param      int					$iconPDF            Deprecated, see getDocumentsLink
	 * 		@param		int					$notused	        Not used
	 * 		@param		integer				$noform				Do not output html form tags
	 * 		@param		string				$param				More param on http links
	 * 		@param		string				$title				Title to show on top of form
	 * 		@param		string				$buttonlabel		Label on submit button
	 * 		@param		string				$codelang			Default language code to use on lang combo box if multilang is enabled
	 * 		@param		string				$morepicto			Add more HTML content into cell with picto
	 *      @param      Object              $object             Object when method is called from an object card.
	 * 		@return		string              					Output string with HTML array of documents (might be empty string)
	 */
	function customshowdocuments($modulepart,$modulesubdir,$filedir,$urlsource,$genallowed,$delallowed=0,$modelselected='',$allowgenifempty=1,$forcenomultilang=0,$iconPDF=0,$notused=0,$noform=0,$param='',$title='',$buttonlabel='',$codelang='',$morepicto='',$object=null)
	{
		// Deprecation warning
		if (0 !== $iconPDF) {
			dol_syslog(__METHOD__ . ": passing iconPDF parameter is deprecated", LOG_WARNING);
		}

		global $langs, $conf, $user, $hookmanager;
		global $form, $bc;

		if (! is_object($form)) $form=new Form($this->db);

		include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		// For backward compatibility
		if (! empty($iconPDF)) {
			return $this->getDocumentsLink($modulepart, $modulesubdir, $filedir);
		}

		$printer=0;
		if (in_array($modulepart,array('facture','supplier_proposal','propal','proposal','order','commande','expedition', 'commande_fournisseur', 'expensereport')))	// The direct print feature is implemented only for such elements
		{
			$printer = (!empty($user->rights->printing->read) && !empty($conf->printing->enabled))?true:false;
		}

		$hookmanager->initHooks(array('formfile'));
		$forname='builddoc';
		$out='';

		$headershown=0;
		$showempty=0;
		$i=0;

		$out.= "\n".'<!-- Start show_document -->'."\n";
		//print 'filedir='.$filedir;

		if (preg_match('/massfilesarea_/', $modulepart))
		{
			$out.='<div id="show_files"><br></div>'."\n";
			$title=$langs->trans("MassFilesArea").' <a href="" id="togglemassfilesarea" ref="shown">('.$langs->trans("Hide").')</a>';
			$title.='<script type="text/javascript" language="javascript">
				jQuery(document).ready(function() {
					jQuery(\'#togglemassfilesarea\').click(function() {
						if (jQuery(\'#togglemassfilesarea\').attr(\'ref\') == "shown")
						{
							jQuery(\'#'.$modulepart.'_table\').hide();
							jQuery(\'#togglemassfilesarea\').attr("ref", "hidden");
							jQuery(\'#togglemassfilesarea\').text("('.dol_escape_js($langs->trans("Show")).')");
						}
						else
						{
							jQuery(\'#'.$modulepart.'_table\').show();
							jQuery(\'#togglemassfilesarea\').attr("ref","shown");
							jQuery(\'#togglemassfilesarea\').text("('.dol_escape_js($langs->trans("Hide")).')");
						}
						return false;
					});
				});
				</script>';
		}

		$titletoshow=$langs->trans("Documents");
		if (! empty($title)) $titletoshow=$title;

		// Show table
		if ($genallowed)
		{
			$modellist=array();

			if ($modulepart == 'company')
			{
				$showempty=1;
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/societe/modules_societe.class.php';
					$modellist=ModeleThirdPartyDoc::liste_modeles($this->db);
				}
			}
			else if ($modulepart == 'propal')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/propale/modules_propale.php';
					$modellist=ModelePDFPropales::liste_modeles($this->db);
				}
			}
			else if ($modulepart == 'supplier_proposal')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/supplier_proposal/modules_supplier_proposal.php';
					$modellist=ModelePDFSupplierProposal::liste_modeles($this->db);
				}
			}
			else if ($modulepart == 'commande')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/commande/modules_commande.php';
					$modellist=ModelePDFCommandes::liste_modeles($this->db);
				}
			}
			elseif ($modulepart == 'expedition')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/expedition/modules_expedition.php';
					$modellist=ModelePDFExpedition::liste_modeles($this->db);
				}
			}
			elseif ($modulepart == 'livraison')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/livraison/modules_livraison.php';
					$modellist=ModelePDFDeliveryOrder::liste_modeles($this->db);
				}
			}
			else if ($modulepart == 'ficheinter')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/fichinter/modules_fichinter.php';
					$modellist=ModelePDFFicheinter::liste_modeles($this->db);
				}
			}
			elseif ($modulepart == 'facture')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/facture/modules_facture.php';
					$modellist=ModelePDFFactures::liste_modeles($this->db);
				}
			}
			elseif ($modulepart == 'contract')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/contract/modules_contract.php';
					$modellist=ModelePDFContract::liste_modeles($this->db);
				}
			}
			elseif ($modulepart == 'project')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/project/modules_project.php';
					$modellist=ModelePDFProjects::liste_modeles($this->db);
				}
			}
			elseif ($modulepart == 'project_task')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/project/task/modules_task.php';
					$modellist=ModelePDFTask::liste_modeles($this->db);
				}
			}
			elseif ($modulepart == 'product')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/product/modules_product.class.php';
					$modellist=ModelePDFProduct::liste_modeles($this->db);
				}
			}
			elseif ($modulepart == 'export')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/export/modules_export.php';
					$modellist=ModeleExports::liste_modeles($this->db);
				}
			}
			else if ($modulepart == 'commande_fournisseur' || $modulepart == 'supplier_order')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/supplier_order/modules_commandefournisseur.php';
					$modellist=ModelePDFSuppliersOrders::liste_modeles($this->db);
				}
			}
			else if ($modulepart == 'facture_fournisseur' || $modulepart == 'supplier_invoice')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/supplier_invoice/modules_facturefournisseur.php';
					$modellist=ModelePDFSuppliersInvoices::liste_modeles($this->db);
				}
			}
			else if ($modulepart == 'supplier_payment')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/supplier_payment/modules_supplier_payment.php';
					$modellist=ModelePDFSuppliersPayments::liste_modeles($this->db);
				}
			}
			else if ($modulepart == 'remisecheque')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/cheque/modules_chequereceipts.php';
					$modellist=ModeleChequeReceipts::liste_modeles($this->db);
				}
			}
			elseif ($modulepart == 'donation')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/dons/modules_don.php';
					$modellist=ModeleDon::liste_modeles($this->db);
				}
			}
			elseif ($modulepart == 'member')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/member/modules_cards.php';
					$modellist=ModelePDFCards::liste_modeles($this->db);
				}
			}
			elseif ($modulepart == 'agenda')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/action/modules_action.php';
					$modellist=ModeleAction::liste_modeles($this->db);
				}
			}
			else if ($modulepart == 'expensereport')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/expensereport/modules_expensereport.php';
					$modellist=ModeleExpenseReport::liste_modeles($this->db);
				}
			}
			else if ($modulepart == 'unpaid')
			{
				$modellist='';
			}
			elseif ($modulepart == 'user')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/user/modules_user.class.php';
					$modellist=ModelePDFUser::liste_modeles($this->db);
				}
			}
			elseif ($modulepart == 'usergroup')
			{
				if (is_array($genallowed)) $modellist=$genallowed;
				else
				{
					include_once DOL_DOCUMENT_ROOT.'/core/modules/usergroup/modules_usergroup.class.php';
					$modellist=ModelePDFUserGroup::liste_modeles($this->db);
				}
			}
			else //if ($modulepart != 'agenda')
			{
				// For normalized standard modules
				$file=dol_buildpath('/core/modules/'.$modulepart.'/modules_'.$modulepart.'.php',0);
				if (file_exists($file))
				{
					$res=include_once $file;
				}
				// For normalized external modules
				else
				{
					$file=dol_buildpath('/'.$modulepart.'/core/modules/'.$modulepart.'/modules_'.$modulepart.'.php',0);
					$res=include_once $file;
				}
				$class='ModelePDF'.ucfirst($modulepart);
				if (class_exists($class))
				{
					$modellist=call_user_func($class.'::liste_modeles',$this->db);
				}
				else
			  {
					dol_print_error($this->db,'Bad value for modulepart');
					return -1;
				}
			}

			// Set headershown to avoid to have table opened a second time later
			$headershown=1;

			$buttonlabeltoshow=$buttonlabel;
			if (empty($buttonlabel)) $buttonlabel=$langs->trans('Generate');

			if ($conf->browser->layout == 'phone') $urlsource.='#'.$forname.'_form';   // So we switch to form after a generation
			if (empty($noform)) $out.= '<form action="'.$urlsource.(empty($conf->global->MAIN_JUMP_TAG)?'':'#builddoc').'" id="'.$forname.'_form" method="post">';
			$out.= '<input type="hidden" name="action" value="builddoc">';
			$out.= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';

			$out.= load_fiche_titre($titletoshow, '', '');
			$out.= '<div class="div-table-responsive-no-min">';
			$out.= '<table class="liste formdoc noborder" summary="listofdocumentstable" width="100%">';

			$out.= '<tr class="liste_titre">';

			$addcolumforpicto=($delallowed || $printer || $morepicto);
			$out.= '<th align="center" colspan="'.(3+($addcolumforpicto?'2':'1')).'" class="formdoc liste_titre maxwidthonsmartphone">';

			// Model
			if (! empty($modellist))
			{
				$out.= '<span class="hideonsmartphone">'.$langs->trans('Model').' </span>';
				if (is_array($modellist) && count($modellist) == 1)    // If there is only one element
				{
					$arraykeys=array_keys($modellist);
					$modelselected=$arraykeys[0];
				}
				$out.= $form->selectarray('model', $modellist, $modelselected, $showempty, 0, 0, '', 0, 0, 0, '', 'minwidth100');
				if ($conf->use_javascript_ajax)
				{
					$out.= ajax_combobox('model');
				}
			}
			else
			{
				$out.= '<div class="float">'.$langs->trans("Files").'</div>';
			}

			// Language code (if multilang)
			if (($allowgenifempty || (is_array($modellist) && count($modellist) > 0)) && $conf->global->MAIN_MULTILANGS && ! $forcenomultilang && (! empty($modellist) || $showempty))
			{
				include_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
				$formadmin=new FormAdmin($this->db);
				$defaultlang=$codelang?$codelang:$langs->getDefaultLang();
				$morecss='maxwidth150';
				if (! empty($conf->browser->phone)) $morecss='maxwidth100';
				$out.= $formadmin->select_language($defaultlang, 'lang_id', 0, 0, 0, 0, 0, $morecss);
			}
			else
			{
				$out.= '&nbsp;';
			}

			// Button
			$genbutton = '<input class="button buttongen" id="'.$forname.'_generatebutton" name="'.$forname.'_generatebutton"';
			$genbutton.= ' type="submit" value="'.$buttonlabel.'"';
			if (! $allowgenifempty && ! is_array($modellist) && empty($modellist)) $genbutton.= ' disabled';
			$genbutton.= '>';
			if ($allowgenifempty && ! is_array($modellist) && empty($modellist) && empty($conf->dol_no_mouse_hover) && $modulepart != 'unpaid')
			{
			   	$langs->load("errors");
			   	$genbutton.= ' '.img_warning($langs->transnoentitiesnoconv("WarningNoDocumentModelActivated"));
			}
			if (! $allowgenifempty && ! is_array($modellist) && empty($modellist) && empty($conf->dol_no_mouse_hover) && $modulepart != 'unpaid') $genbutton='';
			if (empty($modellist) && ! $showempty && $modulepart != 'unpaid') $genbutton='';
			$out.= $genbutton;
			$out.= '</th>';

			if (!empty($hookmanager->hooks['formfile']))
			{
				foreach($hookmanager->hooks['formfile'] as $module)
				{
					if (method_exists($module, 'formBuilddocLineOptions')) $out .= '<th></th>';
				}
			}
			$out.= '</tr>';

			// Execute hooks
			$parameters=array('socid'=>(isset($GLOBALS['socid'])?$GLOBALS['socid']:''),'id'=>(isset($GLOBALS['id'])?$GLOBALS['id']:''),'modulepart'=>$modulepart);
			if (is_object($hookmanager))
			{
				$reshook = $hookmanager->executeHooks('formBuilddocOptions',$parameters,$GLOBALS['object']);
				$out.= $hookmanager->resPrint;
			}

		}

		// Get list of files
		if (! empty($filedir))
		{
			$file_list=dol_dir_list($filedir,'files',0,'','(\.meta|_preview.*.*\.png)$','date',SORT_DESC);

			$link_list = array();
			if (is_object($object))
			{
				require_once DOL_DOCUMENT_ROOT . '/core/class/link.class.php';
				$link = new Link($this->db);
				$sortfield = $sortorder = null;
				$res = $link->fetchAll($link_list, $object->element, $object->id, $sortfield, $sortorder);
			}

			$out.= '<!-- html.formfile::showdocuments -->'."\n";

			// Show title of array if not already shown
			if ((! empty($file_list) || ! empty($link_list) || preg_match('/^massfilesarea/', $modulepart)) && ! $headershown)
			{
				$headershown=1;
				$out.= '<div class="titre">'.$titletoshow.'</div>'."\n";
				$out.= '<div class="div-table-responsive-no-min">';
				$out.= '<table class="noborder" summary="listofdocumentstable" id="'.$modulepart.'_table" width="100%">'."\n";
			}

			// Loop on each file found
			if (is_array($file_list))
			{
				foreach($file_list as $file)
				{
					// Define relative path for download link (depends on module)
					$relativepath=$file["name"];										// Cas general
					if ($modulesubdir) $relativepath=$modulesubdir."/".$file["name"];	// Cas propal, facture...
					if ($modulepart == 'export') $relativepath = $file["name"];			// Other case

					$out.= '<tr class="oddeven">';

					$documenturl = DOL_URL_ROOT.'/document.php';
					if (isset($conf->global->DOL_URL_ROOT_DOCUMENT_PHP)) $documenturl=$conf->global->DOL_URL_ROOT_DOCUMENT_PHP;    // To use another wrapper

					// Show file name with link to download
					$out.= '<td class="minwidth200">';
					$out.= '<a class="documentdownload paddingright" href="'.$documenturl.'?modulepart='.$modulepart.'&amp;file='.urlencode($relativepath).($param?'&'.$param:'').'"';
					$mime=dol_mimetype($relativepath,'',0);
					if (preg_match('/text/',$mime)) $out.= ' target="_blank"';
					$out.= ' target="_blank">';
					$out.= img_mime($file["name"],$langs->trans("File").': '.$file["name"]);
					$out.= dol_trunc($file["name"], 150);
					$out.= '</a>'."\n";
					$out.= $this->showPreview($file,$modulepart,$relativepath,0,$param);
					$out.= '</td>';

					// Show file size
					$size=(! empty($file['size'])?$file['size']:dol_filesize($filedir."/".$file["name"]));
					$out.= '<td align="right" class="nowrap">'.dol_print_size($size).'</td>';

					// Show file date
					$date=(! empty($file['date'])?$file['date']:dol_filemtime($filedir."/".$file["name"]));
					$out.= '<td align="right" class="nowrap">'.dol_print_date($date, 'dayhour', 'tzuser').'</td>';

					if ($delallowed || $printer || $morepicto)
					{
						$out.= '<td align="right">';
						if ($delallowed)
						{
							$out.= '<a href="'.$urlsource.(strpos($urlsource,'?')?'&amp;':'?').'action=remove_file&amp;file='.urlencode($relativepath);
							$out.= ($param?'&amp;'.$param:'');
							//$out.= '&modulepart='.$modulepart; // TODO obsolete ?
							//$out.= '&urlsource='.urlencode($urlsource); // TODO obsolete ?
							$out.= '">'.img_picto($langs->trans("Delete"), 'delete.png').'</a>';
						}
						if ($printer)
						{
							//$out.= '<td align="right">';
							$out.= '<a class="paddingleft" href="'.$urlsource.(strpos($urlsource,'?')?'&amp;':'?').'action=print_file&amp;printer='.$modulepart.'&amp;file='.urlencode($relativepath);
							$out.= ($param?'&amp;'.$param:'');
							$out.= '">'.img_picto($langs->trans("PrintFile", $relativepath),'printer.png').'</a>';
						}
						if ($morepicto)
						{
							$morepicto=preg_replace('/__FILENAMEURLENCODED__/',urlencode($relativepath),$morepicto);
							$out.=$morepicto;
						}
						$out.='</td>';
					}

					if (is_object($hookmanager))
					{
						$parameters=array('socid'=>(isset($GLOBALS['socid'])?$GLOBALS['socid']:''),'id'=>(isset($GLOBALS['id'])?$GLOBALS['id']:''),'modulepart'=>$modulepart,'relativepath'=>$relativepath);
						$res = $hookmanager->executeHooks('formBuilddocLineOptions',$parameters,$file);
						if (empty($res))
						{
							$out .= $hookmanager->resPrint;		// Complete line
							$out.= '</tr>';
						}
						else $out = $hookmanager->resPrint;		// Replace line
			  		}
				}

				$this->numoffiles++;
			}
			// Loop on each file found
			if (is_array($link_list))
			{
				$colspan=2;

				foreach($link_list as $file)
				{
					$out.='<tr class="oddeven">';
					$out.='<td colspan="'.$colspan.'" class="maxwidhtonsmartphone">';
					$out.='<a data-ajax="false" href="' . $link->url . '" target="_blank">';
					$out.=$file->label;
					$out.='</a>';
					$out.='</td>';
					$out.='<td align="right">';
					$out.=dol_print_date($file->datea,'dayhour');
					$out.='</td>';
					if ($delallowed || $printer || $morepicto) $out.='<td></td>';
					$out.='</tr>'."\n";
				}
				$this->numoffiles++;
			}

		 	if (count($file_list) == 0 && count($link_list) == 0 && $headershown)
			{
				$out.='<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("None").'</td></tr>'."\n";
			}

		}

		if ($headershown)
		{
			// Affiche pied du tableau
			$out.= "</table>\n";
			$out.= "</div>\n";
			if ($genallowed)
			{
				if (empty($noform)) $out.= '</form>'."\n";
			}
		}
		$out.= '<!-- End show_document -->'."\n";
		//return ($i?$i:$headershown);
		return $out;
	}

}