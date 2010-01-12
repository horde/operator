<?php
/**
 * $Horde: incubator/operator/viewgraph.php,v 1.11 2009/06/10 17:33:30 slusarz Exp $
 *
 * Copyright 2008-2010 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author Ben Klang <ben@alkaloid.net>
 */

require_once dirname(__FILE__) . '/lib/Application.php';

$operator = new Operator_Application(array('init' => true));
$cache = &$GLOBALS['cache'];

require_once OPERATOR_BASE . '/lib/Form/SearchCDR.php';

$renderer = new Horde_Form_Renderer();
$vars = Horde_Variables::getDefaultVariables();

$form = new SearchCDRForm(_("Graph CDR Data"), $vars);
if ($form->isSubmitted() && $form->validate($vars, true)) {
    $accountcode = $vars->get('accountcode');
    $dcontext = $vars->get('dcontext');
    if (empty($dcontext)) {
        $dcontext = '%';
    }

    try {
        $start = new Horde_Date($vars->get('startdate'));
        $end = new Horde_Date($vars->get('enddate'));
    
        if (($end->month - $start->month) == 0 &&
            ($end->year - $start->year) == 0) {
            // FIXME: This should not cause an error but is due to a bug in
            // Image_Graph.
            $notification->push(_("You must select a range that includes more than one month to view these graphs."));
        } else {
            // See if we have cached data
            $cachekey = md5(serialize(array('getMonthlyCallStats', $start, $end,
                                            $accountcode, $dcontext)));
            // Use 0 lifetime to allow cache lifetime to be set when storing
            // the object.
            $stats = $cache->get($cachekey, 0);
            if ($stats === false) {
                $stats = $operator->driver->getMonthlyCallStats($start,
                                                               $end,
                                                               $accountcode,
                                                               $dcontext);

                $res = $cache->set($cachekey, serialize($stats), 600);
                if ($res === false) {
                    Horde::logMessage('The cache system has experienced an error.  Unable to continue.', __FILE__, __LINE__, PEAR_LOG_ERR);
                    $notification->push(_("Internal error.  Details have been logged for the administrator."));
                    $stats = array();
                }

            } else {
                // Cached data is stored serialized
                $stats = unserialize($stats);
            }
            $_SESSION['operator']['lastsearch']['params'] = array(
                'accountcode' => $vars->get('accountcode'),
                'dcontext' => $vars->get('dcontext'),
                'startdate' => $vars->get('startdate'),
                'enddate' => $vars->get('enddate'));
        }
    } catch (Horde_Exception $e) {
        //$notification->push(_("Invalid dates requested."));
        $notification->push($e);
        $stats = array();
    }
} else {
    if (isset($_SESSION['operator']['lastsearch']['params'])) {
        foreach($_SESSION['operator']['lastsearch']['params'] as $var => $val) {
            $vars->set($var, $val);
        }
    }
    if (isset($_SESSION['operator']['lastsearch']['data'])) {
        $data = $_SESSION['operator']['lastsearch']['data'];
    }
}

if (!empty($stats)) {
    $numcalls_graph = $minutes_graph = $failed_graph =
                      Horde::applicationUrl('graphgen.php');
    
    $numcalls_graph = Horde_Util::addParameter($numcalls_graph, array(
        'graph' => 'numcalls', 'key' => $cachekey));
    $minutes_graph = Horde_Util::addParameter($minutes_graph, array(
        'graph' => 'minutes', 'key' => $cachekey));
    $failed_graph = Horde_Util::addParameter($failed_graph, array(
        'graph' => 'failed', 'key' => $cachekey));
}

$title = _("Call Detail Records Graph");

require OPERATOR_TEMPLATES . '/common-header.inc';
require OPERATOR_TEMPLATES . '/menu.inc';

$form->renderActive($renderer, $vars);

if (!empty($stats)) {
    echo '<br />';
    echo '<img src="' . $numcalls_graph . '"/><br />';
    echo '<img src="' . $minutes_graph . '"/><br />';
    echo '<img src="' . $failed_graph . '"/><br />';
}

require $registry->get('templates', 'horde') . '/common-footer.inc';

// Don't leave stale stats lying about
unset($_SESSION['operator']['stats']);
