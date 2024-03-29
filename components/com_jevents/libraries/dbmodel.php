<?php

/**
 * JEvents Component for Joomla 1.5.x
 *
 * @version     $Id: dbmodel.php 3575 2012-05-01 14:06:28Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C) 2008-2009 GWE Systems Ltd, 2006-2008 JEvents Project Group
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
defined('_JEXEC') or die('Restricted access');

// load language constants
JEVHelper::loadLanguage('front');

class JEventsDBModel
{

	var $cfg = null;
	var $datamodel = null;
	var $legacyEvents = null;

	function JEventsDBModel(&$datamodel)
	{
		$this->cfg = & JEVConfig::getInstance();
		// TODO - remove legacy code
		$this->legacyEvents = 0;

		$this->datamodel = & $datamodel;
				
		$params = JComponentHelper::getParams(JEV_COM_COMPONENT);
		if (!JVersion::isCompatible("1.6.0")){
			// Multi-category events only supported in Joomla 2.5 + so disable elsewhere
			$params->set('multicategory',0);
		}
		
	}

	function accessibleCategoryList($aid=null, $catids=null, $catidList=null)
	{
		if (is_null($aid))
		{
			$aid = $this->datamodel->aid;
		}
		if (is_null($catids))
		{
			$catids = $this->datamodel->catids;
		}
		if (is_null($catidList))
		{
			$catidList = $this->datamodel->catidList;
		}

		$sectionname = JEV_COM_COMPONENT;

		static $instances;

		if (!$instances)
		{
			$instances = array();
		}
		// calculate unique index identifier
		$index = $aid . '+' . $catidList;
		// if catidList = 0 then the result is the same as a blank so slight time saving
		if (is_null($catidList) || $catidList == 0)
		{
			$index = $aid . '+';
		}

		$db = & JFactory::getDBO();

		$where = "";

		if (!array_key_exists($index, $instances))
		{
			if (JVersion::isCompatible("1.6.0"))
			{
				static $allcats;
				if (!isset($allcats))
				{
					jimport("joomla.application.categories");
					$allcats = JCategories::getInstance("jevents");
					// prepopulate the list internally
					$allcats->get('root');
				}

				$catids = explode(",", $catidList);
				$catwhere = array();
				$hascatid=false;
				foreach ($catids as $catid)
				{
					$catid = intval($catid);
					if ($catid > 0)
					{
						$hascatid=true;
						$cat = $allcats->get($catid);
						if ($cat)
						{
							//$catwhere[] = "(c.lft<=" . $cat->rgt . " AND c.rgt>=" . $cat->lft." )";
							$catwhere[] = "(c.lft>=" . $cat->lft . " AND c.rgt<=" . $cat->rgt . " )";
						}
					}
				}
				if (count($catwhere) > 0)
				{
					$where = "AND (" . implode(" OR ", $catwhere) . ")";
				}
				// do we have a complete set of inaccessible or unpublished categories - if so then we must block all events 
				if($hascatid && count($catwhere)==0){
					$where = " AND 0 ";
				}

				$q_published = JFactory::getApplication()->isAdmin() ? "\n AND c.published >= 0" : "\n AND c.published = 1";
				$query = "SELECT c.id"
						. "\n FROM #__categories AS c"
						. "\n WHERE c.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . $aid . ')' : ' <=  ' . $aid)
						. $q_published
						. "\n AND c.extension = '" . $sectionname . "'"
						. "\n " . $where;
				;

				$db->setQuery($query);
				$catlist = $db->loadResultArray();

				$instances[$index] = implode(',', array_merge(array(-1), $catlist));
			}
			else
			{
				if (count($catids) > 0 && !is_null($catidList) && $catidList != "0")
				{
					$where = ' AND (c.id IN (' . $catidList . ') OR p.id IN (' . $catidList . ')  OR gp.id IN (' . $catidList . ') OR ggp.id IN (' . $catidList . '))';
				}

				$q_published = JFactory::getApplication()->isAdmin() ? "\n AND c.published >= 0" : "\n AND c.published = 1";
				$query = "SELECT c.id"
						. "\n FROM #__categories AS c"
						. ' LEFT JOIN #__categories AS p ON p.id=c.parent_id'
						. ' LEFT JOIN #__categories AS gp ON gp.id=p.parent_id '
						. ' LEFT JOIN #__categories AS ggp ON ggp.id=gp.parent_id '
						. "\n WHERE c.access " . ( version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . $aid . ')' : ' <=  ' . $aid)
						. $q_published
						. "\n AND c.section = '" . $sectionname . "'"
						. "\n " . $where;
				;

				$db->setQuery($query);
				$catlist = $db->loadResultArray();

				$instances[$index] = implode(',', array_merge(array(-1), $catlist));
			}
			$dispatcher = & JDispatcher::getInstance();
			$dispatcher->trigger('onGetAccessibleCategories', array(& $instances[$index]));
			if (count($instances[$index]) == 0)
			{
				$instances[$index] = array(-1);
			}
		}
		return $instances[$index];

	}

	function getCategoryInfo($catids=null, $aid=null)
	{

		$db = & JFactory::getDBO();
		if (is_null($aid))
		{
			$aid = $this->datamodel->aid;
		}
		if (is_null($catids))
		{
			$catids = $this->datamodel->catids;
		}

		$catidList = implode(",", $catids);

		$cfg = & JEVConfig::getInstance();
		$sectionname = JEV_COM_COMPONENT;

		static $instances;

		if (!$instances)
		{
			$instances = array();
		}

		// calculate unique index identifier
		$index = $aid . '+' . $catidList;
		$where = null;

		if (!array_key_exists($index, $instances))
		{
			if (count($catids) > 0 && $catidList != "0" && strlen($catidList) != "")
			{
				$where = ' AND c.id IN (' . $catidList . ') ';
			}

			$q_published = JFactory::getApplication()->isAdmin() ? "\n AND c.published >= 0" : "\n AND c.published = 1";
			$query = "SELECT c.*"
					. (JVersion::isCompatible("1.6.0") ? "" : ", ex.*")
					. "\n FROM #__categories AS c"
					. (JVersion::isCompatible("1.6.0") ? "" : " LEFT JOIN #__jevents_categories as ex on ex.id=c.id")
					. "\n WHERE c.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . $aid . ')' : ' <=  ' . $aid)
					. $q_published
					. (JVersion::isCompatible("1.6.0") ? ' AND c.extension ' : ' AND c.section') . ' = ' . $db->Quote($sectionname)
					. "\n " . $where;
			;

			$db->setQuery($query);
			$catlist = $db->loadObjectList('id');

			$instances[$index] = $catlist;
		}
		return $instances[$index];

	}

	function getChildCategories($catids=null, $levels=1, $aid=null)
	{

		$db = & JFactory::getDBO();
		if (is_null($aid))
		{
			$aid = $this->datamodel->aid;
		}
		if (is_null($catids))
		{
			$catids = $this->datamodel->catids;
		}

		$catidList = implode(",", $catids);

		$cfg = & JEVConfig::getInstance();
		$sectionname = JEV_COM_COMPONENT;

		static $instances;

		if (!$instances)
		{
			$instances = array();
		}

		// calculate unique index identifier
		$index = $aid . '+' . $catidList;
		$where = null;

		if (!array_key_exists($index, $instances))
		{
			if (count($catids) > 0 && $catidList != "0" && strlen($catidList) != "")
			{
				$where = ' AND (p.id IN (' . $catidList . ') ' . ($levels > 1 ? ' OR gp.id IN (' . $catidList . ')' : '') . ($levels > 2 ? ' OR ggp.id IN (' . $catidList . ')' : '') . ')';
			}
			// TODO check if this should also check abncestry based on $levels
			$where .= ' AND p.id IS NOT NULL ';

			$q_published = JFactory::getApplication()->isAdmin() ? "\n AND c.published >= 0" : "\n AND c.published = 1";
			$query = "SELECT c.*"
					. "\n FROM #__categories AS c"
					. ' LEFT JOIN #__categories AS p ON p.id=c.parent_id'
					. ($levels > 1 ? ' LEFT JOIN #__categories AS gp ON gp.id=p.parent_id ' : '')
					. ($levels > 2 ? ' LEFT JOIN #__categories AS ggp ON ggp.id=gp.parent_id ' : '')
					. "\n WHERE c.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . $aid . ')' : ' <=  ' . $aid)
					. $q_published
					. (JVersion::isCompatible("1.6.0") ? ' AND c.extension ' : ' AND c.section') . ' = ' . $db->Quote($sectionname)
					. "\n " . $where;
			;


			$db->setQuery($query);
			$catlist = $db->loadObjectList('id');

			$instances[$index] = $catlist;
		}
		return $instances[$index];

	}

	function listEvents($startdate, $enddate, $order="")
	{
		if (!$this->legacyEvents)
		{
			return array();
		}

	}

	function _cachedlistEvents($query, $langtag, $count=false)
	{
		$db = & JFactory::getDBO();
		$db->setQuery($query);
		if ($count)
		{
			$db->query();
			return $db->getNumRows();
		}

		$rows = $db->loadObjectList();
		$rowcount = count($rows);
		if ($rowcount > 0)
		{
			usort($rows, array('JEventsDBModel', 'sortEvents'));
		}

		for ($i = 0; $i < $rowcount; $i++)
		{
			$rows[$i] = new jEventCal($rows[$i]);
		}
		return $rows;

	}

	/**
	 * Fetch recently created events
	 */
	// Allow the passing of filters directly into this function for use in 3rd party extensions etc.
	function recentIcalEvents($startdate, $enddate, $limit=10, $noRepeats=0)
	{
		$user =  JFactory::getUser();
		$db =  JFactory::getDBO();
		$lang = & JFactory::getLanguage();
		$langtag = $lang->getTag();

		if (strpos($startdate, "-") === false)
		{
			$startdate = JevDate::strftime('%Y-%m-%d 00:00:00', $startdate);
			$enddate = JevDate::strftime('%Y-%m-%d 23:59:59', $enddate);
		}

		// process the new plugins
		// get extra data and conditionality from plugins
		$extrawhere = array();
		$extrajoin = array();
		$extrafields = "";  // must have comma prefix
		$extratables = "";  // must have comma prefix
		$needsgroup = false;

		$filterarray = array("published", "justmine", "category", "search");

		// If there are extra filters from the module then apply them now
		$reg = & JFactory::getConfig();
		$modparams = $reg->getValue("jev.modparams", false);
		if ($modparams && $modparams->getValue("extrafilters", false))
		{
			$filterarray = array_merge($filterarray, explode(",", $modparams->getValue("extrafilters", false)));
		}

		$filters = jevFilterProcessing::getInstance($filterarray);
		$filters->setWhereJoin($extrawhere, $extrajoin);
		$needsgroup = $filters->needsGroupBy();

		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onListIcalEvents', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin, & $needsgroup));

		$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
			$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
			$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
			$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
			$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
			$needsgroup = true;
			$catwhere = "\n WHERE 1 ";
		}
		
		$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
		$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );

		// get the event ids first
		$query = "SELECT  ev.ev_id FROM #__jevents_repetition as rpt"
				. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
				. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
				. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
				. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
				. $extrajoin
				.$catwhere 
				. "\n AND ev.created >= '$startdate' AND ev.created <= '$enddate'"
				. $extrawhere
				. "\n AND ev.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. " \n AND icsf.state=1"
				. "\n AND icsf.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				// published state is now handled by filter
				. "\n GROUP BY ev.ev_id";

		// always in reverse created date order!
		$query .= " ORDER BY ev.created DESC ";

		// This limit will always be enough
		$query .= " LIMIT " . $limit;


		$db = JFactory::getDBO();
		$db->setQuery($query);
		$ids = $db->loadResultArray();
		array_push($ids, 0);
		$ids = implode(",", $ids);

		$groupby = "\n GROUP BY rpt.rp_id";
		if ($noRepeats)
			$groupby = "\n GROUP BY ev.ev_id";

		// This version picks the details from the details table
		// ideally we should check if the event is a repeat but this involves extra queries unfortunately
		$query = "SELECT rpt.*, ev.*, rr.*, det.*, ev.state as published, ev.created as created $extrafields"
				. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
				. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
				. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
				. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
				. "\n FROM #__jevents_repetition as rpt"
				. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
				. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
				. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
				. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
				. $extrajoin
				.$catwhere 
				. "\n AND ev.created >= '$startdate' AND ev.created <= '$enddate'"
				. $extrawhere
				. "\n AND ev.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. "  AND icsf.state=1 "
				. "\n AND icsf.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. "  AND ev.ev_id IN (" . $ids . ")"
				// published state is now handled by filter
				//. "\n AND ev.state=1"
				. ($needsgroup ? $groupby : "");
		$query .= " ORDER BY ev.created DESC ";
		//echo str_replace("#__", 'jos_', $query);
		$cache = & JFactory::getCache(JEV_COM_COMPONENT);
		$rows = $cache->call('JEventsDBModel::_cachedlistIcalEvents', $query, $langtag);

		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onDisplayCustomFieldsMultiRowUncached', array(&$rows));

		return $rows;

	}

	/**
	 * Fetch recently created events
	 */
	// Allow the passing of filters directly into this function for use in 3rd party extensions etc.
	function popularIcalEvents($startdate, $enddate, $limit=10, $noRepeats=0)
	{
		$user = JFactory::getUser();
		$db =  JFactory::getDBO();
		$lang = & JFactory::getLanguage();
		$langtag = $lang->getTag();

		if (strpos($startdate, "-") === false)
		{
			$startdate = strftime('%Y-%m-%d 00:00:00', $startdate);
			$enddate = strftime('%Y-%m-%d 23:59:59', $enddate);
		}

		// process the new plugins
		// get extra data and conditionality from plugins
		$extrawhere = array();
		$extrajoin = array();
		$extrafields = "";  // must have comma prefix
		$extratables = "";  // must have comma prefix
		$needsgroup = false;

		$filterarray = array("published", "justmine", "category", "search");

		// If there are extra filters from the module then apply them now
		$reg = & JFactory::getConfig();
		$modparams = $reg->getValue("jev.modparams", false);
		if ($modparams && $modparams->getValue("extrafilters", false))
		{
			$filterarray = array_merge($filterarray, explode(",", $modparams->getValue("extrafilters", false)));
		}

		$filters = jevFilterProcessing::getInstance($filterarray);
		$filters->setWhereJoin($extrawhere, $extrajoin);
		$needsgroup = $filters->needsGroupBy();

		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onListIcalEvents', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin, & $needsgroup));

		$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
			$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
			$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
			$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
			$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
			$needsgroup = true;
			$catwhere = "\n WHERE 1 ";
		}
				
		$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
		$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );

		// get the event ids first - split into 2 queries to pick up the ones after now and the ones before
		$t_datenow = JEVHelper::getNow();
		$t_datenowSQL = $t_datenow->toMysql();

		// get the event ids first
		$query = "SELECT  ev.ev_id FROM #__jevents_repetition as rpt"
				. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
				. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
				. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
				. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
				. $extrajoin
				.$catwhere 
				// New equivalent but simpler test
				. "\n AND rpt.endrepeat >= '$t_datenowSQL' AND rpt.startrepeat <= '$enddate'"
				// We only show events on their first day if they are not to be shown on multiple days so also add this condition
				. "\n AND ((rpt.startrepeat >= '$t_datenowSQL' AND det.multiday=0) OR  det.multiday=1)"
				. $extrawhere
				. "\n AND ev.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. " \n AND icsf.state=1"
				. "\n AND icsf.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				// published state is now handled by filter
				. "\n AND rpt.startrepeat=(SELECT MIN(startrepeat) FROM #__jevents_repetition as rpt2 WHERE rpt2.eventid=rpt.eventid AND rpt2.startrepeat >= '$t_datenowSQL' AND rpt2.startrepeat <= '$enddate')"
				. "\n GROUP BY ev.ev_id";

		// always in reverse hits  order!
		$query .= " ORDER BY det.hits DESC ";

		// This limit will always be enough
		$query .= " LIMIT " . $limit;


		$db = JFactory::getDBO();
		$db->setQuery($query);
		$ids = $db->loadResultArray();
		array_push($ids, 0);
		$ids = implode(",", $ids);

		$groupby = "\n GROUP BY rpt.rp_id";
		if ($noRepeats)
			$groupby = "\n GROUP BY ev.ev_id";

		// This version picks the details from the details table
		// ideally we should check if the event is a repeat but this involves extra queries unfortunately
		$query = "SELECT rpt.*, ev.*, rr.*, det.*, ev.state as published, ev.created as created $extrafields"
				. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
				. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
				. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
				. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
				. "\n FROM #__jevents_repetition as rpt"
				. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
				. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
				. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
				. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
				. $extrajoin
				.$catwhere 
				// New equivalent but simpler test
				. "\n AND rpt.endrepeat >= '$startdate' AND rpt.startrepeat <= '$enddate'"
				. $extrawhere
				. "\n AND ev.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. "  AND icsf.state=1 AND icsf.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. "  AND ev.ev_id IN (" . $ids . ")"
				// published state is now handled by filter
				. ($needsgroup ? $groupby : "");		
		$query .= " ORDER BY det.hits DESC ";

		$cache = & JFactory::getCache(JEV_COM_COMPONENT);
		$rows = $cache->call('JEventsDBModel::_cachedlistIcalEvents', $query, $langtag);

		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onDisplayCustomFieldsMultiRowUncached', array(&$rows));

		return $rows;

	}

	/* Special version for Latest events module */

	function listLatestIcalEvents($startdate, $enddate, $limit=10, $noRepeats=0, $multidayTreatment = 0)
	{
		$user =  JFactory::getUser();
		$lang = & JFactory::getLanguage();
		$langtag = $lang->getTag();

		if (strpos($startdate, "-") === false)
		{
			$startdate = JevDate::strftime('%Y-%m-%d 00:00:00', $startdate);
			$enddate = JevDate::strftime('%Y-%m-%d 23:59:59', $enddate);
		}

		// process the new plugins
		// get extra data and conditionality from plugins
		$extrawhere = array();
		$extrajoin = array();
		$rptwhere = array();		
		$extrafields = "";  // must have comma prefix
		$extratables = "";  // must have comma prefix
		$needsgroup = false;

		$filterarray = array("published", "justmine", "category", "search");

		// If there are extra filters from the module then apply them now
		$reg = & JFactory::getConfig();
		$modparams = $reg->getValue("jev.modparams", false);
		if ($modparams && $modparams->getValue("extrafilters", false))
		{
			$filterarray = array_merge($filterarray, explode(",", $modparams->getValue("extrafilters", false)));
		}

		$filters = jevFilterProcessing::getInstance($filterarray);
		$filters->setWhereJoin($extrawhere, $extrajoin);
		$needsgroup = $filters->needsGroupBy();

		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onListIcalEvents', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin, & $needsgroup, & $rptwhere));

		// What if join multiplies the rows?
		// Useful MySQL link http://forums.mysql.com/read.php?10,228378,228492#msg-228492
		// concat with group
		// http://www.mysqlperformanceblog.com/2006/09/04/group_concat-useful-group-by-extension/

		// did any of the plugins adjust the range of dateds allowed eg. timelimit plugin - is so then we need to use this information otherwise we  get problems
		$regex = "#(rpt.endrepeat>='[0-9:\- ]*' AND rpt.startrepeat<='[0-9:\- ]*')#";
		foreach ($extrawhere as $exwhere){
			if (preg_match($regex, $exwhere)){
				$rptwhere[] = str_replace("rpt.","rpt2.",$exwhere);
			}
		}
		$rptwhere = ( count($rptwhere) ? ' AND ' . implode(' AND ', $rptwhere) : '' );
		
		$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
			$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
			$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
			$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
			$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
			$needsgroup = true;
			$catwhere = "\n WHERE 1 ";
		}
				
		$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
		$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );
				
		// get the event ids first - split into 2 queries to pick up the ones after now and the ones before 
		$t_datenow = JEVHelper::getNow();
		$t_datenowSQL = $t_datenow->toMysql();

		// multiday condition
		if ($multidayTreatment == 3)
		{
			// We only show events once regardless of multiday setting of event so we allow them all through here!
			$multiday = "";
			$multiday2 = "";
		}
		else if ($multidayTreatment == 2)
		{
			// We only show events on their first day only regardless of multiday setting of event so we allow them all through here!
			$multiday = "";
			$multiday2 = "";
		}
		else if ($multidayTreatment == 1)
		{
			// We only show events on all days regardless of multiday setting of event so we allow them all through here!
			$multiday = "";
			$multiday2 = "";
		}
		else
		{
			// We only show events on their first day if they are not to be shown on multiple days so also add this condition
			// i.e. the event settings are used
			// This is the true version of these conditions
			//$multiday = "\n AND ((rpt.startrepeat >= '$startdate' AND det.multiday=0) OR  det.multiday=1)";
			//$multiday2 = "\n AND ((rpt.startrepeat <= '$startdate' AND det.multiday=0) OR  det.multiday=1)";
			// BUT this is logically equivalent and appears much faster  on some databases
			$multiday = "\n AND (rpt.startrepeat >= '$startdate' OR  det.multiday=1)";
			$multiday2 = "\n AND (rpt.startrepeat <= '$startdate'OR  det.multiday=1)";			
		}

		if ($noRepeats)
		{
			// Display a repeating event ONCE we group by event id selecting the most appropriate repeat for each one

			// Find the ones after now (only if not past only)
			$rows1 = array();
			if ($enddate >= $t_datenowSQL && $modparams->get("pastonly", 0) != 1)
			{
				$query = "SELECT rpt.*, ev.*, rr.*, det.*, ev.state as published, ev.created as created $extrafields"
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
					. "\n FROM #__jevents_repetition as rpt"
						. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
						. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
						. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
						. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
						. $extrajoin
						. $catwhere
						// New equivalent but simpler test
						. "\n AND rpt.endrepeat >= '$t_datenowSQL' AND rpt.startrepeat <= '$enddate'"
						. $multiday
						. $extrawhere
						. "\n AND ev.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
						. "  AND icsf.state=1 "
						. "\n AND icsf.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
						// published state is now handled by filter
						. "\n AND rpt.startrepeat=(
				SELECT MIN(startrepeat) FROM #__jevents_repetition as rpt2
				WHERE rpt2.eventid=rpt.eventid
				AND  (
					(rpt2.startrepeat >= '$t_datenowSQL' AND rpt2.startrepeat <= '$enddate')
					OR (rpt2.startrepeat <= '$t_datenowSQL' AND rpt2.endrepeat  > '$t_datenowSQL'  AND det.multiday=1)
					)
				$rptwhere
			) 
			GROUP BY ev.ev_id
			ORDER BY rpt.startrepeat"	;

				// This limit will always be enough
				$query .= " LIMIT " . $limit;
				
				$cache = & JFactory::getCache(JEV_COM_COMPONENT);
list($usec, $sec) = explode(" ", microtime());
$dbstart = ((float)$usec + (float)$sec);				
				$rows1 = $cache->call('JEventsDBModel::_cachedlistIcalEvents', $query, $langtag);
list ($usec,$sec) = explode(" ", microtime());
$dbend = (float)$usec + (float)$sec;
//echo "query ". round($dbend - $dbstart,4)."<br/>";
				
			}

			// Before now (only if not past only == future events)
			$rows2 = array();			
			if ($startdate <= $t_datenowSQL && $modparams->get("pastonly", 0) < 2)
			{
				// note the order is the ones nearest today
				$query = "SELECT rpt.*, ev.*, rr.*, det.*, ev.state as published, ev.created as created $extrafields"
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
					. "\n FROM #__jevents_repetition as rpt"
						. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
						. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
						. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
						. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
						. $extrajoin
						. $catwhere
						// New equivalent but simpler test
						. "\n AND rpt.endrepeat >= '$startdate' AND rpt.startrepeat <= '$t_datenowSQL'"
						. $multiday
						. $extrawhere
						. "\n AND ev.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
						. "  AND icsf.state=1 "
						. "\n AND icsf.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
						// published state is now handled by filter
						. "\n AND rpt.startrepeat=(
					SELECT MAX(startrepeat) FROM #__jevents_repetition as rpt2
					 WHERE rpt2.eventid=rpt.eventid
					AND rpt2.startrepeat <= '$t_datenowSQL' AND rpt2.startrepeat >= '$startdate'
					$rptwhere
				)
				GROUP BY ev.ev_id
				ORDER BY rpt.startrepeat desc"
				;

				// This limit will always be enough
				$query .= " LIMIT " . $limit;

				$cache = & JFactory::getCache(JEV_COM_COMPONENT);
list($usec, $sec) = explode(" ", microtime());
$dbstart = ((float)$usec + (float)$sec);				
				$rows2 = $cache->call('JEventsDBModel::_cachedlistIcalEvents', $query, $langtag);
list ($usec,$sec) = explode(" ", microtime());
$dbend = (float)$usec + (float)$sec;
//echo "query ". round($dbend - $dbstart,4)."<br/>";
			}

			$rows3 = array();
			if ($multidayTreatment != 2 && $multidayTreatment != 3)
			{
				// Mutli day events
				$query = "SELECT rpt.*, ev.*, rr.*, det.*, ev.state as published, ev.created as created $extrafields"
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
					. "\n FROM #__jevents_repetition as rpt"
						. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
						. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
						. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
						. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
						. $extrajoin
						. $catwhere
						// Must be starting before NOW otherwise would already be picked up
						. "\n AND rpt.endrepeat >= '$startdate' AND rpt.startrepeat <= '$t_datenowSQL'"
						. $multiday2
						. $extrawhere
						. "\n AND ev.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
						. "  AND icsf.state=1 "
						. "\n AND icsf.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
						// published state is now handled by filter
						// This is the correct approach but can be slow
						/*
						. "\n AND rpt.startrepeat=(
				SELECT MAX(startrepeat) FROM #__jevents_repetition as rpt2
				 WHERE rpt2.eventid=rpt.eventid
				AND rpt2.startrepeat <= '$t_datenowSQL' AND rpt2.endrepeat >= '$t_datenowSQL'
				$rptwhere
			)"		
						 */
						// This is the alternative - it could produce unexpected results if you have overlapping repeats over 'now' but this is  low risk
						."\n AND rpt.startrepeat <= '$t_datenowSQL' AND rpt.endrepeat >= '$t_datenowSQL'"
						
						." \n GROUP BY ev.ev_id
						ORDER BY rpt.startrepeat"
				;

				// This limit will always be enough
				$query .= " LIMIT " . $limit;

				$cache = & JFactory::getCache(JEV_COM_COMPONENT);
list($usec, $sec) = explode(" ", microtime());
$dbstart = ((float)$usec + (float)$sec);				
				$rows3 = $cache->call('JEventsDBModel::_cachedlistIcalEvents', $query, $langtag);
list ($usec,$sec) = explode(" ", microtime());
$dbend = (float)$usec + (float)$sec;
//echo "query ". round($dbend - $dbstart,4)."<br/>";
			}
			
			// ensure specific event is not used more than once
			$events = array();
			$rows = array();
			// future events
			foreach ($rows1 as $val){
				if (!in_array($val->ev_id(), $events)){
					//echo $val->_startrepeat." ".$val->ev_id()." ".$val->title()."<br/>";
					$events[] = $val->ev_id();
					$rows[]=$val;
				}
			}
			// straddling multi-day event
			foreach ($rows3 as $val){
				if (!in_array($val->ev_id(), $events)){
					//echo $val->_startrepeat." ".$val->ev_id()." ".$val->title()."<br/>";
					$events[] = $val->ev_id();
					$rows[]=$val;
				}
			}
			// past events 
			foreach ($rows2 as $val){
				if (!in_array($val->ev_id(), $events)){
					//echo $val->_startrepeat." ".$val->ev_id()." ".$val->title()."<br/>";
					$events[] = $val->ev_id();
					$rows[]=$val;
				}
			}
			//echo "count rows ".count($rows1)." ".count($rows2)." ".count($rows3)." ".count($rows)."<br/>";			
			unset($rows1);
			unset($rows2);
			unset($rows3);
			
		}
		else {
			// Display a repeating event for EACH repeat
			// We therefore fetch 3 sets of possible repeats if necessary i.e. not over the limit!

			// Find the ones after now (only if not past only)
			$rows1 = array();
			if ($enddate >= $t_datenowSQL && $modparams->get("pastonly", 0) != 1)
			{
				$query = "SELECT rpt.*, ev.*, rr.*, det.*, ev.state as published, ev.created as created $extrafields"
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
					. "\n FROM #__jevents_repetition as rpt"
					. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
					. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
					. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
					. $extrajoin
					. $catwhere
					// New equivalent but simpler test
					. "\n AND rpt.endrepeat >= '$t_datenowSQL' AND rpt.startrepeat <= '$enddate'"
					. $multiday
					. $extrawhere
					. "\n AND ev.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
					. "  AND icsf.state=1 "
					. "\n AND icsf.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
					// published state is now handled by filter
					. "\n GROUP BY rpt.rp_id
					ORDER BY rpt.startrepeat ASC"
				;

				// This limit will always be enough
				$query .= " LIMIT " . $limit;
				
				$cache = & JFactory::getCache(JEV_COM_COMPONENT);
list($usec, $sec) = explode(" ", microtime());
$dbstart = ((float)$usec + (float)$sec);				
				$rows1 = $cache->call('JEventsDBModel::_cachedlistIcalEvents', $query, $langtag);
list ($usec,$sec) = explode(" ", microtime());
$dbend = (float)$usec + (float)$sec;
//echo "query ". round($dbend - $dbstart,4)."<br/>";
			}

			// Before now (only if not past only == future events)
			$rows2 = array();
			if ($startdate <= $t_datenowSQL && $modparams->get("pastonly", 0) < 2)
			{
				// note the order is the ones nearest today
				$query = "SELECT rpt.*, ev.*, rr.*, det.*, ev.state as published, ev.created as created $extrafields"
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
					. "\n FROM #__jevents_repetition as rpt"
						. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
						. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
						. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
						. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
						. $extrajoin
						. $catwhere
						// New equivalent but simpler test
						. "\n AND rpt.endrepeat >= '$startdate' AND rpt.startrepeat <= '$t_datenowSQL'"
						. $multiday
						. $extrawhere
						. "\n AND ev.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
						. "  AND icsf.state=1 "
						. "\n AND icsf.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
						// published state is now handled by filter
						. "\n GROUP BY rpt.rp_id
						ORDER BY rpt.startrepeat desc"
				;

				// This limit will always be enough
				$query .= " LIMIT " . $limit;

				$cache = & JFactory::getCache(JEV_COM_COMPONENT);
list($usec, $sec) = explode(" ", microtime());
$dbstart = ((float)$usec + (float)$sec);				
				$rows2 = $cache->call('JEventsDBModel::_cachedlistIcalEvents', $query, $langtag);
list ($usec,$sec) = explode(" ", microtime());
$dbend = (float)$usec + (float)$sec;
//echo "query ". round($dbend - $dbstart,4)."<br/>";

			}

			$rows3 = array();
			if ($multidayTreatment != 2 && $multidayTreatment != 3)
			{
				// Mutli day events
				$query = "SELECT rpt.*, ev.*, rr.*, det.*, ev.state as published, ev.created as created $extrafields"
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
					. "\n FROM #__jevents_repetition as rpt"
						. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
						. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
						. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
						. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
						. $extrajoin
						. $catwhere
						// Must be starting before NOW otherwise would already be picked up
						. "\n AND rpt.endrepeat >= '$startdate' AND rpt.startrepeat <= '$t_datenowSQL'"
						. $multiday2
						. $extrawhere
						. "\n AND ev.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
						. "  AND icsf.state=1 "
						. "\n AND icsf.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
						// published state is now handled by filter
						. "\n GROUP BY rpt.rp_id
						ORDER BY rpt.startrepeat asc"
				;

				// This limit will always be enough
				$query .= " LIMIT " . $limit;

				$cache = & JFactory::getCache(JEV_COM_COMPONENT);
list($usec, $sec) = explode(" ", microtime());
$dbstart = ((float)$usec + (float)$sec);				
				$rows3 = $cache->call('JEventsDBModel::_cachedlistIcalEvents', $query, $langtag);
list ($usec,$sec) = explode(" ", microtime());
$dbend = (float)$usec + (float)$sec;
//echo "query ". round($dbend - $dbstart,4)."<br/>";
				
			}
			
			// ensure specific repeat is not used more than once
			$repeats = array();
			$rows = array();
			// future events
			foreach ($rows1 as $val){
				if (!in_array($val->rp_id(), $repeats)){
					$repeats[] = $val->rp_id();
					$rows[]=$val;
				}
			}
			// straddling multi-day event
			foreach ($rows3 as $val){
				if (!in_array($val->rp_id(), $repeats)){
					$repeats[] = $val->rp_id();
					$rows[]=$val;
				}
			}
			// past events 
			foreach ($rows2 as $val){
				if (!in_array($val->rp_id(), $repeats)){
					$repeats[] = $val->rp_id();
					$rows[]=$val;
				}
			}
			//echo "count rows ".count($rows1)." ".count($rows2)." ".count($rows3)." ".count($rows)."<br/>";
			unset($rows1);
			unset($rows2);
			unset($rows3);
			
		}
		
		//echo "count rows = ".count($rows)."<Br/>";

		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onDisplayCustomFieldsMultiRowUncached', array(&$rows));
//die();
		return $rows;

	}

	// BAD VERSION - not used
	function listPopularIcalEvents($startdate, $enddate, $limit=10, $noRepeats=0)
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$lang = & JFactory::getLanguage();
		$langtag = $lang->getTag();

		if (strpos($startdate, "-") === false)
		{
			$startdate = JevDate::strftime('%Y-%m-%d 00:00:00', $startdate);
			$enddate = JevDate::strftime('%Y-%m-%d 23:59:59', $enddate);
		}

		// process the new plugins
		// get extra data and conditionality from plugins
		$extrawhere = array();
		$extrajoin = array();
		$extrafields = "";  // must have comma prefix
		$extratables = "";  // must have comma prefix
		$needsgroup = false;

		$filterarray = array("published", "justmine", "category", "search");

		// If there are extra filters from the module then apply them now
		$reg = & JFactory::getConfig();
		$modparams = $reg->getValue("jev.modparams", false);
		if ($modparams && $modparams->getValue("extrafilters", false))
		{
			$filterarray = array_merge($filterarray, explode(",", $modparams->getValue("extrafilters", false)));
		}

		$filters = jevFilterProcessing::getInstance($filterarray);
		$filters->setWhereJoin($extrawhere, $extrajoin);
		$needsgroup = $filters->needsGroupBy();

		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onListIcalEvents', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin, & $needsgroup));

		$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
			$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
			$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
			$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
			$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
			$needsgroup = true;
			$catwhere = "\n WHERE 1 ";
		}
				
		$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
		$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );

		// get the event ids first
		$query = "SELECT  ev.ev_id FROM #__jevents_repetition as rpt"
				. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
				. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
				. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
				. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
				. $extrajoin
				.$catwhere 
				. "\n AND ev.created >= '$startdate' AND ev.created <= '$enddate'"
				. $extrawhere
				. "\n AND ev.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. " \n AND icsf.state=1"
				. "\n AND icsf.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				// published state is now handled by filter
				. "\n GROUP BY ev.ev_id";

		// always in reverse hits order!
		$query .= " ORDER BY det.hits DESC ";

		// This limit will always be enough
		$query .= " LIMIT " . $limit;


		$db = JFactory::getDBO();
		$db->setQuery($query);
		$ids = $db->loadResultArray();
		array_push($ids, 0);
		$ids = implode(",", $ids);

		$groupby = "\n GROUP BY rpt.rp_id";
		if ($noRepeats) {
			$groupby = "\n GROUP BY ev.ev_id";
			$needsgroup = true;
		}

		// This version picks the details from the details table
		// ideally we should check if the event is a repeat but this involves extra queries unfortunately
		$query = "SELECT rpt.*, ev.*, rr.*, det.*, ev.state as published, ev.created as created $extrafields"
				. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
				. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
				. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
				. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
				. "\n FROM #__jevents_repetition as rpt"
				. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
				. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
				. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
				. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
				. $extrajoin
				.$catwhere 
				. "\n AND ev.created >= '$startdate' AND ev.created <= '$enddate'"
				. $extrawhere
				. "\n AND ev.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. "  AND icsf.state=1 "
				. "\n AND icsf.access  " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. "  AND ev.ev_id IN (" . $ids . ")"
				// published state is now handled by filter
				//. "\n AND ev.state=1"
				. ($needsgroup ? $groupby : "");
		$query .= " ORDER BY det.hits DESC ";

		$cache = & JFactory::getCache(JEV_COM_COMPONENT);
		$rows = $cache->call('JEventsDBModel::_cachedlistIcalEvents', $query, $langtag);

		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onDisplayCustomFieldsMultiRowUncached', array(&$rows));

		return $rows;

	}

	// Allow the passing of filters directly into this function for use in 3rd party extensions etc.
	function listIcalEvents($startdate, $enddate, $order="", $filters = false, $extrafields="", $extratables="", $limit="")
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$lang = & JFactory::getLanguage();
		$langtag = $lang->getTag();

		if (strpos($startdate, "-") === false)
		{
			$startdate = JevDate::strftime('%Y-%m-%d 00:00:00', $startdate);
			$enddate = JevDate::strftime('%Y-%m-%d 23:59:59', $enddate);
		}

		// process the new plugins
		// get extra data and conditionality from plugins
		$extrawhere = array();
		$extrajoin = array();
		$extrafields = "";  // must have comma prefix
		// $extratables = "";  // must have comma prefix
		$needsgroup = false;

		if (!$filters)
		{
			$filterarray = array("published", "justmine", "category", "search");

			// If there are extra filters from the module then apply them now
			$reg = & JFactory::getConfig();
			$modparams = $reg->getValue("jev.modparams", false);
			if ($modparams && $modparams->getValue("extrafilters", false))
			{
				$filterarray = array_merge($filterarray, explode(",", $modparams->getValue("extrafilters", false)));
			}

			$filters = jevFilterProcessing::getInstance($filterarray);
			$filters->setWhereJoin($extrawhere, $extrajoin);
			$needsgroup = $filters->needsGroupBy();

			$dispatcher = & JDispatcher::getInstance();
			$dispatcher->trigger('onListIcalEvents', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin, & $needsgroup));

			// What if join multiplies the rows?
			// Useful MySQL link http://forums.mysql.com/read.php?10,228378,228492#msg-228492
			// concat with group
			// http://www.mysqlperformanceblog.com/2006/09/04/group_concat-useful-group-by-extension/
		}
		else
		{
			$filters->setWhereJoin($extrawhere, $extrajoin);
		}

		$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
			$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
			$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
			$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
			$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
			$needsgroup = true;
			$catwhere = "\n WHERE 1 ";
		}
				
		$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
		$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );
			
		// This version picks the details from the details table
		// ideally we should check if the event is a repeat but this involves extra queries unfortunately
		$query = "SELECT det.evdet_id as detailid, rpt.*, ev.*, rr.*, det.* ,  ev.state as published, ev.created as created $extrafields"
				. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
				. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
				. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
				. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
				. "\n FROM #__jevents_repetition as rpt"
				. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
				. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
				. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
				. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
				. $extrajoin
				. $catwhere
				// New equivalent but simpler test
				. "\n AND rpt.endrepeat >= '$startdate' AND rpt.startrepeat <= '$enddate'"
				/*
				  . "\n AND ((rpt.startrepeat >= '$startdate' AND rpt.startrepeat <= '$enddate')"
				  . "\n OR (rpt.endrepeat >= '$startdate' AND rpt.endrepeat <= '$enddate')"
				  // This is redundant!!
				  //. "\n OR (rpt.startrepeat >= '$startdate' AND rpt.endrepeat <= '$enddate')"
				  // This slows the query down
				  . "\n OR (rpt.startrepeat <= '$startdate' AND rpt.endrepeat >= '$enddate')"
				  . "\n )"
				 */
				// Radical alternative - seems slower though
				/*
				  . "\n WHERE rpt.rp_id IN (SELECT  rbd.rp_id
				  FROM jos_jevents_repbyday as rbd
				  WHERE  rbd.catid IN(".$this->accessibleCategoryList().")
				  AND rbd.rptday >= '$startdate' AND rbd.rptday <= '$enddate' )"
				 */
				. $extrawhere
				. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. "  AND icsf.state=1 AND icsf.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				// published state is now handled by filter
				//. "\n AND ev.state=1"
				. ($needsgroup ? "\n GROUP BY rpt.rp_id" : "")
		;

		if ($order != "")
		{
			$query .= " ORDER BY " . $order;
		}
		if ($limit != "")
		{
			$query .= " LIMIT " . $limit;
		}

		$cache = & JFactory::getCache(JEV_COM_COMPONENT);
		$rows = $cache->call('JEventsDBModel::_cachedlistIcalEvents', $query, $langtag);

		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onDisplayCustomFieldsMultiRowUncached', array(&$rows));

		return $rows;

	}

	function _cachedlistIcalEvents($query, $langtag, $count=false)
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$adminuser = JEVHelper::isAdminUser($user);
		$db->setQuery($query);
		if ($adminuser)
		{
			//echo $db->getQuery();
			//echo $db->explain();
			//exit();
		}

		if ($count)
		{
			$db->query();
			return $db->getNumRows();
		}

		$icalrows = $db->loadObjectList();
		if ($adminuser)
		{
			echo $db->getErrorMsg();
		}
		$icalcount = count($icalrows);
		$valid = true;
		for ($i = 0; $i < $icalcount; $i++)
		{
			// only convert rows when necessary
			if ($i == 0 && count(get_object_vars($icalrows[$i])) < 5)
			{
				$valid = false;
				break;
			}
			// convert rows to jIcalEvents
			$icalrows[$i] = new jIcalEventRepeat($icalrows[$i]);
		}

		if (!$valid)
			return $icalrows;

		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onDisplayCustomFieldsMultiRow', array(&$icalrows));

		return $icalrows;

	}

	function listEventsByDateNEW($select_date)
	{
		return $this->listEvents($select_date . " 00:00:00", $select_date . " 23:59:59");

	}

	function listIcalEventsByDay($targetdate)
	{
		// targetdate is midnight at start of day - but just in case
		list ($y, $m, $d) = explode(":", JevDate::strftime('%Y:%m:%d', $targetdate));
		$startdate = JevDate::mktime(0, 0, 0, $m, $d, $y);
		$enddate = JevDate::mktime(23, 59, 59, $m, $d, $y);
		
		// timezone offset (3 hours as a test)
		//$startdate = JevDate::strftime('%Y-%m-%d %H:%M:%S', $startdate+10800);
		//$enddate = JevDate::strftime('%Y-%m-%d %H:%M:%S', $enddate+10800);
		
		return $this->listIcalEvents($startdate, $enddate);

	}

	function listEventsByWeekNEW($weekstart, $weekend)
	{
		return $this->listEvents($weekstart, $weekend);

	}

	function listIcalEventsByWeek($weekstart, $weekend)
	{
		return $this->listIcalEvents($weekstart, $weekend);

	}

	function listEventsByMonthNew($year, $month, $order)
	{
		$db = & JFactory::getDBO();

		$month = str_pad($month, 2, '0', STR_PAD_LEFT);
		$select_date = $year . '-' . $month . '-01 00:00:00';
		$select_date_fin = $year . '-' . $month . '-' . date('t', JevDate::mktime(0, 0, 0, ($month + 1), 0, $year)) . ' 23:59:59';

		return $this->listEvents($select_date, $select_date_fin, $order);

	}

	function listIcalEventsByMonth($year, $month)
	{
		$startdate = JevDate::mktime(0, 0, 0, $month, 1, $year);
		$enddate = JevDate::mktime(23, 59, 59, $month, date('t', $startdate), $year);
		return $this->listIcalEvents($startdate, $enddate, "");

	}

	function listEventsByYearNEW($year, $limitstart=0, $limit=0)
	{
		if (!$this->legacyEvents)
		{
			return array();
		}

	}

	// Allow the passing of filters directly into this function for use in 3rd party extensions etc.
	function listIcalEventsByYear($year, $limitstart, $limit, $showrepeats = true, $order="", $filters = false, $extrafields="", $extratables="", $count=false)
	{
		list($xyear, $month, $day) = JEVHelper::getYMD();
		$thisyear = new JevDate("+0 seconds");
		list($thisyear, $thismonth, $thisday) = explode("-", $thisyear->toFormat("%Y-%m-%d"));
		if (!$this->cfg->getValue("showyearpast", 1) && $year < $thisyear)
		{
			return array();
		}
		$startdate = ($this->cfg->getValue("showyearpast", 1) || $year > $thisyear) ? JevDate::mktime(0, 0, 0, 1, 1, $year) : JevDate::mktime(0, 0, 0, $thismonth, $thisday, $thisyear);
		$enddate = JevDate::mktime(23, 59, 59, 12, 31, $year);
		if (!$count)
		{
			$order = "rpt.startrepeat asc";
		}

		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$lang = & JFactory::getLanguage();
		$langtag = $lang->getTag();

		if (strpos($startdate, "-") === false)
		{
			$startdate = JevDate::strftime('%Y-%m-%d 00:00:00', $startdate);
			$enddate = JevDate::strftime('%Y-%m-%d 23:59:59', $enddate);
		}

		// process the new plugins
		// get extra data and conditionality from plugins
		$extrawhere = array();
		$extrajoin = array();
		$extrafields = "";  // must have comma prefix
		$extratables = "";  // must have comma prefix
		$needsgroup = false;

		if (!$filters)
		{
			$filters = jevFilterProcessing::getInstance(array("published", "justmine", "category", "search"));
			$filters->setWhereJoin($extrawhere, $extrajoin);
			$needsgroup = $filters->needsGroupBy();

			$dispatcher = & JDispatcher::getInstance();
			$dispatcher->trigger('onListIcalEvents', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin, & $needsgroup));
		}
		else
		{
			$filters->setWhereJoin($extrawhere, $extrajoin);
		}
		
		$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
			$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
			$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
			$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
			$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
			$needsgroup = true;
			$catwhere = "\n WHERE 1 ";
		}
				
		$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
		$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );

		// This version picks the details from the details table
		if ($count)
		{
			$query = "SELECT rpt.rp_id";
		}
		else
		{
			$query = "SELECT ev.*, rpt.*, rr.*, det.*, ev.state as published $extrafields"
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn";
		}
		$query .= "\n FROM #__jevents_repetition as rpt"
				. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
				. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
				. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
				. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
				. $extrajoin
				.$catwhere 
				// New equivalent but simpler test
				. "\n AND rpt.endrepeat >= '$startdate' AND rpt.startrepeat <= '$enddate'"
				/*
				  . "\n AND ((rpt.startrepeat >= '$startdate' AND rpt.startrepeat <= '$enddate')"
				  . "\n OR (rpt.endrepeat >= '$startdate' AND rpt.endrepeat <= '$enddate')"
				  //. "\n OR (rpt.startrepeat >= '$startdate' AND rpt.endrepeat <= '$enddate')"
				  . "\n OR (rpt.startrepeat <= '$startdate' AND rpt.endrepeat >= '$enddate'))"
				 */
				. $extrawhere
				. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. "  AND icsf.state=1 AND icsf.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
		// published state is not handled by filter
		//. "\n AND ev.state=1"
		;
		if (!$showrepeats)
		{
			$query .="\n GROUP BY ev.ev_id";
		}
		else if ($needsgroup)
		{
			$query .="\n GROUP BY rpt.rp_id";
		}

		if ($order != "")
		{
			$query .= " ORDER BY " . $order;
		}
		if ($limit != "" && $limit != 0)
		{
			$query .= " LIMIT " . ($limitstart != "" ? $limitstart . "," : "") . $limit;
		}

		$cache = & JFactory::getCache(JEV_COM_COMPONENT);

		$rows = $cache->call('JEventsDBModel::_cachedlistIcalEvents', $query, $langtag, $count);

		if (!$count)
		{
			$dispatcher = & JDispatcher::getInstance();
			$dispatcher->trigger('onDisplayCustomFieldsMultiRowUncached', array(&$rows));
		}

		return $rows;

	}

	// Allow the passing of filters directly into this function for use in 3rd party extensions etc.
	function listIcalEventsByRange($startdate, $enddate, $limitstart, $limit, $showrepeats = true, $order = "rpt.startrepeat asc, rpt.endrepeat ASC, det.summary ASC", $filters = false, $extrafields="", $extratables="", $count=false)
	{		
		list($year, $month, $day) = explode('-', $startdate);
		list($thisyear, $thismonth, $thisday) = JEVHelper::getYMD();

		//$startdate 	= $this->cfg->getValue("showyearpast",1)?JevDate::mktime( 0, 0, 0, intval($month),intval($day),intval($year) ):JevDate::mktime( 0, 0, 0, $thismonth,$thisday, $thisyear );
		$startdate = JevDate::mktime(0, 0, 0, intval($month), intval($day), intval($year));

		$startdate = JevDate::strftime('%Y-%m-%d', $startdate);

		if (strlen($startdate) == 10)
			$startdate.= " 00:00:00";
		if (strlen($enddate) == 10)
			$enddate.= " 23:59:59";

		// This code is used by the iCals code with a spoofed user so check if this is what is happening
		if (JRequest::getString("jevtask", "") == "icals.export")
		{
			$registry = & JRegistry::getInstance("jevents");
			$user = $registry->getValue("jevents.icaluser", false);
			if (!$user)
				$user = JFactory::getUser();
		}
		else
		{
			$user = JFactory::getUser();
		}
		$db = JFactory::getDBO();
		$lang = & JFactory::getLanguage();
		$langtag = $lang->getTag();

		// process the new plugins
		// get extra data and conditionality from plugins
		$extrawhere = array();
		$extrajoin = array();
		$extrafields = "";  // must have comma prefix
		$needsgroup = false;

		if (!$filters)
		{
			$filters = jevFilterProcessing::getInstance(array("published", "justmine", "category", "search"));
			$filters->setWhereJoin($extrawhere, $extrajoin);
			$needsgroup = $filters->needsGroupBy();

			$dispatcher = & JDispatcher::getInstance();
			$dispatcher->trigger('onListIcalEvents', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin, & $needsgroup));
		}
		else
		{
			$filters->setWhereJoin($extrawhere, $extrajoin);
		}
		
		$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
			$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
			$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
			$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
			$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
			$needsgroup = true;
			$catwhere = "\n WHERE 1 ";
		}
				
		$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
		$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );

		// This version picks the details from the details table
		if ($count)
		{
			$query = "SELECT count(distinct rpt.rp_id)";
		}
		else
		{
			$query = "SELECT ev.*, rpt.*, rr.*, det.*, ev.state as published $extrafields"
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn";
		}
		$query .= "\n FROM #__jevents_repetition as rpt"
				. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
				. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
				. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
				. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = rpt.eventid"
				. $extrajoin
				.$catwhere 
				. "\n AND rpt.endrepeat >= '$startdate' AND rpt.startrepeat <= '$enddate'"

				// Must suppress multiday events that have already started
				. "\n AND NOT (rpt.startrepeat < '$startdate' AND det.multiday=0) "
				. $extrawhere
				. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. "  AND icsf.state=1 AND icsf.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
		;
		if (!$showrepeats && !$count)
		{
			$query .="\n GROUP BY ev.ev_id";
		}
		else if ($needsgroup && !$count)
		{
			$query .="\n GROUP BY rpt.rp_id";
		}

		if ($order != "")
		{
			$query .= " ORDER BY " . $order;
		}
		if ($limit != "" && $limit != 0)
		{
			$query .= " LIMIT " . ($limitstart != "" ? $limitstart . "," : "") . $limit;
		}

		if ($count)
		{
			$db = & JFactory::getDBO();
			$db->setQuery($query);
			$res = $db->loadResult();
			return $res;
		}


		$cache = & JFactory::getCache(JEV_COM_COMPONENT);

		$rows = $cache->call('JEventsDBModel::_cachedlistIcalEvents', $query, $langtag, $count);

		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onDisplayCustomFieldsMultiRowUncached', array(&$rows));

		return $rows;

	}

	function countIcalEventsByYear($year, $showrepeats = true)
	{
		$startdate = JevDate::mktime(0, 0, 0, 1, 1, $year);
		$enddate = JevDate::mktime(23, 59, 59, 12, 31, $year);
		return $this->listIcalEventsByYear($year, "", "", $showrepeats, "", false, "", "", true);

	}

	function countIcalEventsByRange($startdate, $enddate, $showrepeats = true)
	{
		return $this->listIcalEventsByRange($startdate, $enddate, "", "", $showrepeats, "", false, "", "", true);

	}

	function listEventsById($rpid, $includeUnpublished=0, $jevtype="icaldb")
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		$frontendPublish = JEVHelper::isEventPublisher();

		if ($jevtype == "icaldb")
		{
			// process the new plugins
			// get extra data and conditionality from plugins
			$extrafields = "";  // must have comma prefix
			$extratables = "";  // must have comma prefix
			$extrawhere = array();
			$extrajoin = array();
			$dispatcher = & JDispatcher::getInstance();
			$dispatcher->trigger('onListEventsById', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin));
			
			$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
			$params = JComponentHelper::getParams("com_jevents");
			if ($params->get("multicategory",0)){
				$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
				$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
				$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
				$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
				$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
				$needsgroup = true;
				$catwhere = "\n WHERE 1 ";
			}
			
			$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
			$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );

			$query = "SELECT ev.*, ev.state as published, rpt.*, rr.*, det.* $extrafields, ev.created as created "
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
					. "\n FROM (#__jevents_vevent as ev $extratables)"
					. "\n LEFT JOIN #__jevents_repetition as rpt ON rpt.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
					. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
					. $extrajoin
					. $catwhere
					. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
					. "  AND icsf.state=1 AND icsf.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
					. $extrawhere
					. "\n AND rpt.rp_id = '$rpid'";
			$query .="\n GROUP BY rpt.rp_id";
		}
		else
		{
			die("invalid jevtype in listEventsById - more changes needed");
		}

		$db->setQuery($query);
		//echo $db->_sql;
		$rows = $db->loadObjectList();

		// iCal agid uses GUID or UUID as identifier
		if ($rows)
		{
			// set multi-category and check access levels
			// done in the above query
			/*
			if (!$this->setMultiCategory($rows[0],$accessibleCategories)){
				return null;
			}
			 * 
			 */
			if (strtolower($jevtype) == "icaldb")
			{
				$row = new jIcalEventRepeat($rows[0]);
			}
			else if (strtolower($jevtype) == "jevent")
			{
				$row = new jEventCal($rows[0]);
			}
		}
		else
		{
			$row = null;
		}

		return $row;

	}

	/**
	 * Get Event by ID (not repeat Id) result is based on first repeat
	 *
	 * @param event_id $evid
	 * @param boolean $includeUnpublished
	 * @param string $jevtype
	 * @return jeventcal (or desencent)
	 */
	function getEventById($evid, $includeUnpublished=0, $jevtype="icaldb")
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		$frontendPublish = JEVHelper::isEventPublisher();

		if ($jevtype == "icaldb")
		{
			// process the new plugins
			// get extra data and conditionality from plugins
			$extrafields = "";  // must have comma prefix
			$extratables = "";  // must have comma prefix
			$extrawhere = array();
			$extrajoin = array();
			$dispatcher = & JDispatcher::getInstance();
			$dispatcher->trigger('onListEventsById', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin));

			$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
			$params = JComponentHelper::getParams("com_jevents");
			if ($params->get("multicategory",0)){
				$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
				$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
				$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
				$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
				$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
				$needsgroup = true;
				$catwhere = "\n WHERE 1 ";
			}
			
			$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
			$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );
			// make sure we pick up the event state
			
			$query = "SELECT ev.*, rpt.*, rr.*, det.* $extrafields , ev.state as state,  ev.state as published "
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
					. "\n FROM (#__jevents_vevent as ev $extratables)"
					. "\n LEFT JOIN #__jevents_repetition as rpt ON rpt.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
					. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid "
					. $extrajoin
					. $catwhere
					. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
					. ($includeUnpublished ? "": " AND icsf.state=1")
					."\n AND icsf.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
					. $extrawhere
					. "\n AND ev.ev_id = '$evid'"
					. "\n GROUP BY rpt.rp_id"
					. "\n LIMIT 1";
		}
		else
		{
			die("invalid jevtype in listEventsById - more changes needed");
		}

		$db->setQuery($query);
		//echo $db->_sql;
		$rows = $db->loadObjectList();

		// iCal agid uses GUID or UUID as identifier
		if ($rows)
		{
			// set multi-category and check access levels
			// done in the above query
			/*
			if (!$this->setMultiCategory($rows[0],$accessibleCategories)){
				return null;
			}
			 * 
			 */
			
			if (strtolower($jevtype) == "icaldb")
			{
				$row = new jIcalEventRepeat($rows[0]);
			}
			else if (strtolower($jevtype) == "jevent")
			{
				$row = new jEventCal($rows[0]);
			}
		}
		else
		{
			$row = null;
		}

		return $row;

	}

	function listEventsByCreator($creator_id, $limitstart, $limit)
	{
		if (!$this->legacyEvents)
		{
			return array();
		}

	}

	function listIcalEventsByCreator($creator_id, $limitstart, $limit, $orderby='dtstart ASC')
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		$cfg = & JEVConfig::getInstance();

		$rows_per_page = $limit;

		if (empty($limitstart) || !$limitstart)
		{
			$limitstart = 0;
		}

		$limit = "";
		if ($limitstart > 0 || $rows_per_page > 0)
		{
			$limit = "LIMIT $limitstart, $rows_per_page";
		}

		$frontendPublish = JEVHelper::isEventPublisher();

		$adminCats = JEVHelper::categoryAdmin();

		// process the new plugins
		// get extra data and conditionality from plugins
		$extrawhere = array();
		$extrajoin = array();
		$extrafields = "";  // must have comma prefix
		$extratables = "";  // must have comma prefix
		$needsgroup = false;

		$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
			$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
			$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
			$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
			$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
			$needsgroup = true;
			$catwhere = "\n WHERE 1 ";
		}
				
		$where = '';
		if ($creator_id == 'ADMIN')
		{
			$where = "";
		}
		else if ($adminCats && count($adminCats) > 0)
		{
			//$adminCats = " OR (ev.state=0 AND ev.catid IN(".implode(",",$adminCats)."))";
			if ($params->get("multicategory",0)){
				$adminCats = " OR catmap.catid IN(" . implode(",", $adminCats) . ")";
			}
			else {
				$adminCats = " OR ev.catid IN(" . implode(",", $adminCats) . ")";
			}
			$where = " AND ( ev.created_by = " . $user->id . $adminCats . ")";
		}
		else
		{
			$where = " AND ev.created_by = '$creator_id' ";
		}

		$filters = jevFilterProcessing::getInstance(array("published", "justmine", "category", "startdate", "search"));
		$filters->setWhereJoin($extrawhere, $extrajoin);

		$needsgroup = false;
		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onListIcalEvents', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin, & $needsgroup));

		$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
		$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );

		$query = "SELECT ev.*, rr.*, det.*, ev.state as published, count(rpt.rp_id) as rptcount $extrafields"
				. "\n , YEAR(dtstart) as yup, MONTH(dtstart ) as mup, DAYOFMONTH(dtstart ) as dup"
				. "\n , YEAR(dtend  ) as ydn, MONTH(dtend   ) as mdn, DAYOFMONTH(dtend   ) as ddn"
				. "\n , HOUR(dtstart) as hup, MINUTE(dtstart) as minup, SECOND(dtstart   ) as sup"
				. "\n , HOUR(dtend  ) as hdn, MINUTE(dtend  ) as mindn, SECOND(dtend     ) as sdn"
				. "\n FROM #__jevents_vevent as ev"
				. "\n LEFT JOIN #__jevents_repetition as rpt ON rpt.eventid = ev.ev_id"
				. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
				. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = ev.detail_id"
				. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid"
				. $extrajoin
				.$catwhere 
				. $extrawhere
				. $where
				//. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ?  ' IN (' . JEVHelper::getAid($user) . ')'  :  ' <=  ' .JEVHelper::getAid($user))
				. "\n AND icsf.state=1"
				. "\n GROUP BY ev.ev_id"
				. "\n ORDER BY " . ($orderby != "" ? $orderby : "dtstart ASC")
				. "\n $limit";

		$db->setQuery($query);

		//echo $db->explain();
		$icalrows = $db->loadObjectList();
		echo $db->getErrorMsg();
		$icalcount = count($icalrows);
		for ($i = 0; $i < $icalcount; $i++)
		{
			// convert rows to jIcalEvents
			$icalrows[$i] = new jIcalEventDB($icalrows[$i]);
		}
		return $icalrows;

	}

	function listIcalEventRepeatsByCreator($creator_id, $limitstart, $limit, $orderby="rpt.startrepeat")
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		$cfg = & JEVConfig::getInstance();

		$rows_per_page = $limit;

		if (empty($limitstart) || !$limitstart)
		{
			$limitstart = 0;
		}

		$limit = "";
		if ($limitstart > 0 || $rows_per_page > 0)
		{
			$limit = "LIMIT $limitstart, $rows_per_page";
		}

		// process the new plugins
		// get extra data and conditionality from plugins
		$extrawhere = array();
		$extrajoin = array();
		$extrafields = "";  // must have comma prefix
		$extratables = "";  // must have comma prefix
		$needsgroup = false;

		$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
			$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
			$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
			$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
			$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
			$needsgroup = true;
			$catwhere = "\n WHERE 1 ";
		}
				
		$adminCats = JEVHelper::categoryAdmin();
		$where = '';
		if ($creator_id == 'ADMIN')
		{
			$where = "";
		}
		else if ($adminCats && count($adminCats) > 0)
		{
			if ($params->get("multicategory",0)){
				$adminCats = " OR catmap.catid IN(" . implode(",", $adminCats) . ")";	
			}
			else {
				$adminCats = " OR ev.catid IN(" . implode(",", $adminCats) . ")";	
			}
			$where = " AND ( ev.created_by = " . $user->id . $adminCats . ")";
		}
		else
		{
			$where = " AND ev.created_by = '$creator_id' ";
		}

		$frontendPublish = JEVHelper::isEventPublisher();

		$filters = jevFilterProcessing::getInstance(array("published", "justmine", "category", "startdate", "search"));
		$filters->setWhereJoin($extrawhere, $extrajoin);

		$needsgroup = false;
		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onListIcalEvents', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin, & $needsgroup));

		$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
		$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );

		$needsgroup = false;
		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onListIcalEvents', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin, & $needsgroup));

		if ($frontendPublish)
		{
			// TODO fine a single query way of doing this !!!
			$query = "SELECT rp_id"
					. "\n FROM #__jevents_repetition as rpt "
					. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid"
					. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
					. $extrajoin
					.$catwhere 
					. $extrawhere
					. $where
					. "\n  AND icsf.state=1"
					//. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ?  ' IN (' . JEVHelper::getAid($user) . ')'  :  ' <=  ' .JEVHelper::getAid($user))
					. "\n GROUP BY rpt.rp_id"
					. "\n ORDER BY " . ($orderby != "" ? $orderby : "rpt.startrepeat ASC")
					. "\n $limit";
			;

			$db->setQuery($query);
			$rplist = $db->loadResultArray();
			//echo $db->explain();

			$rplist = implode(',', array_merge(array(-1), $rplist));

			$query = "SELECT ev.*, rpt.*, rr.*, det.*, ev.state as published"
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
					. "\n FROM #__jevents_vevent as ev "
					. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid"
					. "\n LEFT JOIN #__jevents_repetition as rpt ON rpt.eventid = ev.ev_id"
					. "\n AND rpt.eventid = ev.ev_id"
					. "\n AND rpt.rp_id IN($rplist)"
					. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
					. $extrajoin
					.$catwhere 
					. $extrawhere
					. $where
					. "\n  AND icsf.state=1"
					//. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ?  ' IN (' . JEVHelper::getAid($user) . ')'  :  ' <=  ' .JEVHelper::getAid($user))
					. "\n GROUP BY rpt.rp_id"
					. "\n ORDER BY " . ($orderby != "" ? $orderby : "rpt.startrepeat ASC")
			;
		}
		else
		{
			// TODO fine a single query way of doing this !!!
			$query = "SELECT rp_id"
					. "\n FROM #__jevents_vevent as ev "
					. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid"
					. "\n LEFT JOIN #__jevents_repetition as rpt ON rpt.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
					. $extrajoin
					.$catwhere 
					. $extrawhere
					. "\n AND icsf.state=1"
					. $where
					//. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ?  ' IN (' . JEVHelper::getAid($user) . ')'  :  ' <=  ' .JEVHelper::getAid($user))
					. "\n GROUP BY rpt.rp_id"
					. "\n ORDER BY " . ($orderby != "" ? $orderby : "rpt.startrepeat ASC")
					. "\n $limit";
			;

			$db->setQuery($query);
			$rplist = $db->loadResultArray();

			$rplist = implode(',', array_merge(array(-1), $rplist));

			$query = "SELECT ev.*, rpt.*, rr.*, det.*, ev.state as published"
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
					. "\n FROM #__jevents_vevent as ev "
					. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid"
					. "\n LEFT JOIN #__jevents_repetition as rpt ON rpt.eventid = ev.ev_id"
					. "\n AND rpt.rp_id IN($rplist)"
					. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
					. $extrajoin
					.$catwhere 
					. $where
					//. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ?  ' IN (' . JEVHelper::getAid($user) . ')'  :  ' <=  ' .JEVHelper::getAid($user))
					. "\n AND icsf.state=1"
					. $extrawhere
					. "\n GROUP BY rpt.rp_id"
					. "\n ORDER BY " . ($orderby != "" ? $orderby : "rpt.startrepeat ASC")
			;
		}
		$db->setQuery($query);
		$icalrows = $db->loadObjectList();
		$icalcount = count($icalrows);
		for ($i = 0; $i < $icalcount; $i++)
		{
			// convert rows to jIcalEvents
			$icalrows[$i] = new jIcalEventRepeat($icalrows[$i]);
		}
		return $icalrows;

	}

	function countEventsByCreator($creator_id)
	{
		if (!$this->legacyEvents)
		{
			return 0;
		}

	}

	function countIcalEventsByCreator($creator_id)
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		$extrawhere = array();
		$extrajoin = array();
		$extrafields = "";
			
		$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
			$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
			$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
			$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
			$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
			$needsgroup = true;
			$catwhere = "\n WHERE 1 ";
		}
				
		
		$adminCats = JEVHelper::categoryAdmin();
		$where = '';
		if ($creator_id == 'ADMIN')
		{
			$where = "";
		}
		else if ($adminCats && count($adminCats) > 0)
		{
			if ($params->get("multicategory",0)){
				$adminCats = " OR catmap.catid IN(" . implode(",", $adminCats) . ")";
			}
			else {
				$adminCats = " OR ev.catid IN(" . implode(",", $adminCats) . ")";
			}				
			$where = " AND ( ev.created_by = " . $user->id . $adminCats . ")";
		}
		else
		{
			$where = " AND ev.created_by = '$creator_id' ";
		}

		// State is managed by plugin
		/*
		  $frontendPublish = JEVHelper::isEventPublisher();
		  $state = "\n AND ev.state=1";
		  if ($frontendPublish){
		  $state = "";
		  }
		 */

		$filters = jevFilterProcessing::getInstance(array("published", "justmine", "category", "startdate", "search"));
		$filters->setWhereJoin($extrawhere, $extrajoin);

		$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
		$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );

		$query = "SELECT MIN(rpt.rp_id) as rp_id"
				. "\n FROM #__jevents_vevent as ev "
				. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid"
				. "\n LEFT JOIN #__jevents_repetition as rpt ON rpt.eventid = ev.ev_id"
				. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
				. $extrajoin
				.$catwhere 
				. $extrawhere
				. $where
				. "\n AND icsf.state=1"
				. "\n GROUP BY ev.ev_id";

		$db->setQuery($query);
		$db->query();
		return $db->getNumRows();

	}

	function countIcalEventRepeatsByCreator($creator_id)
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		$extrawhere = array();
		$extrajoin = array();
		$extrafields = "";  // must have comma prefix		
		
		$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
			$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
			$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
			$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
			$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
			$needsgroup = true;
			$catwhere = "\n WHERE 1 ";
		}
				
		
		$adminCats = JEVHelper::categoryAdmin();
		$where = '';
		if ($creator_id == 'ADMIN')
		{
			$where = "";
		}
		else if ($adminCats && count($adminCats) > 0)
		{
			if ($params->get("multicategory",0)){
				$adminCats = " OR catmap.catid IN(" . implode(",", $adminCats) . ")";
			}
			else {
				$adminCats = " OR ev.catid IN(" . implode(",", $adminCats) . ")";
			}
			$where = " AND ( ev.created_by = " . $user->id . $adminCats . ")";
		}
		else
		{
			$where = " AND ev.created_by = '$creator_id' ";
		}

		// State is managed by plugin

		$filters = jevFilterProcessing::getInstance(array("published", "justmine", "category", "startdate", "search"));
		$filters->setWhereJoin($extrawhere, $extrajoin);
		
		$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
		$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );

		$query = "SELECT rpt.rp_id, ev.catid"
				. "\n FROM #__jevents_repetition as rpt "
				. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
				. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid"
				. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
				. $extrajoin
				.$catwhere 
				. $extrawhere
				. $where
				. "\n AND icsf.state=1"
				. "\n GROUP BY rpt.rp_id"
		;

		$db->setQuery($query);
		$db->query();
		return $db->getNumRows();

	}

	function listEventsByCat($catids, $limitstart, $limit)
	{
		if (!$this->legacyEvents)
		{
			return array();
		}

	}

	// Allow the passing of filters directly into this function for use in 3rd party extensions etc.
	function listIcalEventsByCat($catids, $showrepeats = false, $total=0, $limitstart=0, $limit=0, $order = "rpt.startrepeat asc, rpt.endrepeat ASC, det.summary ASC", $filters = false, $extrafields="", $extratables="")
	{
		$db = & JFactory::getDBO();
		$user = JFactory::getUser();

		// Use catid in accessibleCategoryList to pick up offsping too!
		$aid = null;
		$catidlist = implode(",", $catids);

		// process the new plugins
		// get extra data and conditionality from plugins
		$extrafields = "";  // must have comma prefix
		$extratables = "";  // must have comma prefix
		$extrawhere = array();
		$extrajoin = array();
		$needsgroup = false;

		if (!$this->cfg->getValue("showyearpast", 1))
		{
			list($year, $month, $day) = JEVHelper::getYMD();
			$startdate = JevDate::mktime(0, 0, 0, $month, $day, $year);
			$today = JevDate::strtotime("+0 days");
			if ($startdate < $today)
				$startdate = $today;
			$startdate = JevDate::strftime('%Y-%m-%d 00:00:00', $startdate);
			$extrawhere[] = "rpt.endrepeat >=  '$startdate'";
		}

		if (!$filters)
		{
			$filters = jevFilterProcessing::getInstance(array("published", "justmine", "category", "search"));
			$filters->setWhereJoin($extrawhere, $extrajoin);
			$needsgroup = $filters->needsGroupBy();

			$dispatcher = & JDispatcher::getInstance();
			$dispatcher->trigger('onListIcalEvents', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin, & $needsgroup));
		}
		else
		{
			$filters->setWhereJoin($extrawhere, $extrajoin);
		}
		
		$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
			$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
			$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
			$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
			$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
			$needsgroup = true;
			$catwhere = "\n WHERE 1 ";
		}
		
		$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
		$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );

		if ($limit > 0 || $limitstart > 0)
		{
			if (empty($limitstart) || !$limitstart)
			{
				$limitstart = 0;
			}

			$rows_per_page = $limit;
			$limit = " LIMIT $limitstart, $rows_per_page";
		}
		else
		{
			$limit = "";
		}

		if ($order != "")
		{
			$order = (strpos($order, 'ORDER BY') === false ? " ORDER BY " : " ") . $order;
		}

		$user = JFactory::getUser();
		if ($showrepeats)
		{
			$query = "SELECT ev.*, rpt.*, rr.*, det.* $extrafields"
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
					. "\n FROM #__jevents_vevent as ev"
					. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid"
					. "\n LEFT JOIN #__jevents_repetition as rpt ON rpt.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
					. $extrajoin
					//. "\n WHERE ev.catid IN(".$this->accessibleCategoryList($aid,$catids,$catidlist).")"
					.$catwhere 
					. $extrawhere
					//. "\n AND ev.state=1"
					. "\n  AND icsf.state=1"
					. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
					. "\n GROUP BY rpt.rp_id"
					. $order
					. $limit;
		}
		else
		{
			// TODO find a single query way of doing this !!!
			$query = "SELECT MIN(rpt.rp_id) as rp_id FROM #__jevents_repetition as rpt "
					. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
					. "\n LEFT JOIN #__jevents_icsfile as icsf  ON icsf.ics_id=ev.icsid"
					. $extrajoin
					.$catwhere 
					. $extrawhere
					. "\n  AND icsf.state=1"
					. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
					. "\n GROUP BY ev.ev_id"
			;

			$db->setQuery($query);
			//echo $db->explain();

			$rplist = $db->loadResultArray();

			$rplist = implode(',', array_merge(array(-1), $rplist));

			$query = "SELECT rpt.rp_id,ev.*, rpt.*, rr.*, det.* $extrafields"
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
					. "\n FROM #__jevents_repetition as rpt  "
					. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid"
					. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
					. $extrajoin
					//. "\n WHERE ev.catid IN(".$this->accessibleCategoryList($aid,$catids,$catidlist).")"
					.$catwhere 
					. $extrawhere
					//. "\n AND ev.state=1"
					. "\n AND rpt.rp_id IN($rplist)"
					. "\n  AND icsf.state=1"
					. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
					. ($needsgroup ? "\n GROUP BY rpt.rp_id" : "")
					. $order
					. $limit;
		}

		$cache = & JFactory::getCache(JEV_COM_COMPONENT);
		$lang = & JFactory::getLanguage();
		$langtag = $lang->getTag();

		$rows = $cache->call('JEventsDBModel::_cachedlistIcalEvents', $query, $langtag);

		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onDisplayCustomFieldsMultiRowUncached', array(&$rows));

		return $rows;

	}

	function countEventsByCat($catid)
	{
		return 0;

	}

	function countIcalEventsByCat($catids, $showrepeats = false)
	{
		$db = & JFactory::getDBO();
		$user = JFactory::getUser();
		
		// Use catid in accessibleCategoryList to pick up offsping too!
		$aid = null;
		$catidlist = implode(",", $catids);

		// process the new plugins
		// get extra data and conditionality from plugins
		$extrafields = "";  // must have comma prefix
		$extratables = "";  // must have comma prefix
		$extrawhere = array();
		$extrajoin = array();
		$needsgroup = false;

		if (!$this->cfg->getValue("showyearpast", 1))
		{
			list($year, $month, $day) = JEVHelper::getYMD();
			$startdate = JevDate::mktime(0, 0, 0, $month, $day, $year);
			$startdate = JevDate::strftime('%Y-%m-%d 00:00:00', $startdate);
			$extrawhere[] = "rpt.endrepeat >=  '$startdate'";
		}

		$filters = jevFilterProcessing::getInstance(array("published", "justmine", "category", "search"));
		$filters->setWhereJoin($extrawhere, $extrajoin);
		$needsgroup = $filters->needsGroupBy();

		$extrafields = "";  // must have comma prefix
		$extratables = "";  // must have comma prefix

		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onListIcalEvents', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin, & $needsgroup));

		$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
			$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
			$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
			$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
			$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
			$needsgroup = true;
			$catwhere = "\n WHERE 1 ";
		}
				
		$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
		$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );

		// Get the count
		if ($showrepeats)
		{
			$query = "SELECT count(DISTINCT rpt.rp_id) as cnt"
					. "\n FROM #__jevents_vevent as ev "
					. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid"
					. "\n LEFT JOIN #__jevents_repetition as rpt ON rpt.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
					. $extrajoin
					//. "\n WHERE ev.catid IN(".$this->accessibleCategoryList($aid,$catids,$catidlist).")"
					.$catwhere 
					. "\n AND icsf.state=1"
					. $extrawhere
			//. "\n AND ev.state=1"
			;
		}
		else
		{
			// TODO fine a single query way of doing this !!!
			$query = "SELECT MIN(rpt.rp_id) as rp_id FROM #__jevents_repetition as rpt "
					. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
					. "\n LEFT JOIN #__jevents_icsfile as icsf  ON icsf.ics_id=ev.icsid "
					. $extrajoin
					//. "\n WHERE ev.catid IN(".$this->accessibleCategoryList($aid,$catids,$catidlist).")"
					.$catwhere 
					. $extrawhere
					//. "\n AND ev.state=1"
					. "\n AND icsf.state=1"
					. "\n GROUP BY ev.ev_id";

			$db->setQuery($query);

			$rplist = $db->loadResultArray();

			$rplist = implode(',', array_merge(array(-1), $rplist));

			$query = "SELECT count(DISTINCT det.evdet_id) as cnt"
					. "\n FROM #__jevents_vevent as ev "
					. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid"
					. "\n LEFT JOIN #__jevents_repetition as rpt ON rpt.eventid = ev.ev_id"
					. "\n AND rpt.rp_id IN($rplist)"
					. "\n LEFT JOIN #__jevents_rrule as rr ON rr.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
					. $extrajoin
					//. "\n WHERE ev.catid IN(".$this->accessibleCategoryList($aid,$catids,$catidlist).")"
					.$catwhere 
					. "\n AND icsf.state=1"
					. $extrawhere
			//. "\n AND ev.state=1"
			//. ($needsgroup?"\n GROUP BY rpt.rp_id":"")
			;
		}

		$db->setQuery($query);
		//echo $db->_sql;
		//echo $db->explain();
		$total = intval($db->loadResult());
		return $total;

	}

	function listEventsByKeyword($keyword, $order, &$limit, &$limitstart, &$total, $useRegX=false)
	{
		$user = JFactory::getUser();
		$adminuser = JEVHelper::isAdminUser($user);
		$db = JFactory::getDBO();

		$rows_per_page = $limit;
		if (empty($limitstart) || !$limitstart)
		{
			$limitstart = 0;
		}

		$limitstring = "";
		if ($rows_per_page > 0)
		{
			$limitstring = "LIMIT $limitstart, $rows_per_page";
		}

		$where = "";
		$having = "";
		if (!JRequest::getInt('showpast', 0))
		{
			$datenow = & JevDate::getDate("-12 hours");
			$having = " AND rpt.endrepeat>'" . $datenow->toMysql() . "'";
		}

		if (!$order)
		{
			$order = 'publish_up';
		}

		$order = preg_replace("/[\t ]+/", '', $order);
		$orders = explode(",", $order);

		// this function adds #__events. to the beginning of each ordering field
		function app_db($strng)
		{
			return '#__events.' . $strng;

		}

		$order = implode(',', array_map('app_db', $orders));

		$total = 0;

		// process the new plugins
		// get extra data and conditionality from plugins
		$extrawhere = array();
		$extrajoin = array();
		$extrafields = "";  // must have comma prefix		
		$needsgroup = false;

		$filterarray = array("published");
		// If there are extra filters from the module then apply them now
		$reg = & JFactory::getConfig();
		$modparams = $reg->getValue("jev.modparams", false);
		if ($modparams && $modparams->getValue("extrafilters", false))
		{
			$filterarray = array_merge($filterarray, explode(",", $modparams->getValue("extrafilters", false)));
		}

		$filters = jevFilterProcessing::getInstance($filterarray);
		$filters->setWhereJoin($extrawhere, $extrajoin);
		$needsgroup = $filters->needsGroupBy();

		JPluginHelper::importPlugin('jevents');
		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onListIcalEvents', array(& $extrafields, & $extratables, & $extrawhere, & $extrajoin, & $needsgroup));
		
		$catwhere = "\n WHERE ev.catid IN(" . $this->accessibleCategoryList() . ")";
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$extrajoin[] = "\n #__jevents_catmap as catmap ON catmap.evid = rpt.eventid";
			$extrajoin[] = "\n #__categories AS catmapcat ON catmap.catid = catmapcat.id";
			$extrafields .= ", GROUP_CONCAT(DISTINCT catmap.catid SEPARATOR ',') as catids";
			$extrawhere[]= " catmapcat.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user));
			$extrawhere[]= " catmap.catid IN(" . $this->accessibleCategoryList() . ")";
			$needsgroup = true;
			$catwhere = "\n WHERE 1 ";
		}
				
		$extrajoin = ( count($extrajoin) ? " \n LEFT JOIN " . implode(" \n LEFT JOIN ", $extrajoin) : '' );
		$extrawhere = ( count($extrawhere) ? ' AND ' . implode(' AND ', $extrawhere) : '' );

		$extrasearchfields = array();
		$dispatcher->trigger('onSearchEvents', array(& $extrasearchfields, & $extrajoin, & $needsgroup));

		if (count($extrasearchfields) > 0)
		{
			$extraor = implode(" OR ", $extrasearchfields);
			$extraor = " OR " . $extraor;
			// replace the ### placeholder with the keyword
			$extraor = str_replace("###", $keyword, $extraor);

			$searchpart = ( $useRegX ) ? "(det.summary RLIKE '$keyword' OR det.description RLIKE '$keyword' OR det.extra_info RLIKE '$keyword' $extraor)\n" :
					" (MATCH (det.summary, det.description, det.extra_info) AGAINST ('$keyword' IN BOOLEAN MODE) $extraor)\n";
		}
		else
		{
			$searchpart = ( $useRegX ) ? "(det.summary RLIKE '$keyword' OR det.description RLIKE '$keyword'  OR det.extra_info RLIKE '$keyword')\n" :
					"MATCH (det.summary, det.description, det.extra_info) AGAINST ('$keyword' IN BOOLEAN MODE)\n";
		}

		// Now Search Icals
		$query = "SELECT count( distinct det.evdet_id) FROM #__jevents_vevent as ev"
				. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid"
				. "\n LEFT JOIN #__jevents_repetition as rpt ON rpt.eventid = ev.ev_id"
				. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
				. $extrajoin
				.$catwhere 
				. "\n AND icsf.state=1 AND icsf.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. "\n AND ";
		$query .= $searchpart;
		$query .= $extrawhere;
		$query .= $having;
		$db->setQuery($query);
		//echo $db->explain();
		$total += intval($db->loadResult());

		if ($total < $limitstart)
		{
			$limitstart = 0;
		}

		$rows = array();
		if ($total == 0)
			return $rows;

		// Now Search Icals
		// New version
		$query = "SELECT DISTINCT det.evdet_id FROM  #__jevents_vevdetail as det"
				. "\n LEFT JOIN #__jevents_repetition as rpt ON rpt.eventdetail_id = det.evdet_id"
				. "\n LEFT JOIN #__jevents_vevent as ev ON ev.ev_id = rpt.eventid"
				. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid"
				. $extrajoin
				.$catwhere 
				. "\n  AND icsf.state=1 AND icsf.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
				. "\n AND ev.access " . (version_compare(JVERSION, '1.6.0', '>=') ? ' IN (' . JEVHelper::getAid($user) . ')' : ' <=  ' . JEVHelper::getAid($user))
		;
		$query .= " AND ";
		$query .= $searchpart;
		$query .= $extrawhere;
		$query .= $having;
		$query .= "\n ORDER BY rpt.startrepeat ASC ";
		$query .= "\n $limitstring";

		$db->setQuery($query);
		if ($adminuser)
		{
			//echo $db->_sql;
			//echo $db->explain();
		}
		//echo $db->explain();
		$details = $db->loadResultArray();

		$icalrows = array();
		foreach ($details as $detid)
		{
			$query2 = "SELECT ev.*, rpt.*, det.* $extrafields"
					. "\n , YEAR(rpt.startrepeat) as yup, MONTH(rpt.startrepeat ) as mup, DAYOFMONTH(rpt.startrepeat ) as dup"
					. "\n , YEAR(rpt.endrepeat  ) as ydn, MONTH(rpt.endrepeat   ) as mdn, DAYOFMONTH(rpt.endrepeat   ) as ddn"
					. "\n , HOUR(rpt.startrepeat) as hup, MINUTE(rpt.startrepeat ) as minup, SECOND(rpt.startrepeat ) as sup"
					. "\n , HOUR(rpt.endrepeat  ) as hdn, MINUTE(rpt.endrepeat   ) as mindn, SECOND(rpt.endrepeat   ) as sdn"
					. "\n FROM #__jevents_vevent as ev"
					. "\n LEFT JOIN #__jevents_repetition as rpt ON rpt.eventid = ev.ev_id"
					. "\n LEFT JOIN #__jevents_vevdetail as det ON det.evdet_id = rpt.eventdetail_id"
					. "\n LEFT JOIN #__jevents_icsfile as icsf ON icsf.ics_id=ev.icsid"
					. $extrajoin
					. "\n WHERE rpt.eventdetail_id = $detid"
					. $extrawhere
					. $having
					. "\n ORDER BY rpt.startrepeat ASC limit 1";
			$db->setQuery($query2);
			//echo $db->explain();
			$data = $db->loadObject();
			// belts and braces - some servers have a MYSQLK bug on the  user of DISTINCT!
			if (!$data->ev_id)
				continue;
			$icalrows[] = $data;
		}

		$num_events = count($icalrows);

		for ($i = 0; $i < $num_events; $i++)
		{
			// convert rows to jevents
			$icalrows[$i] = new jIcalEventRepeat($icalrows[$i]);
		}
		
		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onDisplayCustomFieldsMultiRow', array(&$icalrows));
		$dispatcher->trigger('onDisplayCustomFieldsMultiRowUncached', array(&$icalrows));
		
		return $icalrows;

	}

	function sortEvents($a, $b)
	{

		list( $adate, $atime ) = explode(' ', $a->publish_up);
		list( $bdate, $btime ) = explode(' ', $b->publish_up);
		return strcmp($atime, $btime);

	}

	function sortJointEvents($a, $b)
	{
		$adatetime = $a->getUnixStartTime();
		$bdatetime = $b->getUnixStartTime();
		if ($adatetime == $bdatetime)
			return 0;
		return ($adatetime > $bdatetime) ? -1 : 1;

	}

	function findMatchingRepeat($uid, $year, $month, $day)
	{
		$start = $year . '/' . $month . '/' . $day . ' 00:00:00';
		$end = $year . '/' . $month . '/' . $day . ' 23:59:59';

		$db = & JFactory::getDBO();
		$query = "SELECT ev.*, rpt.* "
				. "\n FROM #__jevents_vevent as ev"
				. "\n LEFT JOIN #__jevents_repetition as rpt ON rpt.eventid = ev.ev_id"
				. "\n WHERE ev.uid = " . $db->Quote($uid)
				. "\n AND rpt.startrepeat>=" . $db->Quote($start) . " AND rpt.startrepeat<=" . $db->Quote($end)
		;

		$db->setQuery($query);
		//echo $db->_sql;
		$rows = $db->loadObjectList();
		if (count($rows) > 0)
		{
			return $rows[0]->rp_id;
		}

		// still no match so find the nearest repeat and give a message.
		$db = & JFactory::getDBO();
		$query = "SELECT ev.*, rpt.*, abs(datediff(rpt.startrepeat," . $db->Quote($start) . ")) as diff "
				. "\n FROM #__jevents_repetition as rpt"
				. "\n LEFT JOIN #__jevents_vevent as ev ON rpt.eventid = ev.ev_id"
				. "\n WHERE ev.uid = " . $db->Quote($uid)
				. "\n ORDER BY diff asc LIMIT 3"
		;

		$db->setQuery($query);
		//echo $db->_sql;
		$rows = $db->loadObjectList();
		if (count($rows) > 0)
		{
			JError::raiseNotice(1, JText::_("This event has changed - this is occurance is now the closest to the date you searched for"));
			return $rows[0]->rp_id;
		}

	}

	function setMultiCategory(&$row,$accessibleCategories){
		// check multi-category access
		// do not use jev_com_component incase we call this from locations etc.
		$params = JComponentHelper::getParams("com_jevents");
		if ($params->get("multicategory",0)){
			$db = & JFactory::getDBO();
			// get list of categories this event is in - are they all accessible?
			$db->setQuery("SELECT catid FROM #__jevents_catmap WHERE evid=".$row->ev_id);
			$catids = $db->loadResultArray();
			// backward compatbile
			if(!$catids){
				return true;
			}

			// are there any catids not in list of accessible Categories 
			$inaccessiblecats = array_diff($catids, explode(",",$accessibleCategories));
			if (count($inaccessiblecats )){
				return null;
			}
			$row->catids = $catids;
		}
		return true;
	}
}
