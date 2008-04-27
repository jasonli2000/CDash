<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id$
  Language:  PHP
  Date:      $Date$
  Version:   $Revision$

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
include("config.php");
include("common.php");

/** Generate the index table */
function generate_index_table()
{ 
  $noforcelogin = 1;
  include("config.php");
  include('login.php');
  include('version.php');

  $xml = '<?xml version="1.0"?><cdash>';
  $xml .= add_XML_value("title","CDash");
  $xml .= "<cssfile>".$CDASH_CSS_FILE."</cssfile>";
  $xml .= "<version>".$CDASH_VERSION."</version>";
  
  $xml .= "<hostname>".$_SERVER['SERVER_NAME']."</hostname>";
  $xml .= "<date>".date("r")."</date>";
  
  // Check if the database is up to date
  if(!mysql_query("SELECT showtesttime FROM project LIMIT 1"))
    {  
    $xml .= "<upgradewarning>The current database shema doesn't match the version of CDash you are running,
    upgrade your database structure in the Administration panel of CDash.</upgradewarning>";
    }

  $xml .= "<dashboard>

 <googletracker>".$CDASH_DEFAULT_GOOGLE_ANALYTICS."</googletracker>
 </dashboard> ";
 
 // Show the size of the database
 $rows = mysql_query("SHOW table STATUS");
  $dbsize = 0;
  while ($row = mysql_fetch_array($rows)) 
   {
  $dbsize += $row['Data_length'] + $row['Index_length']; 
  }
  
 $ext = "b";
 if($dbsize>1024)
   {
   $dbsize /= 1024;
   $ext = "Kb";
   }
 if($dbsize>1024)
   {
   $dbsize /= 1024;
   $ext = "Mb";
   }
 if($dbsize>1024)
   {
   $dbsize /= 1024;
   $ext = "Gb";
   }
 if($dbsize>1024)
   {
   $dbsize /= 1024;
   $ext = "Tb";
   } 
 $xml .= "<database>";
 $xml .= add_XML_value("size",round($dbsize,1).$ext);
 $xml .= "</database>";
 
  // User
  $userid = 0;
  if(isset($_SESSION['cdash']))
    {
    $xml .= "<user>";
    $userid = $_SESSION['cdash']['loginid'];
    $user = mysql_query("SELECT admin FROM user WHERE id='$userid'");
    $user_array = mysql_fetch_array($user);
    $xml .= add_XML_value("id",$userid);
    $xml .= add_XML_value("admin",$user_array["admin"]);
    $xml .= "</user>";
    }
        
  $projects = get_projects($userid);
  $row=0;
  foreach($projects as $project)
    {
    $xml .= "<project>";
    $xml .= "<name>".$project['name']."</name>";
      
    if($project['last_build'] == "NA")
      {
      $xml .= "<lastbuild>NA</lastbuild>";
      }
    else
      {
      $xml .= "<lastbuild>".date("Y-m-d H:i:s T",strtotime($project['last_build']. "UTC"))."</lastbuild>";
      }
    
    // Display the first build
    if($project['first_build'] == "NA")
      {
      $xml .= "<firstbuild>NA</firstbuild>";
      }
    else
      {
      $xml .= "<firstbuild>".date("Y-m-d H:i:s T",strtotime($project['first_build']. "UTC"))."</firstbuild>";
      }

    $xml .= "<nbuilds>".$project['nbuilds']."</nbuilds>";
    $xml .= "<row>".$row."</row>";
    $xml .= "</project>";
    if($row == 0)
      {
      $row = 1;
      }
    else
      {
      $row = 0;
      }
    }
  $xml .= "</cdash>";
  return $xml;
}

/** Generate the main dashboard XML */
function generate_main_dashboard_XML($projectid,$date)
{
  $start = microtime_float();
  $noforcelogin = 1;
  include("config.php");
  include('login.php');
  include('version.php');
      
  $db = mysql_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
  if(!$db)
    {
    echo "Error connecting to CDash database server<br>\n";
    exit(0);
    }
  if(!mysql_select_db("$CDASH_DB_NAME",$db))
    {
    echo "Error selecting CDash database<br>\n";
    exit(0);
    }
  
  $project = mysql_query("SELECT * FROM project WHERE id='$projectid'");
  if(mysql_num_rows($project)>0)
    {
    $project_array = mysql_fetch_array($project);
    $svnurl = make_cdash_url(htmlentities($project_array["cvsurl"]));
    $homeurl = make_cdash_url(htmlentities($project_array["homeurl"]));
    $bugurl = make_cdash_url(htmlentities($project_array["bugtrackerurl"]));
    $googletracker = htmlentities($project_array["googletracker"]);  
    $docurl = make_cdash_url(htmlentities($project_array["documentationurl"]));
    $projectpublic =  $project_array["public"]; 
    $projectname = $project_array["name"];
    }
  else
    {
    $projectname = "NA";
    }

  checkUserPolicy(@$_SESSION['cdash']['loginid'],$project_array["id"]);
    
  $xml = '<?xml version="1.0"?><cdash>';
  $xml .= "<title>CDash - ".$projectname."</title>";
  $xml .= "<cssfile>".$CDASH_CSS_FILE."</cssfile>";
  $xml .= "<version>".$CDASH_VERSION."</version>";

  list ($previousdate, $currentstarttime, $nextdate) = get_dates($date,$project_array["nightlytime"]);
  $logoid = getLogoID($projectid);

  // Main dashboard section 
  $xml .=
  "<dashboard>
  <datetime>".date("l, F d Y H:i:s T",time())."</datetime>
  <date>".$date."</date>
  <unixtimestamp>".$currentstarttime."</unixtimestamp>
  <svn>".$svnurl."</svn>
  <bugtracker>".$bugurl."</bugtracker> 
  <googletracker>".$googletracker."</googletracker> 
  <documentation>".$docurl."</documentation>
  <home>".$homeurl."</home>
  <logoid>".$logoid."</logoid> 
  <projectid>".$projectid."</projectid> 
  <projectname>".$projectname."</projectname> 
  <previousdate>".$previousdate."</previousdate> 
  <projectpublic>".$projectpublic."</projectpublic> 
  <nextdate>".$nextdate."</nextdate>";
 
  if($currentstarttime>time()) 
   {
   $xml .= "<future>1</future>";
    }
  else
  {
  $xml .= "<future>0</future>";
  }
  $xml .= "</dashboard>";

  // updates
  $xml .= "<updates>";
  
  $gmdate = gmdate("Ymd", $currentstarttime);
  $xml .= "<url>viewChanges.php?project=" . $projectname . "&amp;date=" .$gmdate. "</url>";

  $dailyupdate = mysql_query("SELECT count(*) FROM dailyupdatefile,dailyupdate 
                              WHERE dailyupdate.date='$gmdate' and projectid='$projectid'
                              AND dailyupdatefile.dailyupdateid = dailyupdate.id");
  $dailyupdate_array = mysql_fetch_array($dailyupdate);
  $nchanges = $dailyupdate_array[0]; 
  
  $dailyupdateauthors = mysql_query("SELECT dailyupdatefile.author FROM dailyupdatefile,dailyupdate 
                              WHERE dailyupdate.date='$gmdate' and projectid='$projectid'
                              AND dailyupdatefile.dailyupdateid = dailyupdate.id GROUP BY dailyupdatefile.author");
  $nauthors = mysql_num_rows($dailyupdateauthors);   
  $xml .= "<nchanges>".$nchanges."</nchanges>";
  $xml .= "<nauthors>".$nauthors."</nauthors>";
  $xml .= "<timestamp>" . date("Y-m-d H:i:s T", $currentstarttime)."</timestamp>";
  $xml .= "</updates>";

  // User
  if(isset($_SESSION['cdash']))
    {
    $xml .= "<user>";
    $userid = $_SESSION['cdash']['loginid'];
    $user2project = mysql_query("SELECT role FROM user2project WHERE userid='$userid' and projectid='$projectid'");
    $user2project_array = mysql_fetch_array($user2project);
    $user = mysql_query("SELECT admin FROM user WHERE id='$userid'");
    $user_array = mysql_fetch_array($user);
    $xml .= add_XML_value("id",$userid);
    $isadmin=0;
    if($user2project_array["role"]>1 || $user_array["admin"])
      {
      $isadmin=1;
       }
    $xml .= add_XML_value("admin",$isadmin);
    $xml .= "</user>";
    }
  
  $totalerrors = 0;
  $totalwarnings = 0;
  $totalconfigure = 0;
  $totalnotrun = 0;
  $totalfail= 0;
  $totalpass = 0;  
      
  // Local function to add expected builds
  function add_expected_builds($groupid,$currentstarttime,$received_builds,$rowparity)
    {
    $currentUTCTime =  gmdate("YmdHis",$currentstarttime+3600*24);
    $xml = "";
    $build2grouprule = mysql_query("SELECT g.siteid,g.buildname,g.buildtype,s.name FROM build2grouprule AS g,site as s
                                    WHERE g.expected='1' AND g.groupid='$groupid' AND s.id=g.siteid
                                    AND g.starttime<$currentUTCTime AND (g.endtime>$currentUTCTime OR g.endtime='0000-00-00 00:00:00')
                                    ");
    while($build2grouprule_array = mysql_fetch_array($build2grouprule))
      {
      $key = $build2grouprule_array["name"]."_".$build2grouprule_array["buildname"];
      if(array_search($key,$received_builds) === FALSE) // add only if not found
        {      
        $xml .= "<build>";
        
        /*      
        if($rowparity%2==0)
          {
           $xml .= add_XML_value("rowparity","trodd");
                    }
                else
                    {
                    $xml .= add_XML_value("rowparity","treven");
                    }
                $rowparity++;
        */
                
        $xml .= add_XML_value("site",$build2grouprule_array["name"]);
        $xml .= add_XML_value("siteid",$build2grouprule_array["siteid"]);
        $xml .= add_XML_value("buildname",$build2grouprule_array["buildname"]);
        $xml .= add_XML_value("buildtype",$build2grouprule_array["buildtype"]);
        $xml .= add_XML_value("buildgroupid",$groupid);
        $xml .= add_XML_value("expected","1");

        $divname = $build2grouprule_array["siteid"]."_".$build2grouprule_array["buildname"]; 
        $divname = str_replace("+","_",$divname);
        $divname = str_replace(".","_",$divname);

        $xml .= add_XML_value("expecteddivname",$divname);
        $xml .= add_XML_value("submitdate","No Submission");
        $xml  .= "</build>";
        }
      }
    return $xml;
    }
    
  // Check the builds
  $beginning_timestamp = $currentstarttime;
  $end_timestamp = $currentstarttime+3600*24;

  $beginning_UTCDate = gmdate("YmdHis",$beginning_timestamp);
  $end_UTCDate = gmdate("YmdHis",$end_timestamp);                                                      
  
  $sql =  "SELECT b.id,b.siteid,b.name,b.type,b.generator,b.starttime,b.endtime,b.submittime,g.name as groupname,gp.position,g.id as groupid 
                         FROM build AS b, build2group AS b2g,buildgroup AS g, buildgroupposition AS gp
                         WHERE b.starttime<$end_UTCDate AND b.starttime>=$beginning_UTCDate
                         AND b.projectid='$projectid' AND b2g.buildid=b.id AND gp.buildgroupid=g.id AND b2g.groupid=g.id  
                         AND gp.starttime<$end_UTCDate AND (gp.endtime>$end_UTCDate OR gp.endtime='0000-00-00 00:00:00')
                         ORDER BY gp.position ASC,b.name ASC ";
    
  // We shoudln't get any builds for group that have been deleted (otherwise something is wrong)
  $builds = mysql_query($sql);
  echo mysql_error();
  
  // The SQL results are ordered by group so this should work
  // Group position have to be continuous
  $previousgroupposition = -1;
  
  $received_builds = array();
  $rowparity = 0;
  $dynanalysisrowparity = 0;
  $coveragerowparity = 0;
  
  // Find the last position of the group
  $groupposition_array = mysql_fetch_array(mysql_query("SELECT gp.position FROM buildgroupposition AS gp,buildgroup AS g 
                                                        WHERE g.projectid='$projectid' AND g.id=gp.buildgroupid 
                                                        AND gp.starttime<$end_UTCDate AND (gp.endtime>$end_UTCDate OR gp.endtime='0000-00-00 00:00:00')
                                                        ORDER BY gp.position DESC LIMIT 1"));
  $lastGroupPosition = $groupposition_array["position"];
  
  while($build_array = mysql_fetch_array($builds))
    {
    $groupposition = $build_array["position"];
    if($previousgroupposition != $groupposition)
      {
      $groupname = $build_array["groupname"];  
      if($previousgroupposition != -1)
        {
        $xml .= add_expected_builds($groupid,$currentstarttime,$received_builds,$rowparity);
        if($previousgroupposition == $lastGroupPosition)
          {
          $xml .= "<last>1</last>";
          }
        $xml .= "</buildgroup>";
        }
      
      // We assume that the group position are continuous in N
      // So we fill in the gap if we are jumping
      $prevpos = $previousgroupposition+1;
      if($prevpos == 0)
        {
        $prevpos = 1;
        }
      for($i=$prevpos;$i<$groupposition;$i++)
        {
        $group = mysql_fetch_array(mysql_query("SELECT g.name,g.id FROM buildgroup AS g,buildgroupposition AS gp WHERE g.id=gp.buildgroupid 
                                                AND gp.position='$i' AND g.projectid='$projectid'
                                                AND gp.starttime<$end_UTCDate AND (gp.endtime>$end_UTCDate  OR gp.endtime='0000-00-00 00:00:00')
                                                "));
        $xml .= "<buildgroup>";
        $rowparity = 0;
        $xml .= add_XML_value("name",$group["name"]);
        $xml .= add_XML_value("linkname",str_replace(" ","_",$group["name"]));
        $xml .= add_XML_value("id",$group["id"]);
        $xml .= add_expected_builds($group["id"],$currentstarttime,$received_builds,$rowparity);
        if($previousgroupposition == $lastGroupPosition)
          {
          $xml .= "<last>1</last>";
          }
        $xml .= "</buildgroup>";  
        }  
             
      $xml .= "<buildgroup>";
      
      // Make the default for now
      // This should probably be defined by the user as well on the users page
      if($groupname == "Continuous")
        {
        $xml .= add_XML_value("sortlist","{sortlist: [[11,1]]}"); //buildtime
        }
      
      $rowparity = 0;
      $received_builds = array();
      $xml .= add_XML_value("name",$groupname);
      $xml .= add_XML_value("linkname",str_replace(" ","_",$groupname));
      $xml .= add_XML_value("id",$build_array["groupid"]);
      $previousgroupposition = $groupposition;
      }
    $groupid = $build_array["groupid"];
    $buildid = $build_array["id"];
    $configure = mysql_query("SELECT status FROM configure WHERE buildid='$buildid'");
    $nconfigure = mysql_num_rows($configure);
    $siteid = $build_array["siteid"];
    $site_array = mysql_fetch_array(mysql_query("SELECT name FROM site WHERE id='$siteid'"));
    
    // Get the site name
    $xml .= "<build>";
        
        if($rowparity%2==0)
          {
            $xml .= add_XML_value("rowparity","trodd");
          }
        else
          {
            $xml .= add_XML_value("rowparity","treven");
            }
        $rowparity++;
        
    $xml .= add_XML_value("type",strtolower($build_array["type"]));
    $xml .= add_XML_value("site",$site_array["name"]);
    $xml .= add_XML_value("siteid",$siteid);
    $xml .= add_XML_value("buildname",$build_array["name"]);
    $xml .= add_XML_value("buildid",$build_array["id"]);
    $xml .= add_XML_value("generator",$build_array["generator"]);
    
            
    // Search if we have notes for that build
    $buildnote = mysql_query("SELECT count(*) FROM buildnote WHERE buildid='$buildid'");
    $buildnote_array = mysql_fetch_row($buildnote);
    if($buildnote_array[0]>0)
      {
      $xml .= add_XML_value("buildnote","1");
      }
      
    $received_builds[] = $site_array["name"]."_".$build_array["name"];
    
    $note = mysql_query("SELECT count(*) FROM note WHERE buildid='$buildid'");
    $note_array = mysql_fetch_row($note);
    if($note_array[0]>0)
      {
      $xml .= add_XML_value("note","1");
      }
      
    $update = mysql_query("SELECT count(*) FROM updatefile WHERE buildid='$buildid'");
    $update_array = mysql_fetch_row($update);
    $xml .= add_XML_value("update",$update_array[0]);
  
    $updatestatus = mysql_query("SELECT status FROM buildupdate WHERE buildid='$buildid'");
    $updatestatus_array = mysql_fetch_array($updatestatus);
    
    if(strlen($updatestatus_array["status"]) > 0 && $updatestatus_array["status"]!="0")
      {
      $xml .= add_XML_value("updateerrors",1);
      }
    else
      {
      $updateerrors = mysql_query("SELECT count(*) FROM updatefile WHERE buildid='$buildid' AND author='Local User' AND revision='-1'");
      $updateerrors_array = mysql_fetch_row($updateerrors);
      //$xml .= add_XML_value("updateerrors",$updateerrors_array[0]);
      $xml .= add_XML_value("updateerrors",0);
      if($updateerrors_array[0]>0)
        {
        $xml .= add_XML_value("updatewarning",1);
        }
      }
   
    $xml .= "<compilation>";
    
    // Find the number of errors and warnings
    $builderror = mysql_query("SELECT count(*) FROM builderror WHERE buildid='$buildid' AND type='0'");
    $builderror_array = mysql_fetch_array($builderror);
    $nerrors = $builderror_array[0];
    $totalerrors += $nerrors;
    $xml .= add_XML_value("error",$nerrors);
    $buildwarning = mysql_query("SELECT count(*) FROM builderror WHERE buildid='$buildid' AND type='1'");
    $buildwarning_array = mysql_fetch_array($buildwarning);
    $nwarnings = $buildwarning_array[0];
    $totalwarnings += $nwarnings;
    $xml .= add_XML_value("warning",$nwarnings);
    $diff = (strtotime($build_array["endtime"])-strtotime($build_array["starttime"]))/60;
    $xml .= "<time>".$diff."</time>";
    $xml .= "</compilation>";
    
    // Get the Configure options
    $configure = mysql_query("SELECT status FROM configure WHERE buildid='$buildid'");
    if($nconfigure)
      {
      $configure_array = mysql_fetch_array($configure);
      $xml .= add_XML_value("configure",$configure_array["status"]);
      $totalconfigure += $configure_array["status"];
      }
  
    // Get the tests
    $test = mysql_query("SELECT * FROM build2test WHERE buildid='$buildid'");
    if(mysql_num_rows($test)>0)
      {
      $test_array = mysql_fetch_array($test);
      $xml .= "<test>";
      // We might be able to do this in one request
      $nnotrun_array = mysql_fetch_array(mysql_query("SELECT count(*) FROM build2test WHERE buildid='$buildid' AND status='notrun'"));
      $nnotrun = $nnotrun_array[0];
      
      $sql = "SELECT count(*) FROM build2test WHERE buildid='$buildid' ";
      if($project_array["showtesttime"] == 1)
        {
        $sql .= "AND (status='failed' OR timestatus='1')";
        }
      else
        {
        $sql .= "AND status='failed'";
        }
      $nfail_array = mysql_fetch_array(mysql_query($sql));
      $nfail = $nfail_array[0];
      
      $sql = "SELECT count(*) FROM build2test WHERE buildid='$buildid' AND status='passed'";
      if($project_array["showtesttime"] == 1)
        {
        $sql .= " AND timestatus='0'";
        }
      $npass_array = mysql_fetch_array(mysql_query($sql));
      $npass = $npass_array[0];      
  
      $time_array = mysql_fetch_array(mysql_query("SELECT SUM(time) FROM build2test WHERE buildid='$buildid'"));
      $time = $time_array[0];
      
      $totalnotrun += $nnotrun;
      $totalfail += $nfail;
      $totalpass += $npass;
      
      $xml .= add_XML_value("notrun",$nnotrun);
      $xml .= add_XML_value("fail",$nfail);
      $xml .= add_XML_value("pass",$npass);
      $xml .= add_XML_value("time",round($time/60,1));
      $xml .= "</test>";
      }
     
     $starttimestamp = strtotime($build_array["starttime"]." UTC");
     $submittimestamp = strtotime($build_array["submittime"]." UTC");
     $xml .= add_XML_value("builddate",date("Y-m-d H:i:s T",$starttimestamp)); // use the default timezone
     $xml .= add_XML_value("submitdate",date("Y-m-d H:i:s T",$submittimestamp));// use the default timezone
     $xml .= "</build>";
    
    // Coverage
    $coverages = mysql_query("SELECT * FROM coveragesummary WHERE buildid='$buildid'");
    while($coverage_array = mysql_fetch_array($coverages))
      {
      $xml .= "<coverage>";
      if($coveragerowparity%2==0)
        {
        $xml .= add_XML_value("rowparity","trodd");
        }
      else
        {
        $xml .= add_XML_value("rowparity","treven");
        }
      $coveragerowparity++;
                
      $xml .= "  <site>".$site_array["name"]."</site>";
      $xml .= "  <buildname>".$build_array["name"]."</buildname>";
      $xml .= "  <buildid>".$build_array["id"]."</buildid>";
      
      @$percent = round($coverage_array["loctested"]/($coverage_array["loctested"]+$coverage_array["locuntested"])*100,2);
      
      $xml .= "  <percentage>".$percent."</percentage>";
      $xml .= "  <percentagegreen>".$project_array["coveragethreshold"]."</percentagegreen>";
      $xml .= "  <fail>".$coverage_array["locuntested"]."</fail>";
      $xml .= "  <pass>".$coverage_array["loctested"]."</pass>";
      
      $starttimestamp = strtotime($build_array["starttime"]." UTC");
      $submittimestamp = strtotime($build_array["submittime"]." UTC");
      $xml .= add_XML_value("date",date("Y-m-d H:i:s T",$starttimestamp)); // use the default timezone         
      $xml .= add_XML_value("submitdate",date("Y-m-d H:i:s T",$submittimestamp));// use the default timezone
      $xml .= "</coverage>";
      }  // end coverage
    
    // Dynamic Analysis
    $dynanalysis = mysql_query("SELECT checker FROM dynamicanalysis WHERE buildid='$buildid' LIMIT 1");
    while($dynanalysis_array = mysql_fetch_array($dynanalysis))
      {
      $xml .= "<dynamicanalysis>";
      if($dynanalysisrowparity%2==0)
        {
        $xml .= add_XML_value("rowparity","trodd");
        }
      else
        {
        $xml .= add_XML_value("rowparity","treven");
        }
      $dynanalysisrowparity++;
      $xml .= "  <site>".$site_array["name"]."</site>";
      $xml .= "  <buildname>".$build_array["name"]."</buildname>";
      $xml .= "  <buildid>".$build_array["id"]."</buildid>";
      
      $xml .= "  <checker>".$dynanalysis_array["checker"]."</checker>";
      $defect = mysql_query("SELECT sum(dd.value) FROM dynamicanalysisdefect AS dd,dynamicanalysis as d 
                                              WHERE d.buildid='$buildid' AND dd.dynamicanalysisid=d.id");
      $defectcount = mysql_fetch_array($defect);
      if(!isset($defectcount[0]))
        {
        $defectcounts = 0;
        }
      else
        { 
        $defectcounts = $defectcount[0];
        }
      $xml .= "  <defectcount>".$defectcounts."</defectcount>";
      $starttimestamp = strtotime($build_array["starttime"]." UTC");
      $submittimestamp = strtotime($build_array["submittime"]." UTC");
      $xml .= add_XML_value("date",date("Y-m-d H:i:s T",$starttimestamp)); // use the default timezone
      $xml .= add_XML_value("submitdate",date("Y-m-d H:i:s T",$submittimestamp));// use the default timezone
      $xml .= "</dynamicanalysis>";
      }  // end coverage   
    } // end looping through builds
    
  if(mysql_num_rows($builds)>0)
    {
    $xml .= add_expected_builds($groupid,$currentstarttime,$received_builds,$rowparity);
    if($previousgroupposition == $lastGroupPosition)
      {
      $xml .= "<last>1</last>";
      }
    $xml .= "</buildgroup>";
    }
    
  // Fill in the rest of the info
  $prevpos = $previousgroupposition+1;
  if($prevpos == 0)
    {
    $prevpos = 1;
    }
    
  for($i=$prevpos;$i<=$lastGroupPosition;$i++)
    {
    $group = mysql_fetch_array(mysql_query("SELECT g.name,g.id FROM buildgroup AS g,buildgroupposition AS gp WHERE g.id=gp.buildgroupid 
                                                                                     AND gp.position='$i' AND g.projectid='$projectid'
                                                                                     AND gp.starttime<$end_UTCDate AND (gp.endtime>$end_UTCDate  OR gp.endtime='0000-00-00 00:00:00')"));
    $xml .= "<buildgroup>";  
    $xml .= add_XML_value("id",$group["id"]);
    $xml .= add_XML_value("name",$group["name"]);
    $xml .= add_XML_value("linkname",str_replace(" ","_",$group["name"]));
    $xml .= add_expected_builds($group["id"],$currentstarttime,$received_builds,$rowparity);
    if($i == $lastGroupPosition)
      {
      $xml .= "<last>1</last>";
      }
    $xml .= "</buildgroup>";  
    }
 
  $xml .= add_XML_value("totalConfigure",$totalconfigure);
  $xml .= add_XML_value("totalError",$totalerrors);
  $xml .= add_XML_value("totalWarning",$totalwarnings);
 
  $xml .= add_XML_value("totalNotRun",$totalnotrun);
  $xml .= add_XML_value("totalFail",$totalfail);
  $xml .= add_XML_value("totalPass",$totalpass); 
   
  $end = microtime_float();
  $xml .= "<generationtime>".round($end-$start,3)."</generationtime>";
  $xml .= "</cdash>";

  return $xml;
} 

// Check if we can connect to the database
$db = mysql_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
if(!$db)
  {
  // redirect to the install.php script
  echo "<script language=\"javascript\">window.location='install.php'</script>";
  return;
  }
if(mysql_select_db("$CDASH_DB_NAME",$db) === FALSE)
  {
  echo "<script language=\"javascript\">window.location='install.php'</script>";
  return;
  }
if(mysql_query("SELECT id FROM user LIMIT 1",$db) === FALSE)
  {
  echo "<script language=\"javascript\">window.location='install.php'</script>";
  return;
  }


@$projectname = $_GET["project"];

// If we should not generate any XSL
if(isset($NoXSLGenerate))
  {
  return;
  }
    
function microtime_float()
  {
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
  }


if(!isset($projectname )) // if the project name is not set we display the table of projects
  {
  $xml = generate_index_table();
  // Now doing the xslt transition
  generate_XSLT($xml,"indextable");
  }
else
  {
  $projectid = get_project_id($projectname);
  @$date = $_GET["date"];
  
  $xml = generate_main_dashboard_XML($projectid,$date);
  // Now doing the xslt transition
  generate_XSLT($xml,"index");
  }
?>
