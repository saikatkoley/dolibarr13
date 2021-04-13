<?php
/* Copyright (C) 2000-2007  Rodolphe Quiedeville            <rodolphe@quiedeville.org>
 * Copyright (C) 2003       Jean-Louis Bergamo          <jlb@j1b.org>
 * Copyright (C) 2004-2018  Laurent Destailleur         <eldy@users.sourceforge.net>
 * Copyright (C) 2004       Sebastien Di Cintio         <sdicintio@ressource-toi.org>
 * Copyright (C) 2004       Benoit Mortier              <benoit.mortier@opensides.be>
 * Copyright (C) 2004       Christophe Combelles            <ccomb@free.fr>
 * Copyright (C) 2005-2019  Regis Houssin               <regis.houssin@inodbox.com>
 * Copyright (C) 2008       Raphael Bertrand (Resultic) <raphael.bertrand@resultic.fr>
 * Copyright (C) 2010-2018  Juanjo Menent               <jmenent@2byte.es>
 * Copyright (C) 2013       Cédric Salvador             <csalvador@gpcsolutions.fr>
 * Copyright (C) 2013-2017  Alexandre Spangaro          <aspangaro@open-dsi.fr>
 * Copyright (C) 2014       Cédric GROSS                    <c.gross@kreiz-it.fr>
 * Copyright (C) 2014-2015  Marcos García               <marcosgdf@gmail.com>
 * Copyright (C) 2015       Jean-François Ferry         <jfefe@aternatik.fr>
 * Copyright (C) 2018-2019  Frédéric France             <frederic.france@netlogic.fr>
 * Copyright (C) 2019       Thibault Foucart            <support@ptibogxiv.net>
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
 * or see https://www.gnu.org/
 */

/**
 *  \file           htdocs/core/lib/functions.lib.php
 *  \brief          A set of functions for Dolibarr
 *                  This file contains all frequently used functions.
 */

include_once DOL_DOCUMENT_ROOT.'/core/lib/json.lib.php';


/**
 * Function dolGetButtonTitle : this kind of buttons are used in title in list
 *
 * @param string    $label      label of button
 * @param string    $helpText   optional : content for help tooltip
 * @param string    $iconClass  class for icon element (Example: 'fa fa-file')
 * @param string    $url        the url for link
 * @param string    $id         attribute id of button
 * @param int       $status     0 no user rights, 1 active, -1 Feature Disabled, -2 disable Other reason use helpText as tooltip
 * @param array     $params     various params for future : recommended rather than adding more function arguments
 * @return string               html button
 */
function CustomdolGetButtonTitle($label, $helpText = '', $iconClass = 'fa fa-file', $url = '', $id = '', $status = 1, $params = array())
{
    global $langs, $conf, $user;

    // Actually this conf is used in css too for external module compatibility and smooth transition to this function
    if (!empty($conf->global->MAIN_BUTTON_HIDE_UNAUTHORIZED) && (!$user->admin) && $status <= 0) {
        return '';
    }

    $class = 'btnTitle';

    // hidden conf keep during button transition TODO: remove this block
    if (!empty($conf->global->MAIN_USE_OLD_TITLE_BUTTON)) {
        $class = 'butActionNew';
    }
    if (!empty($params['morecss'])) $class .= ' '.$params['morecss'];

    $attr = array(
        'class' => $class
        ,'href' => empty($url) ? '' : $url
    );

    if (!empty($helpText)) {
        $attr['title'] = dol_escape_htmltag($helpText);
    }

    if ($status <= 0) {
        $attr['class'] .= ' refused';

        // hidden conf keep during button transition TODO: remove this block
        if (!empty($conf->global->MAIN_USE_OLD_TITLE_BUTTON)) {
            $attr['class'] = 'butActionNewRefused';
        }

        $attr['href'] = '';

        if ($status == -1) { // disable
            $attr['title'] = dol_escape_htmltag($langs->transnoentitiesnoconv("FeatureDisabled"));
        }
        elseif ($status == 0) { // Not enough permissions
            $attr['title'] = dol_escape_htmltag($langs->transnoentitiesnoconv("NotEnoughPermissions"));
        }
    }

    if (!empty($attr['title'])) {
        $attr['class'] .= ' classfortooltip';
    }

    if (empty($id)) {
        $attr['id'] = $id;
    }

    // Override attr
    if (!empty($params['attr']) && is_array($params['attr'])) {
        foreach ($params['attr'] as $key => $value) {
            if ($key == 'class') {
                $attr['class'] .= ' '.$value;
            }
            elseif ($key == 'classOverride') {
                $attr['class'] = $value;
            }
            else {
                $attr[$key] = $value;
            }
        }
    }

    if (isset($attr['href']) && empty($attr['href'])) {
        unset($attr['href']);
    }

    // TODO : add a hook

    // escape all attribute
    $attr = array_map('dol_escape_htmltag', $attr);

    $TCompiledAttr = array();
    foreach ($attr as $key => $value) {
        $TCompiledAttr[] = $key.'="'.$value.'"';
    }

    $compiledAttributes = (empty($TCompiledAttr) ? '' : implode(' ', $TCompiledAttr));

    $tag = (empty($attr['href']) ? 'span' : 'a');

    $button = '<'.$tag.' '.$compiledAttributes.' >';
    $button .= '<span class="'.$iconClass.' valignmiddle btnTitle-icon"></span>';
    $button .= '<span class="valignmiddle text-plus-circle btnTitle-label'.(empty($params['forcenohideoftext']) ? ' hideonsmartphone' : '').'">'.$label.'</span>';
    $button .= '</'.$tag.'>';

    // hidden conf keep during button transition TODO: remove this block
    if (!empty($conf->global->MAIN_USE_OLD_TITLE_BUTTON)) {
        $button = '<'.$tag.' '.$compiledAttributes.' ><span class="text-plus-circle">'.$label.'</span>';
        $button .= '<span class="'.$iconClass.' valignmiddle"></span>';
        $button .= '</'.$tag.'>';
    }

    return $button;
}