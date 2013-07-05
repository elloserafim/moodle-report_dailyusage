<?php

defined('MOODLE_INTERNAL') || die;

// just a link to course report
$ADMIN->add('reports', new admin_externalpage('reportdaily', 'Daily', "$CFG->wwwroot/report/daily/index.php", 'report/stats:view', empty($CFG->enablestats)));

// no report settings
$settings = null;
