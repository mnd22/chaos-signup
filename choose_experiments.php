<?php

  /**
   * @file choose_experiments.php
   * 
   * http://www.chaosscience.org.uk/committee/events/chooseexpts
   * 
   * POST variables:
   * @param eventid The node ID of the event to add experiments to.
   *
   * Table structure is as follows (some info trimmed):
   * DESCRIBE signup_system_experiments
   *   Array ( [Field] => eventid [Type] => int(10) unsigned [Null] => NO )
   *   Array ( [Field] => exptid [Type] => int(10) unsigned [Null] => NO )
   *   Array ( [Field] => datecreated [Type] => int(10) unsigned [Null] => NO )
   *   Array ( [Field] => datechanged [Type] => int(10) unsigned [Null] => NO )
   * 
   * @author   Mark Durkee
   */
   
  include_once("../signup_system/useful_functions.php");

  /**
   * Adds an experiment to the list for the specified event.
   *
   * @param $exptid     The node ID of the experiment
   * @param $eventid    The node ID of the event.
   * @param $mindems    Minimum number of demonstrators to be assigned.
   * @param $maxdems    Maximum number of demonstrators to be assigned.
   *
   * @returns Boolean   TRUE if successful, FALSE otherwise.
   */
  function add_experiment_to_event($exptid, $eventid, $mindems, $maxdems)
  {
    global $TABLES;
    
    #--------------------------------------------------------------------------
    # Check whether the experiment and event IDs given correspond to actual
    # experiments and events.  If not, do nothing.
    #--------------------------------------------------------------------------
    if (is_experiment($exptid) && is_event($eventid))
    {
      $query = "INSERT INTO " . $TABLES['EXPERIMENTS']
                      . " (eventid, exptid, mindems, maxdems) VALUES"
                      . "('" . $eventid .  "', '" . $exptid . "', '"
                             . $mindems . "', '" . $maxdems ."')";
      $successful = db_query($query);
    }
    else
    {
      $successful = FALSE;
    }

    return $successful;
  }

 /**
  * Clears out existing experiment choices, and replaces them with new ones.
  * Requires $_POST access.
  *
  * @param $eventid  ID of the event that we're saving expts for.
  *
  */
  function save_experiment_list($eventid)
  {
    global $TABLES;

    $query = 'DELETE FROM '. $TABLES['EXPERIMENTS'] . ' WHERE eventid = '
                                                    . (string)$eventid;
    $successful = db_query($query);
  
    if (isset($_POST['exptlist']))
    {
      $new_expt_list = $_POST['exptlist'];
      
      foreach($new_expt_list as $exptid)
      {
        $mindems = '1';
        $maxdems = '1';

        if (isset($_POST['min' . $exptid]) && isset($_POST['max' . $exptid]))
        {
          $mindems = $_POST['min' . $exptid];
          $maxdems = $_POST['max' . $exptid];
        }
      
        $successful = add_experiment_to_event($exptid, 
                                              $eventid,
                                              $mindems,
                                              $maxdems);
        if (!$successful)
        {
          echon('Failed to save experiment ' . $exptid . ' to DB');
        }
      }
    }
    else
    {
      echon("You've clicked submit, but no experiments were selected.  If");
      echon("this wasn't intentional you'll need to select a new list now");
    }
    
    return $successful;
  }
  
 /**
  * Generates main page content.
  */
  function main_choose_experiments()
  {
    global $URLS, $TABLES, $EMAILS, $EXPT_SUBJECTS;
        
    if (!isset($_GET['eventid']))
    {
      echon("<p>");
      echon(" This page allows you to choose experiments in use for an event,");
      echon("  you've somehow managed to get here without specifying which");
      echon("  event!  If you clicked a link to get here");
      echon("  this might be a bug in the website, , it would be helpful if");
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
      echon(" This page allows you to choose experiments in use for an event,");
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
    # We now know this is a legitimate event, so print header and proceed.
    #---------------------------------------------------------------------------
    $event_title = get_node_title($eventid);
    $event_page_link = get_node_link($eventid);
    
    echon('<h1>' . $event_title . '</h1>');
    echon('<h2>Selections of available experiments</h2>');
    echon('<p>'); 
    echon('  Use this page to select which experiments are required for'); 
    echon('  the event and how many demonstrators are required for each.'); 
    echon('  The min/max demonstrators figures will make the assignment tool'); 
    echon('  automatically highlight when enough demonstrators have been'); 
    echon('  assigned.  For a reserve experiment that you are happy to not');
    echon('  run, select 0 as the minimum value.  Some experiments, such as');  
    echon('  Sodium Acetate, require at least two demonstrators at CBS!');  
    echon('  '); 
    echon('</p>');
    echon('<p><a href="' . get_node_link($eventid) . '">'); 
    echon('  Return to main event page'); 
    echon('</a></p>'); 
     
    if (isset($_POST['changesubmitted']))
    {
      #-------------------------------------------------------------------------
      # Save event experiment selection.
      #-------------------------------------------------------------------------
      save_experiment_list($eventid);
    }

    #---------------------------------------------------------------------------
    # Now create the form that allows the user to enter updates.              
    #---------------------------------------------------------------------------
    $current_url = $URLS['EXPT_CHOICE'] . '?eventid=' . $eventid;
    echon('<form action="' . $current_url . '" method="post">');
    echon('  <input type="hidden" name="changesubmitted" />');
    echon('  <input type="hidden" name="eventid" value="' . $eventid . '"/>');

    echon('  <table>');
    echon('    <tr>');
    echon('      <th>Experiment Name</th>');
    echon('      <th>Selected</th>');
    echon('      <th>Min dems</th>');
    echon('      <th>Max dems</th>');
    echon('    </tr>');
    
    #---------------------------------------------------------------------------
    # Get a full list of all experiments in the database, in format
    #      Array( ExperimentID => Array(name, subject, status))
    #---------------------------------------------------------------------------
    $expts_array = list_all_experiments();

    $current_expt_list = get_event_expt_list($eventid);

    foreach ($EXPT_SUBJECTS as $tid => $subject)
    {
      echon('    <tr>');
      echon('      <td colspan=4><b>' . $subject . '</b></td>');
      echon('    </tr>');

      #-------------------------------------------------------------------------
      # Show experiments of this subject.
      #-------------------------------------------------------------------------
      foreach ($expts_array as $exptid => $expt_details)
      {
        if ($expt_details['subject'] == $subject)
        {
          $checked_string = '';
          $minvalue = '1';
          $maxvalue = '1';

          if (array_key_exists($exptid, $current_expt_list))
          {
            $checked_string = 'checked="checked" ';
            $minvalue = $current_expt_list[$exptid]['mindems'];
            $maxvalue = $current_expt_list[$exptid]['maxdems'];
          }

          $expt_url = $BASE_URL . '/node/' . (string)$exptid;
          echon('    <tr>');
          echon('      <td><a href="' . $expt_url . '">'
                      . htmlspecialchars($expt_details['title']) . "</a></td>");
          echon('      <td><input type="checkbox" name="exptlist[]" value="'
                              . $exptid . '" ' . $checked_string . '/> </td>');
          echon('      <td><input type="text" name="min' . $exptid
                              . '" value="' . $minvalue . '" size="3" /></td>');
          echon('      <td><input type="text" name="max' . $exptid
                              . '" value="' . $maxvalue . '" size="3" /></td>');
          echon('    </tr>');
        }
      }
    }
      
    echon('  </table>');
    
    echon('  <input type="submit" value="Submit choices" />');
    
    echon('</form>');

  }
      

  #----------------------------------------------------------------------------
  # START OF MAIN FUNCTION
  #
  # Just calls into the main_choose_experiments() function defined above.                    
  #---------------------------------------------------------------------------- 
  main_choose_experiments()

?>

