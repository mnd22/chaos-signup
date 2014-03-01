<?php
 /**
  * This is the main signup page for new users.
  * http://www.chaosscience.org.uk/demonstrator/signup
  *
  * @file user_signup.php
  *
  * @author   Mark Durkee
  * @version  V0.03
  */
  include_once("../signup_system/useful_functions.php");

 /**
  * Main function to construct page content.
  */
  function main()
  {
    #---------------------------------------------------------------------------
    # Import global variables (used as constants) declared in constants.php.
    #---------------------------------------------------------------------------
    global $URLS, $TABLES, $EMAILS, $EXPT_SUBJECTS;

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
        save_user_expt_choices($eventid, $userid);
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
    
    $expts_assigned = get_expt_assignment($eventid, $userid);
    
    if ($expts_assigned['mornexptid'] || $expts_assigned['afterexptid'])
    {
      echon('You have been assigned to the following experiments:<br />');
    }
    
    if ($expts_assigned['mornexptid'])
    {
      echon('Morning: <a href="' . $URLS['BASE'] .  '/node/'
            . (string)$expts_assigned['mornexptid']. '">' .
              get_node_title($expts_assigned['mornexptid']) . '</a><br />');
    }

    if ($expts_assigned['afterexptid'])
    {
      echon('Afternoon: <a href="' . $URLS['BASE'] .  '/node/'
            . (string)$expts_assigned['afterexptid']. '">' .
              get_node_title($expts_assigned['afterexptid']) . '</a><br />');
    }

    #---------------------------------------------------------------------------
    # Now create the form that allows the user to enter updates.
    #
    # Firstly set hidden fields to indicate that a change has been input if we 
    # then go on to click the submit button.              
    #---------------------------------------------------------------------------
    $current_url = $URLS['USER_SIGNUP'] . '?eventid=' . (string)$eventid;
    echon('<form action="' . $current_url . '" method="post">');

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
    echon('      <th colspan=3>Event Availability</th>');
    echon('    </tr>');

    if ($assign_sessions == 'Yes')
    {
      #-------------------------------------------------------------------------
      # This is a CBS-like event, so offer a choice between morning/afternoon.
      #
      # First, work out which box to tick as default if the user has already
      # submitted the form on a previous occasion..
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
      # Session choice is not available yet, though hard to see why anyone 
      # would not want this to be available at the same time as signup!
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
      #-------------------------------------------------------------------------
      echon('    <tr>');
      echon('      <th colspan=3>Experiment choices</th>');
      echon('    </tr>');

      echon('    <tr>');
      echon('      <td colspan=3><p>');
      echon('        Please select the experiments that you would be happy');
      echon('        to demonstrate from the list below.  The more options');
      echon('        that you choose, the more likely you are to get');
      echon('        something that you want.  You don\'t have to restrict');
      echon('        yourself to experiments that are listed under your own');
      echon('        subject, many chemistry/medic experiments are suitable');
      echon('        for biologists, physics experiments for engineers etc.');
      echon('        </p><p>');
      echon('        <b>You MUST choose at least 3 experiments</b>');
      echon('      </td>');
      echon('    </tr>');

      $current_expts = get_user_expt_choices($eventid, $userid);
      
      $expt_options = get_event_expt_list($eventid);

      #-------------------------------------------------------------------------
      # We want the user to be able to sort experiments by subject.  To do this,
      # we create an array that holds the expt_options in this form.  First we 
      # need to initialize an array with the correct constants.
      #-------------------------------------------------------------------------
      $expt_subject_options = Array();

      foreach ($EXPT_SUBJECTS as $subject)
      {
        $expt_subject_options[$subject] = Array();
      }

      foreach ($expt_options as $exptid => $dems_range)
      {
        $subject = get_expt_subject($exptid);

        if (in_array($subject, $EXPT_SUBJECTS))
        {
          $expt_subject_options[$subject][] = $exptid;
        }
      }

      foreach ($EXPT_SUBJECTS as $subject)
      {
        echon('    <tr>');
        echon('      <td colspan=3><b>' . $subject . '</b></td>');
        echon('    </tr>');

        foreach ($expt_subject_options[$subject] as $exptid)
        {
          $expt_title = get_node_title($exptid);
          $expt_intro = get_expt_intro($exptid, 0, FALSE);
          
          $checked_string = '';
          
          if(in_array($exptid, $current_expts))
          {
            $checked_string = 'checked="checked" ';
          }

          echon('    <tr>');
          echon('      <td></td>');
          echon('      <td><input type="checkbox" name="exptlist[]" value="'
                                . $exptid . '" ' . $checked_string . '/></td>');

          echon('      <td><a href="' . $URLS['BASE'] . '/node/' .
          (string)$exptid . '">' . htmlspecialchars($expt_title) . '</a></td>');
          echon('    </tr>');
          echon('    <tr>');
          echon('      <td></td><td></td>');
          echon('      <td>' . htmlspecialchars($expt_intro) . '</td>');
          echon('    </tr>');
        }
      }
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
    
    echon('    <tr>');
    echon('      <th colspan=3>Other Comments</th>');
    echon('    </tr>');
    echon('      <td>Anything else you want to tell us?</td><td></td>');
    echon('      <td>');
    echon('        <input type="text" name="othercomments" size="70" ' .
                         'maxlength="500" value="' . $current_comments . '"/>');
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