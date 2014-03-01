<?php

  /**
   * @file choose_experiments.php
   *
   * Table structure is as follows (some info trimmed):
   * DESCRIBE signup_system_experiments
   *   Array ( [Field] => eventid [Type] => int(10) unsigned [Null] => NO )
   *   Array ( [Field] => exptid [Type] => int(10) unsigned [Null] => NO )
   *   Array ( [Field] => datecreated [Type] => int(10) unsigned [Null] => NO )
   *   Array ( [Field] => datechanged [Type] => int(10) unsigned [Null] => NO )
   *
   * @author   Mark Durkee
   * @version  V0.01
   */
   
  include_once("../signup_system/useful_functions.php");
  include_once("../signup_system/constants.php");

  /**
   * Adds an experiment to the list for the specified event.
   *
   * @param $exptid     The node ID of the experiment
   * @param $eventid    The node ID of the event.
   *
   * @returns Boolean   TRUE if successful, FALSE otherwise.
   */
  function add_experiment_to_event($exptid, $eventid, $min_dems, $max_dems)
  {

    #--------------------------------------------------------------------------
    # Check whether the experiment and event IDs given correspond to actual
    # experiments and events.  If not, do nothing.
    #--------------------------------------------------------------------------
    if (is_experiment($exptid) && is_event($eventid))
    {
      $query = 'INSERT INTO ' . $TABLES['EXPERIMENTS'] . ' '
                      . "(eventid, exptid) VALUES"
                      . "('" . $exptid . "', '" . $eventid . "')";
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
  * @param $eventid  ID of the event that we're saving expts for.
  *
  */
  function save_experiment_list($eventid)
  {
    $query = 'DELETE FROM table_name WHERE eventid = ' . (string)$eventid;
    $successful = db_query($insert_query);
  
    if (isset($_POST['exptlist']))
    {
      $new_expt_list = $_POST('exptlist');
      
      foreach($new_expt_list as $exptid)
      {
        $query = 'DELETE FROM table_name WHERE eventid = ' . (string)$eventid;
        $successful = db_query($insert_query);
      }
    }
    else
    {
      echon("You've clicked submit, but no experiments were selected.  If");
      echon("this wasn't intentional you'll need to select a new list now");
    }
  
  }
  
  function main()
  {
    global $URLS, $TABLES, $EMAILS;
        
    if (!isset($_POST['eventid']))
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
    $event_id = $_POST['eventid'];

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
    # We now know this is a legitimate event, so print its title and proceed.
    #---------------------------------------------------------------------------
    $event_title = get_node_title($eventid);
    echon('<h1>' . $event_title . '</h1>');
    echon('<h2>Selections of available experiments</h2>');
     
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
    $current_url = $URLS['EXPT_CHOICE'] . '?eventid=' . $event_id;
    echon('<form action="' . $current_url . '" method="get">');
    echon('  <input type="submit" value="Submit Experiment Choice" />');
    echon('  <input type="hidden" name="changesubmitted" />');
    echon('  <input type="hidden" name="eventid" value="' . $event_id . '"/>');

    echon('  <table>');
    echon('    <tr>');
    echon('      <th>ExperimentName</th>');
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
          $expt_url = $BASE_URL . '/node/' . (string)$exptid;
          echon('    <tr>');
          echon('      <td><a href="' . $expt_url . '">' . $expt_details['name']
                                                         . "</a></td>");
          echon('      <td><input type="checkbox" name="exptlist[]" value="'
                                                        . $exptid . '/> </td>');
          echon('      <td><input type="text" name="min' . $exptid
                                                        . '" value="1"/></td>');
          echon('      <td><input type="text" name="max' . $exptid
                                                        . '" value="1"/></td>');
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
  # Just calls into the main() function defined above.                    
  #---------------------------------------------------------------------------- 
  main()

?>

 