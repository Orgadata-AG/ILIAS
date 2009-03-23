<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* Lucene query parser
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesSearch
*/
class ilLuceneQueryParser
{
	private $query_string;
	private $parsed_query;

	/**
	 * Constructor 
	 * @param string query string
	 * @return
	 */
	public function __construct($a_query_string)
	{
		$this->query_string = $a_query_string;
	}
	
	/**
	 * parse query string 
	 * @return
	 */
	public function parse()
	{
		$this->parsed_query = preg_replace_callback('/(owner:)\s?([A-Za-z0-9_\.\+\*\@!\$\%\~\-]+)/',array($this,'replaceOwnerCallback'),$this->query_string);
	}
	
	/**
	 * get query 
	 * @return
	 */
	public function getQuery()
	{
		return $this->parsed_query;	 
	}
	
	/**
	 * Replace owner callback (preg_replace_callback)
	 */
	protected function replaceOwnerCallback($matches)
	{
		if(isset($matches[2]))
		{
			if($usr_id = ilObjUser::_loginExists($matches[2]))
			{
				return $matches[1].$usr_id;
			}	
		}
		return $matches[0];
	}
}
?>
