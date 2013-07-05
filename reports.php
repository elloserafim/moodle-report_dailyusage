<?php 

require_once('../../config.php');
require_once("../../course/lib.php");

$id = optional_param('id', $SITE->id, PARAM_INT);
$ts = optional_param('ts', 0, PARAM_INT);

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_login($course);

$context = context_course::instance($course->id);

require_capability('report/loglive:view', $context);

$title = "Last Updated Courses";

$url = new moodle_url('/report/daily/reports.php');

$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->navbar->add('Daily', new moodle_url('index.php'));
$PAGE->navbar->add($title, new moodle_url('index.php'));

echo $OUTPUT->header();

$title_id = get_string("courses");
$title_id .="(ID) (Desc)";
$title_fullname=get_string("fullname");
$title_shortname=get_string("shortname");
//    $title_key=get_string("password");
$title_startdate=get_string("startdate");
//added_by_PA
$title_lastmod="Last Updated";
$title_num_resources="No: Resources";
$title_num_labels="No: Labels";
$title_res_minus_labels="Resources - Labels";

?>

<center>

	<p class="style1">This page lists all visible Moodle courses - it shows when they were last updated and shows how many resources (excluding Labels) each course has.</p>
	<p class="style1">To view a particular course, click on it's course number (in bold) and it will open in a new window. </p>
	<p class="style1">At the bottom of the page the average # resources per course is also listed. </p>

	<table border=3>

	<?php
    
    if (!$courses = $DB->get_records("course",array("visible" => "1"),"timemodified DESC")) {
        error("geen courses!");
    }
    
    $counter = 1;
	$tot_res = 0;
            
    echo "<tr bgcolor=yellow align=center>
            <td align=left>TOT</td>
            <td align=left><b>".$title_id."</b></td>
            <td align=left><b>Details</b></td>
            <td align=left>".$title_fullname."</td>
            <td align=left>".$title_shortname."</td>
			<td align=left>".$title_lastmod."</td>
			<td align=left>".$title_res_minus_labels."</td>
          </tr>";
    $i = 0;
    foreach ($courses as $course) {
            
        if ($i <=20) {	
			$res = count(get_array_of_activities($course->id));
			$lab = count(get_all_instances_in_course('label', $course));
			$total = $res - $lab;
            
			echo "<tr> 
                    <td align=center bgcolor=\'#FFFFB8\'> $counter </td>
                    <td align=center bgcolor=\'#eeffff\'><b><a target='blank' href=\"$CFG->wwwroot/course/view.php?id=$course->id\">".$course->id."</a></b></td>
                    <td align=center><a target='_info' href=\"$CFG->wwwroot/course/info.php?id=$course->id\">
                    <img alt='Info' src='http://moodle.coleggwent.ac.uk/pix_gloscat/help.gif'></a>
                    <td align=left>".$course->fullname."</td>
                    <td align=left>".$course->shortname."</td>
					<td align=right><font size=-2>".strftime('%d/%m/%y %H:%M',$course->timemodified)."</font></td>
					<td align=right><font size=-2>".$total."</font></td>
                  </tr>";    
    $counter=$counter+1;
	$tot_res=$tot_res+$total;
        $i++;
        }
    }
	
//Summery Stats Added by PA

//Work out mean number of resources
$avg_courses=floor($tot_res/$counter);

echo "

	<tr> 
                    <td colspan=\"7\" align=right bgcolor='#000000'></td>
	</tr>
	<tr> 
                    <td colspan=\"7\" align=right>
                        <b>Mean Resources per course: ".$avg_courses."</b>
                    </td>
	</tr>";  
	
?>

    </table>

</center>

<?php

echo $OUTPUT->footer();

?>
