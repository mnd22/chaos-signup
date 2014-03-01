<?php
  /**
   * This is the main signup page for new users.
   * http://www.chaosscience.org.uk/demonstrator/signup
   *
   * @file user_signup.php
   *
   * @author   Mark Durkee
   * @version  V0.02
   */
  include_once("../signup_system/useful_functions.php");

  /**
   * Main function to construct page content.
   */
  function main()
  {
    #---------------------------------------------------------------------------
    # Import global variables declared in useful_functions.
    #---------------------------------------------------------------------------
    global $URLS, $TABLES;
    
    $EMAILS = Array('WEB'     => "webmaster@chaosscience.org.uk",
                    'CONTACT' => "contact@chaosscience.org.uk");
    
    if (!isset($_GET['eventid']))
    {
      echon("<p>");
      echon("  This page allows you to sign up for an event, but you've");
      echon("  somehow managed to get here without specifying which event");
      echon("  you have signed up for.  If you clicked a link to get here");
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
    
    if (!is_event($eventid))
    {
      echon("<p>");
      echon("  This page allows you to sign up for an event, but you've");
      echon("  somehow managed to specify an invalid event code.");
      echon("  If you clicked a link to get here, this might be a bug in the");
      echon("  website, it would be helpful if you could report this to " .
                                                          $EMAILS['WEB'] . ".");
      echon("</p>");
      echon("<p>");
      echon("  <a href='" . $URLS['EVENT_LIST']
                             . "'>Click here to go to the list of events.</a>");
      echon("</p>");
      return FALSE;
    }
    
    $event_title = get_node_title($eventid);
    $event_thanks_message = get_thanks_message($eventid);
    echon('<h1>' . $event_title . '</h1>');
    
    $userid = current_user();
    
    if ($userid == 0)
    {
      #-------------------------------------------------------------------------
      # User is anonymous, so print message telling user to log in.
      # The login_url and register_url we give them will send them back here.
      #-------------------------------------------------------------------------    
      $login_url = $URLS['LOGIN']
               . '?destination=demonstrator/signup?eventid=' . (string)$eventid;
      $register_url = $URLS['REGISTER']
               . '?destination=demonstrator/signup?eventid=' . (string)$eventid;
               
      echon("<p>");
      echon(" Thanks for showing an interest in helping with a CHaOS event.");
      echon(" The first stage in signing up is to <a href='" . $register_url
                                                   . "'>create an account</a>");
      echon(" on website (or <a href='" . $login_url .
                                        "'>log into an existing account</a>).");
      echon(" You can then return to this page to sign up for the event.");
      echon("</p>");
      return FALSE;
    }

    #---------------------------------------------------------------------------
    # These variables check whether or not we want to ask various standard types
    # of questions.
    #---------------------------------------------------------------------------
    $assign_sessions = check_standard_questions($eventid, 'session');
    $assign_experiments = check_standard_questions($eventid, 'expts');
    $assign_dates = check_standard_questions($eventid, 'dates');
    $extra_questions = 'No';
    
    $current_time = time();
    
    if (isset($_POST['changesubmitted']))
    {
      #-------------------------------------------------------------------------
      # The user has just submitted a change, so save the change in the database
      # and inform them that it has been submitted.
      #-------------------------------------------------------------------------
      save_signup_to_db($current_time, $eventid, $userid);

      if ($assign_sessions == 'Yes' and isset($_POST['sessions']))
      {
        save_sessions($eventid, $userid, $_POST['sessions'], "");
      }
      
      if ($assign_experiments == 'Yes')
      {
        echon('ERROR: Attempted to assign expts, but that is not implemented');
      }
      
      if ($assign_dates == 'Yes')
      {
        echon('ERROR: Attempted to assign dates, but that is not implemented');
      }
      
      if (isset($_POST['othercomments']))
      {
        save_comments($eventid, $userid, $_POST['othercomments']);
      }
      
      echon("<table bgcolor='#00FFCC'><tr><td>");
      echon($event_thanks_message);
      echon("</td></tr></table>");
    }
    elseif (signup_exists($eventid, $userid))
    {
      echon("<table><tr><td>");
      echon("  You've already signed up for this event.  You can edit your ");
      echon("  signup by making changes in the form below. ");
      echon("</td></tr></table>");
    }

    #---------------------------------------------------------------------------
    # Now create the form that allows the user to enter updates.
    #
    # Firstly set hidden fields to indicate that a change has been input if we 
    # then go on to click the submit button.              
    #---------------------------------------------------------------------------
    echon('<form action="' . $URLS['USER_SIGNUP'] . '?eventid=' . $eventid
                                                          . '" method="post">');
    echon('  <input type="hidden" name="changesubmitted" />');
    echon('  <input type="hidden" name="eventid" value="' . $eventid . '"/>');
    echon('  <input type="hidden" name="userid" value="' . $userid . '"/>');

    $profile_edit_url = $BASE_URL . '/user/' . (string)$userid
                                               . '/edit/Personal%20Information';
   
    echon('  <table>');
    echon('    <tr>');
    echon('      <th colspan=3>Personal Information</th>');
    echon('    </tr><tr>');
    echon('      <td>Name</td><td></td>');
    echon('      <td>' . get_user_detail($userid, 'fullname') . '</td>');
    echon('    </tr><tr>');
    echon('      <td>E-mail</td><td></td>');
    echon('      <td>' . get_user_detail($userid, 'mail') . '</td>');
    echon('    </tr><tr>');
    echon('      <td>College</td><td></td>');
    echon('      <td>' . get_user_detail($userid, 'college') . '</td>');
    echon('    </tr><tr>');
    echon('      <td>Subject</td><td></td>');
    echon('      <td>' . get_user_detail($userid, 'subject') . '</td>');
    echon('    </tr><tr>');
    echon('      <td>Year</td><td></td>');
    echon('      <td>' . get_user_detail($userid, 'yeargroup') . '</td>');
    echon('    </tr><tr><td colspan=3><i>');
    echon('      If any of the information above is incorrect/incomplete/out');
    echon('      of date, you can change it by <a href="' . $profile_edit_url .
                                             '">editing your user profile</a>');
    echon('      (either before or after filling in the rest of the form).');
    echon('    </i></td></tr>');
    echon('  </table>');

    echon('  <table>');
    echon('    <tr>');
    echon('      <th colspan=3>Event Information</th>');
    echon('    </tr>');

    if ($assign_sessions == 'Yes')
    {
      #-------------------------------------------------------------------------
      # Work out which box to tick as default, if the user has already submitted
      # form.
      #-------------------------------------------------------------------------
      $current_session_wanted = get_session_wanted($eventid, $userid);
      $morning_checked = "";
      $afternoon_checked = "";
      $both_checked = "";
      $either_checked = "";
      
      switch ($current_session_wanted)
      {
        case "Morning":
          $morning_checked = 'checked ="checked" ';
          break;
        case "Afternoon":
          $afternoon_checked = 'checked ="checked" ';
          break;
        case "Either":
          $either_checked = 'checked ="checked" ';
          break;
        case "Both":
          $both_checked = 'checked ="checked" ';
        	break;
      }

      #-------------------------------------------------------------------------
      # This is a CBS-like event, so offer a choice between morning/afternoon.
      #-------------------------------------------------------------------------
      echon('    <tr>');
      echon('      <td>Sessions available</td><td></td><td>');
      echon('        <input type="radio" name="sessions" value="Morning" ' .
                                         $morning_checked . '/> Morning<br />');
      echon('        <input type="radio" name="sessions" value="Afternoon" ' .
                                     $afternoon_checked . '/> Afternoon<br />');
      echon('        <input type="radio" name="sessions" value="Either" ' .
                                           $either_checked . '/> Either<br />');
      echon('        <input type="radio" name="sessions" value="Both" ' .
                                               $both_checked . '/> Both<br />');
      echon('      </td>');
      echon('    </tr><tr><td colspan=3><i>');
      echon("      Session times are normally:<br />");
      echon("      Morning: 9.30am-1.30pm, Afternoon: 12.30pm - 5pm<br />");
      echon("      Don't worry, you'll get a chance to stop for a");
      echon("      tea-break (or <b>FREE</b> lunch if you're here all day).");
      echon('    </i></td></tr>');
    }
    elseif ($assign_sessions == 'Later')
    {
      #-------------------------------------------------------------------------
      # Session choice is not available yet, though hard to see why any
      #-------------------------------------------------------------------------
      echon('    <tr><td colspan=3><i>');
      echon("      At a later date you'll be able to choose which sessions");
      echon("      you would like to help with, we'll e-mail you when this");
      echon("      choice becomes available");
      echon('    </i></td></tr>');
    }
    
    if ($assign_experiments == 'Yes')
    {
      #-------------------------------------------------------------------------
      # Let the user make a choice of experiments.
      # This function does not yet work, but will be implemented soon.
      #-------------------------------------------------------------------------
      echon("ERROR: Experiment assignment function is not yet functional");
      #-------------------------------------------------------------------------
      # If we're giving demonstrators an experiment choice then display the
      # options here.
      #---------------------------------------------------------------------------
      #$query = "SELECT nid, title FROM drupal_node WHERE type = 'experiment'";
      #$query_result = db_query($query);
      #while ($row = db_fetch_array($query_result))
      #{
      #  echon '    <tr><td>';
      #  echon '      <a href="' . $BASE_URL . '/node/'
      #                              . $row['nid'] . '">' . $row['title'] . "</a>";
      #  echon '    </td><td>';
      #  echon '      ';
      #  echon '    </td></tr>';
      #}
    }
    elseif ($assign_experiments == 'Later')
    {
      echon('    <tr><td colspan=3><i>');
      echon("      At a later date you'll be able to choose which experiments");
      echon("      you would like to demonstrate, we'll e-mail you when this");
      echon("      choice becomes available.");
      echon("      If you want to take an advance look you can <a href='" .
                                                    $URLS['EXPT_LIST'] . "'> ");
      echon("      see our full list of experiments here</a>.");
      echon('    </i></td></tr>');
    }

    if ($assign_dates == 'Yes')
    {
      #-------------------------------------------------------------------------
      # This is a Roadshow-like event, so offer a choice of dates.
      # This function does not yet work.
      #-------------------------------------------------------------------------
      echon("ERROR: Date assignment function is not yet functional");
    }
    elseif ($assign_dates == 'Later')
    {
      echon("ERROR: Date assignment function is not yet functional");
    }

    #---------------------------------------------------------------------------
    # Finally, we always allow the user to give us any other comments (of up to
    # 500 characters).
    #---------------------------------------------------------------------------
    $current_comments = "";

    if (comment_exists($eventid, $userid))
    {
      $current_comments = 'value="' . get_comments($eventid, $userid) . '" ';
    }

    echon('      <td>Other comments</td><td></td>');
    echon('      <td>');
    echon('        <input type="text" name="othercomments" size="70" ' .
                                 'maxlength="500" ' . $current_comments . '/>');
    echon('      </td>');
    echon('    </tr>');
    echon('  </table>');
    
    #---------------------------------------------------------------------------
    # Create the submit button and end the form.
    #--------------------------------------------------------------------------- 
    echon('  <input type="submit" value="Submit form" />');
    echon('</form>');
  }  
    
  #----------------------------------------------------------------------------
  # START OF MAIN FUNCTION
  #
  # Just calls into the main() function defined above.
  #---------------------------------------------------------------------------- 
  main();
?>