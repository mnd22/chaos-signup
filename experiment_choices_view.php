<?php
 /**
   * The PHP code below generates the default content for an event page, giving
   * the user links to experiment assignment pages etc.  Do not edit it unless
   * you know what you're doing.  Note that if you do know what you're doing
   * with PHP it is safe to edit this text for a single event without breaking
   * other events.
   *
   * @file event_page_default.php
   *
   * @author   Mark Durkee
   * @version  V0.03
   */

  include_once("../signup_system/useful_functions.php");

  /**
   * Displays a list of current signups.
   */
  function main()
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

    $all_signups = list_all_signups($eventid);

    if (!$all_signups)
    {
      echon("There are no signups for this event so far.");
      return FALSE;
    }

    $num_signups = count($all_signups);

    $user_columns = Array('fullname' => 'Name',
                          'mail'     => 'E-mail',
                          'subject'  => 'Subject');
    $other_columns = Array('session'  => 'Session',
                           'comments' => 'Comments');

    $expt_columns = Array();
    $expt_list = get_event_expt_list($eventid);
    
    foreach ($expt_list as $exptid => $dems)
    {
      $expt_columns[$exptid] = get_node_title($exptid);
    }
    
    $num_cols = count($user_columns) + count($other_columns)
                                     + count($expt_columns);

    echon('<h2>Volunteer signups</h2>');

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

    foreach ($expt_columns as $col_name)
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

      foreach ($other_columns as $col_key => $col_name)
      {
        if ($col_key == 'session')
        {
          echon('    <td>' . $font_start_tag .
                get_session_wanted($eventid, $userid) . $font_end_tag .'</td>');
        }
        elseif ($col_key == 'comments')
        {
          echon('    <td>' . $font_start_tag . get_comments($eventid, $userid)
                                                    . $font_end_tag . '</td>');
        }
      }

      $user_expts = get_user_expt_choices($eventid, $userid);

      foreach ($expt_columns as $exptid => $exptname)
      {
        if (in_array($exptid, $user_expts))
        {
          echon('    <td>' . $font_start_tag . '1'
                                           . $font_end_tag . '</td>');
        }
        else
        {
           echon('    <td></td>');
        }
      }

      echon('  </tr>');
    }

    echon('</table>');

  }

  #----------------------------------------------------------------------------
  # START OF MAIN FUNCTION
  #
  # Just calls into the main() function defined above.
  #----------------------------------------------------------------------------
  main()

?>