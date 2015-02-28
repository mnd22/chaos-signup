<?php
 /**
   * @file experiment_choices_assign.php
   * Assign experiments to demonstrators.
   * 
   * http://www.chaosscience.org.uk/committee/events/assignexpts
   * 
   * POST variables:
   * @param eventid The node ID of the event to add experiments to.
   * 
   * @author   Mark Durkee
   */

  include_once("../signup_system/useful_functions.php");

  /**
   * Displays a list of current signups.
   */
  function main_experiment_choices_assign()
  {
    global $URLS, $TABLES, $EMAILS, $EXPT_SUBJECTS;

    if (!isset($_GET['eventid']))
    {
      echon("<p>");
      echon(" This page allows you to view experiment choices for an event,");
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

    $eventid = $_GET['eventid'];
    $signup_url = $URLS['USER_SIGNUP'] .'?eventid=' . $eventid;


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
    # We now know this is a legitimate event, so print its title and proceed.
    #---------------------------------------------------------------------------
    $event_title = get_node_title($eventid);

    $all_signups = list_all_signups($eventid);

    if (!$all_signups)
    {
      echon("There are no signups for this event so far.");
      return FALSE;
    }

    $num_signups = count($all_signups);

    $expt_list_url = $URLS['LIST_BY_EXPT'] . '?eventid=' . $eventid;
    $expt_choices_url = $URLS['VIEW_EXPT_LIST'] . '?eventid=' . $eventid;
    $signup_url = $URLS['USER_SIGNUP'] . '?eventid=' . $eventid;

    echon('<h1>' . $event_title . '</h1>');
    echon('<h2>Assignment of experiments</h2>');
    echon('<p>'); 
    echon('  Use this page to assign experiments to demonstrators.  Once they'); 
    echon('  are all assigned, e-mail demonstrators and let them know that');
    echon('  they can view their choices on the ');
    echon('  <a href="' . $signup_url . '">original sign-up page</a>');   
    echon('</p><p>');
    echon('  <a href="' . $expt_list_url . '">This page</a>');
    echon('  is a convenient list of current experiment assignments');
    echon('  sorted by experiments, with highlighting of those without the');
    echon('  correct number of demonstrators assigned, to help this process.');
    echon('</p><p>');
    echon('  You can view demonstrator preferences at');
    echon('  <a href="' . $expt_choices_url . '">'.$expt_choices_url .'</a>');
    echon('  and it may be easiest to copy-paste this page to a spreadsheet');
    echon('  to figure out how you want to do the assignment.');
    echon('</p>');   
    echon('  Note that choices are only saved when you click Submit.');  
    echon('</p>');
    echon('<p><a href="' . get_node_link($eventid) . '">'); 
    echon('  Return to main event page'); 
    echon('</a></p>'); 
    
    if (isset($_POST['changesubmitted']))
    {
      #-------------------------------------------------------------------------
      # Clear out existing selection.
      #-------------------------------------------------------------------------
      $query = 'DELETE FROM '. $TABLES['EXPT_ASSIGN'] . ' WHERE eventid = '
                                                      . (string)$eventid;
      $successful = db_query($query);

      foreach($all_signups as $userid => $status)
      {
        $mornexptid = 0;
        $afterexptid = 0;
        
        if (isset($_POST['morn' . (string)$userid]))
        {
          $mornexptid = $_POST['morn' . (string)$userid];
        }
        
        if (isset($_POST['after' . (string)$userid]))
        {
          $afterexptid = $_POST['after' . (string)$userid];
        }
        
        assign_user_expts($eventid, $userid, $mornexptid, $afterexptid);
      }
    }

    $user_columns = Array('fullname' => 'Name',
                          'subject'  => 'Subject');
                          
    $other_columns = Array('session' => 'Session',
                           'numchoices' => 'Num choices',
                           'mornselect'  => 'Morning Assignment',
                           'morncurrent' => 'Morning Current',
                           'aftselect' => 'Afternoon Assignment',
                           'aftcurrent' => 'Afternoon Current',);

    #---------------------------------------------------------------------------
    # Generate a list of all experiments available in the event.
    #---------------------------------------------------------------------------
    $expt_names = Array();
    $expt_list = get_event_expt_list($eventid);
    
    foreach ($expt_list as $exptid => $dems)
    {
      $expt_names[$exptid] = substr(get_node_title($exptid), 0, 15);
    }
    
    #---------------------------------------------------------------------------
    # In addition to assigning users experiments, we can make 2 more choices.
    # 0 represents no assignment.
    # 1 represents committee stuff (NB: node 1 is not an expt, so this is fine!)
    #---------------------------------------------------------------------------
    $special_options = Array(0);
    $expt_names[0] = "None";
    #$expt_names[1] = "Committee Stuff"
    
    $num_cols = count($user_columns) + count($other_columns);
    
    $current_url = 'http://www.chaosscience.org.uk/committee/events/assignexpts?eventid=' . (string)$eventid;
    
    echon('<form action="' . $current_url . '" method="post">');

    echon('  <input type="hidden" name="changesubmitted" />');
    echon('  <input type="hidden" name="eventid" value="' . $eventid . '"/>');
    echon('  <input type="hidden" name="userid" value="' . $userid . '"/>');
    echon('  <input type="submit" value="Submit changes" />');
    echon('Note that experiment names are trimmed to 15 characters below to');
    echon('make the alignment work better.');
    echon('<table>');

    #---------------------------------------------------------------------------
    # Generate header.
    #---------------------------------------------------------------------------
    echon('  <tr>');

    foreach ($user_columns as $col_name)
    {
      echon('    <th>' . $col_name .'</th>');
    }

    foreach ($other_columns as $col_name)
    {
      echon('    <th>' . $col_name .'</th>');
    }

    echon('  </tr>');
    
    #---------------------------------------------------------------------------
    # Generate main table content.
    #---------------------------------------------------------------------------
    foreach ($all_signups as $userid => $status)
    {
      if ($status == 'withdrawn')
      {
        $font_start_tag = '<font color="grey">';
        $font_end_tag   = '</font>';
      }
      
      echon('  <tr>');

      foreach ($user_columns as $col_key => $col_name)
      {
        echon('    <td>' . $font_start_tag . get_user_detail($userid, $col_key)
                                           . $font_end_tag . '</td>');
      }

      $session_wanted = get_session_wanted($eventid, $userid);

      foreach ($other_columns as $col_key => $col_name)
      {
        if ($col_key == 'session')
        {
          echon('    <td>' . $font_start_tag .
                $session_wanted . $font_end_tag .'</td>');
        }
        elseif ($col_key == 'comments')
        {
          echon('    <td>' . $font_start_tag . get_comments($eventid, $userid)
                                                    . $font_end_tag . '</td>');
        }
      }
      
      $user_expts = get_user_expt_choices($eventid, $userid);
      
      $editlink = 'http://www.chaosscience.org.uk/committee/events/editsignup' .
                                  '?eventid=' . $eventid . '&userid=' . $userid;

      echon('    <td><a href="' . $editlink . '">' .
            $font_start_tag . count($user_expts) . $font_end_tag . '</a></td>');
                                                    
      $assign_options = array_merge($special_options, $user_expts);
      
      $current_expts = get_expt_assignment($eventid, $userid);

      #-------------------------------------------------------------------------
      # Morning experiment assignment.
      #-------------------------------------------------------------------------
      if ($session_wanted != 'Afternoon')
      {
        echon('    <td><select name="morn' . (string)$userid . '">');

        foreach ($assign_options as $exptid)
        {
          $defaultstring = '';

          if ($exptid == $current_expts['mornexptid'])
          {
            $defaultstring = ' selected="selected"';
          }
          
          echon('      <option value="' . (string)$exptid . '"'
                 . $defaultstring . '>' . $expt_names[$exptid] . '</option>');

        }
        echon('    </select></td>');

        echon('    <td>' . $expt_names[$current_expts['mornexptid']] . '</td>');
      }
      else
      {
        echon('    <td></td><td></td>');
      }
      
      #-------------------------------------------------------------------------
      # Afternoon experiment assignment.
      #-------------------------------------------------------------------------
      if ($session_wanted != 'Morning')
      {
        echon('    <td><select name="after' . (string)$userid . '">');

        foreach ($assign_options as $exptid)
        {
          $defaultstring = '';
          
          if ($exptid == $current_expts['afterexptid'])
          {
            $defaultstring = ' selected="selected"';
          }
          
          echon('      <option value="' . (string)$exptid . '"'
                 . $defaultstring . '>' . $expt_names[$exptid] . '</option>');
        }
        
        echon('    </select></td>');

        echon('    <td>' . $expt_names[$current_expts['afterexptid']]
                         . '</td>');
      }
      else
      {
        echon('    <td></td><td></td>');
      }
      
      echon('  </tr>');
    }

    echon('</table>');

  }

  #----------------------------------------------------------------------------
  # START OF MAIN FUNCTION
  #
  # Just calls into the main_event_page_default() function defined above.
  #----------------------------------------------------------------------------
  main_experiment_choices_assign()

?>
