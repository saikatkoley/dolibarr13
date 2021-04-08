<?php
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
include_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';

class CustomDolGraph extends DolGraph
{
	/**
	 * Constructor
	 *
	 * @param	string	$library		'auto' (default)
	 */
	public function __construct($library = 'auto')
	{
		global $conf;
		global $theme_bordercolor, $theme_datacolor, $theme_bgcolor;
	}

	/**
	 * Set Y precision added by Saikat Koley on 8th April 2021 copied from  Dolibarr V7
	 *
	 * @param 	float	$which_prec		Precision
	 * @return 	boolean
	 */
	function SetPrecisionY($which_prec)
	{
		$this->PrecisionY = $which_prec;
		return true;
	}
}