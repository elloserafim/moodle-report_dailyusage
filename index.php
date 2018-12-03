<?php

require_once('../../config.php');
require_once("../../course/lib.php");

$id = optional_param('id', $SITE->id, PARAM_INT);
$ts = optional_param('ts', 0, PARAM_INT);

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_login($course);

$context = context_course::instance($course->id);

require_capability('report/loglive:view', $context);

$title = 'Daily usage';
$url = new moodle_url('/report/dailyusage/index.php', array('ts'=>$ts));

$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->navbar->add('Daily usage', new moodle_url('index.php'));

echo $OUTPUT->header();
	
?>
<style type="text/css">
/* Activity Stats */
table.day_activity {
	text-transform:capitalize;
	border:1px solid #CCC;
	width:100%;
}
table.day_activity td {
	border:1px solid #CCC;
}
table.day_activity h4 {
	font-size:1.2em;
}
#activity h1 {
	font-size:1.6em;
	text-transform:capitalize;
	margin-bottom:10px;
}
#activity h2 {
	font-size:1.45em;
	color:#000;
	text-transform:capitalize;
}
#activity h3 {
	font-size:2.2em; 
	color:#000; 
	font-weight:bold;
	float:left;
}
#activity h4 {
	font-size:1.3em; 
	text-transform:capitalize;
	margin:0;
	padding:0;
	margin-top:10px;
	margin-bottom:2px;
	color:#000;
}
#activity p {
	margin-bottom:2px;
}
#activity a.view_usage {
	display:block;
	margin-left:87px;
	padding-top:9px;
	padding-bottom:2px;
}
table.day_activity {
	display:none;
	width:100%;
}
table.activity_stats {
	margin:0;
	padding:0;
}
table.activity_stats td {
	border:none;
}
div.line {
	display:block;
	height:9px;
	background-color:#464646;
}
</style>
<div id="content">
    <table id="layout-table" summary="layout">
        <tbody>
            <tr>
                <td id="left-column" summary="layout" valign="top">
                    <div id="stats_menu">
                        <h3><?= get_string('daily','report_daily') ?></h3>
                        <ul>
							<li><span style="color:#AAA;"><?= get_string('stat','report_daily') ?></span></li>
                        </ul>

                        <h3><?= get_string('courses','report_daily') ?></h3>
                        <ul>
                            <li><a href="reports.php"><?= get_string('lastupdatedcourses','report_daily') ?></a></li>
                        </ul>
                    </div>
                </td>
                <td id="middle-column">
<?php

				//PT-BR

				setlocale(LC_ALL, NULL);
				setlocale(LC_ALL, 'pt_BR.utf-8');  
				

				// Check stats from 0am to 23pm
				$start_hour = 0;
				$end_hour = 23;
				$timestamp = mktime($start_hour, 0, 0, date('n'), (date('j')), date('Y'));
				
				// Make dropdown from today plus past six days
				$selectbox = '<select name="ts" onchange="this.form.submit()">';
				for ($i=7; $i>=1; $i--) {
					$ts_today = strtotime("-$i days", $timestamp);
					$friendly_date = strftime('%A - %d/%m/%Y', $ts_today);
					$selected = (isset($ts) && $ts == $ts_today) ? ' selected="selected"' : '';
					$selectbox .= '<option value="'.$ts_today.'"'.$selected.'>'.$friendly_date.'</option>';
				}
				$selected = (isset($ts) && ($ts == $timestamp || $ts == 0)) ? ' selected="selected"' : '';
				$selectbox .= '<option value="'.$timestamp.'"'.$selected.'>'. get_string('today', 'report_daily').'</option>';
				$selectbox .= '</select>';
				
				// Use GET param if set: otherwise use current day
				if ($ts != 0) {
					$timestamp = $ts;
				}
				
				echo '<div id="activity">';
				
				echo '<h1>'.get_string('pluginname', 'report_daily').'</h1>';
				
				// Get approx. number of students currently online
				echo '<h4>' . get_string('onlineusers', 'report_daily') . '</h4>';
				$now = time();
				$five_mins_ago = strtotime('-5 minutes', $now);
				
				$query = sprintf("SELECT COUNT(DISTINCT userid) FROM ".$CFG->prefix."logstore_standard_log WHERE timecreated > %d", $five_mins_ago);
				$online_users = number_format($DB->count_records_sql($query));
				
				echo "<p><img src=\"./images/go.gif\" alt=\"Online\" width=\"11\" height=\"11\" />&nbsp; <b>$online_users ". get_string('usersonlinenow', 'report_daily')  ."</p>";
				
				$query = "SELECT COUNT(id) FROM ".$CFG->prefix."user WHERE auth != 'nologin'";
				$active = number_format($DB->count_records_sql($query));
				echo "<p>$active ". get_string('activeusers', 'report_daily') .".</p>";
				echo '<br />';
				
				echo '<div id="day_select"><form method="get">';
				echo get_string('pastweek', 'report_daily') . ': ' . $selectbox;
				echo '</form></div>';
				
				echo '<h4>'. get_string('usageperformance', 'report_daily') .'</h4>';
				echo '<div class="graph"><img src="'.$CFG->wwwroot.'/report/daily/activitygraph.php?ts='.$timestamp.'" alt="Graph" width="750" height="400" /></div>';

				echo '<h4>'.get_string('userlogins', 'report_daily') .'</h4>';
				$end_today = strtotime('+24 hours', $timestamp);
				$query = sprintf("SELECT COUNT(DISTINCT userid) as no_logins  FROM ".$CFG->prefix."logstore_standard_log WHERE timecreated > %d AND timecreated < %d and target = 'user' and action ='loggedin'", 
				    $timestamp,
				    $end_today
				);
				if ($user_logins = $DB->get_records_sql($query)) {
					foreach($user_logins as $login) {
						$no_logins = number_format($login->no_logins);
					}
				} else {
					$no_logins = 0;
				}
				$date_chosen = date('d/m/y', $timestamp);
				$date_today = date('d/m/y', time());
				if ($date_chosen == $date_today) {
					echo "<p>$no_logins ". get_string('uniqueloginstoday', 'report_daily') ." (".date('d/m/y', $timestamp).")</p>";
				} else {
					echo "<p>$no_logins ". get_string('uniqueloginsdate', 'report_daily') ." <b>". strftime('%A', $timestamp)." (".date('d/m/y', $timestamp).")</b></p>";
				}
				
				$ts_last_week = strtotime('-1 week midnight', $timestamp);
				$ts_last_week_end = strtotime('tomorrow', $ts_last_week);

				
				$query = sprintf("SELECT COUNT(DISTINCT userid) as no_logins FROM ".$CFG->prefix."logstore_standard_log WHERE timecreated > %d AND timecreated < %d AND target = 'user' AND action ='loggedin'", 
                    $ts_last_week,
                    $ts_last_week_end
				);

				if ($user_logins = $DB->get_records_sql($query)) {
					foreach($user_logins as $login) {
						$no_logins = number_format($login->no_logins);
					}
				} else {
					$no_logins = 0;
				}
			  
				printf(get_string('uniqueloginsthisday', 'report_daily'), $no_logins,  strftime('%d/%m/%Y', $ts_last_week), strftime('%A', $timestamp));
				echo '</div>';
?>
                </td>
                <td id="right-column"></td>
            </tr>
        </tbody>
    </table>
</div>
<?php
	echo $OUTPUT->footer();
?>
