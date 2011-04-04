<?php
/**
 * See horde/config/prefs.php for documentation on the structure of this file.
 *
 * IMPORTANT: Local overrides MUST be placed in pref.local.php, or
 * prefs-servername.php if the 'vhosts' setting has been enabled in Horde's
 * configuration.
 */

$prefGroups['display'] = array(
    'column' => _("General Preferences"),
    'label' => _("Display Preferences"),
    'desc' => _("Set default display parameters."),
    'members' => array('rowsperpage', 'resultlimit', 'columns')
);

$_prefs['rowsperpage'] = array(
    'value' => 100,
    'locked' => false,
    'type' => 'number',
    'desc' => _("Maximum number of call records to show per search result page.")
);

$_prefs['columns'] = array(
    'value' => 'a:6:{i:0;s:11:"accountcode";i:1;s:4:"clid";i:2;s:3:"dst";i:3;s:5:"start";i:4;s:8:"duration";i:5;s:11:"disposition";}',
    'locked' => false,
    'type' => 'multienum',
    'enum' => Operator::getColumns(),
    'desc' => _("The columns to be displayed on the Call Detail Review screen")
);
