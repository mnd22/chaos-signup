<?php

  /**
   * Lists current assignments for this event by experiment, allowing the
   * user to see which experiments are still awaiting a demonstrator, and
   * which have too many.
   * http://www.chaosscience.org.uk/committee/events/listbyexperiment
   *      
   * @file list_by_experiment.php
   *
   * @author   Mark Durkee
   */
   
  include_once("../signup_system/useful_functions.php");
  
 /**
  * Get list of people who have been assigned to an experiment, structured by
  * experiment list.
  *
  * @param $eventid 
  * @param $current_expt_list
  *
  * @returns Array of form ('morning' => $morning_list, 
  *                         'afternoon' => $afternoon_list); 
  *          where each list is an array of assignments to each experiment.
  */ 
  function get_signups_per_expt($eventid, $current_expt_list)
  {
    global $TABLES;

    #---------------------------------------------------------------------------
    # Check whether the experiment and event IDs given correspond to actual
    # experiments and events.  If not, do nothing.
    #
    # Table has syntax:
    # (eventid INT UNSIGNED NOT NULL, userid INT UNSIGNED NOT NULL,
    #  mornexptid INT UNSIGNED, afterexptid INT UNSIGNED)
    #---------------------------------------------------------------------------
    $query = 'SELECT * FROM ' . $TABLES['EXPT_ASSIGN'] .
                     ' WHERE eventid="' . $eventid . '"';
    $query_result = db_query($query);
    
    #---------------------------------------------------------------------------
    # Create arrays of form "exptid => List of assigned dems" for each session
    #---------------------------------------------------------------------------
    $morning_list = Array();
    $afternoon_list = Array();
     
    foreach ($current_expt_list as $exptid => $dems)
    {
      $morning_list[$exptid] = Array();
      $afternoon_list[$exptid] = Array();      
    } 
                       
    while ($row = db_fetch_array($query_result))
    {
      if (isset($row['userid']))
      {
        if (isset($row['mornexptid']) && array_key_exists($row['mornexptid'],
                                                          $current_expt_list))
        {
          $morning_list[$row['mornexptid']][] = $row['userid'];
        }
        
        if (isset($row['afterexptid']) && array_key_exists($row['afterexptid'],
                                                           $current_expt_list))
        {
          $afternoon_list[$row['afterexptid']][] = $row['userid'];
        }      
      }
      else
      {
        #-----------------------------------------------------------------------
        # This should never happen, as userid is not null in mysql.
        #-----------------------------------------------------------------------
        echon("ERROR: Database corruption in experiment list.");
        exit(0);
      }
    }
        
    return Array('morning'   => $morning_list,
                 'afternoon' => $afternoon_list);
  }
  
 /**
  * Main function generating page content.
  */ 
  function main_list_by_experiment()
  {
    global $URLS, $TABLES, $EMAILS, $EXPT_SUBJECTS;
        
    if (!isset($_GET['eventid']))
    {
      echon("<p>");
      echon(" This page allows you to show experiment assignments for an");
      echon("  event, but you've somehow managed to get here without");
      echon("  specifying which event!  If you clicked a link to get here");
      echon("  this might be a bug in the website, it would be helpful if");
      echon("  you could report this to " . $EMAILS['WEB'] . ".");
      echon("</p>");
      echon("<p>");
      echon("  <a href='" . $URLS['EVENT_LIST']
                             . "'>Click here to go to the list of events.</a>");
      echon("</p>");
      return FALSE;
    }

    #---------------------------------------------------------------------------
    # Page has been called with an event specified, so record this and proceed.
    #---------------------------------------------------------------------------
    $eventid = $_GET['eventid'];

    if (!is_event($eventid))
    {
      echon("<p>");
      echon(" This page shows experiment assignments for an event,");
      echon(" but you've somehow managed to specify an invalid event code.");
      echon(" If you clicked a link to get here, this might be a bug in the");
      echon(" website, it would be helpful if you could report this to " .
                                                          $EMAILS['WEB'] . ".");
      echon("</p>");
      echon("<p>");
      echon("  <a href='" . $URLS['EVENT_LIST']
                             . "'>Click here to go to the list of events.</a>");
      echon("</p>");
      return FALSE;
    }

    #---------------------------------------------------------------------------
    # We now know this is a legitimate event, so print its title and proceed.
    #---------------------------------------------------------------------------
    $event_title = get_node_title($eventid);
    echon('<h1>' . $event_title . '</h1>');
    echon('<h2>List of current experiment assignments</h2>');
     
    #---------------------------------------------------------------------------
    # This is a read-only page, so no need to create a form.
    #---------------------------------------------------------------------------
    $current_url = $URLS['BASE'] . 'committee/events/listbyexperiment?eventid=' 
                                                                     . $eventid;

    echon('<table>');
    echon('  <tr>');
    echon('    <th>Experiment Name</th>');
    echon('    <th>Morning<br />demonstrators</th>');
    echon('    <th>Afternoon<br />demonstrators</th>');
    echon('    <th>Min dems</th>');
    echon('    <th>Max dems</th>');
    echon('  </tr>');
    
    #---------------------------------------------------------------------------
    # Get a full list of all experiments in the database, in format
    #      Array( ExperimentID => Array(name, subject, status))
    #---------------------------------------------------------------------------
    $expts_array = list_all_experiments();
    $current_expt_list = get_event_expt_list($eventid);
    $currentsignups = get_signups_per_expt($eventid, $current_expt_list);
    $errortag = ' bgcolor="#F78181"';
    
    foreach ($EXPT_SUBJECTS as $tid => $subject)
    {
      echon('  <tr>');
      echon('    <td colspan=5><b>' . $subject . '</b></td>');
      echon('  </tr>');

      #-------------------------------------------------------------------------
      # Show experiments of this subject.
      #-------------------------------------------------------------------------
      foreach ($expts_array as $exptid => $expt_details)
      {
        if ($expt_details['subject'] == $subject)
        {

          if (array_key_exists($exptid, $current_expt_list))
          {
            $mornlist = $currentsignups['morning'][$exptid];
            $afterlist = $currentsignups['afternoon'][$exptid]; 
            
            #-------------------------------------------------------------------
            # Generate full names of users from userid codes.
            #-------------------------------------------------------------------
            $mornnames = Array();
            $afternames = Array();
            
            foreach ($mornlist as $userid)
            {
              $mornnames[] = get_user_detail($userid, 'fullname');
            }
            
            foreach ($afterlist as $userid)
            {
              $afternames[] = get_user_detail($userid, 'fullname');
            }

            #-------------------------------------------------------------------
            # Check whether this row meets assignment parameters.
            #-------------------------------------------------------------------            
            $morncount = count($mornlist);
            $aftercount = count($afterlist);
            $minvalue = (int)($current_expt_list[$exptid]['mindems']);
            $maxvalue = (int)($current_expt_list[$exptid]['maxdems']);
            
            $mornerrortag = '';
            $aftererrortag = '';
            $minerrortag = '';
            $maxerrortag = '';
            
            if ($morncount < $minvalue)
            {
              $mornerrortag = $errortag;
              $minerrortag = $errortag; 
            }

            if ($aftercount < $minvalue)
            {
              $aftererrortag = $errortag;
              $minerrortag = $errortag; 
            }
            
            if ($morncount > $maxvalue)
            {
              $mornerrortag = $errortag;
              $maxerrortag = $errortag; 
            }

            if ($aftercount > $maxvalue)
            {
              $aftererrortag = $errortag;
              $maxerrortag = $errortag; 
            }
                      
            $expt_url = $BASE_URL . '/node/' . (string)$exptid;
            echon('  <tr>');
            echon('    <td><a href="' . $expt_url . '">'
                      . htmlspecialchars($expt_details['title']) . "</a></td>");
            echon('    <td' . $mornerrortag  . '>' 
                            . implode('<br />', $mornnames)  . '</td>');
            echon('    <td' . $aftererrortag . '>' 
                            . implode('<br />', $afternames)  . '</td>');
            echon('    <td' . $minerrortag   . '>' . $minvalue   . '</td>');
            echon('    <td' . $maxerrortag   . '>' . $maxvalue   . '</td>');
            echon('  </tr>');
          }
        }
      }
    }
      
    echon('  </table>');
    
  }
      

  #----------------------------------------------------------------------------
  # START OF MAIN FUNCTION
  #
  # Just calls into the main_list_by_experiment() function defined above.                    
  #---------------------------------------------------------------------------- 
  main_list_by_experiment()

?>

 
