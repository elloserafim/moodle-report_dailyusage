<?php

defined('MOODLE_INTERNAL') || die;

// just a link to course report
$ADMIN->add('reports', new admin_externalpage('reportdailyusage', 'Daily usage', "$CFG->wwwroot/report/dailyusage/index.php", 'report/stats:view', empty($CFG->enablestats)));

// no report settings
$settings = null;
