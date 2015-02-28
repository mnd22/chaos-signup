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
  function main_event_page_default()
  {
    global $URLS, $TABLES, $EMAILS, $EXPT_SUBJECTS;
    #---------------------------------------------------------------------------
    # This is the default content for the page that defines the event.  We need
    # therefore to work out what page we're on.  The only way of doing this in
    # drupal seems to be the following (note arg is a drupal API function not a
    # standard PHP one).
    #---------------------------------------------------------------------------
    $eventid = arg(1);
    $signup_url = $URLS['USER_SIGNUP'] . '?eventid=' . $eventid;

    $assign_experiments = check_standard_questions($eventid, 'expts');

    echon('<p>');
    echon('  The publicly facing signup form for this page can be found at');
    echon('  <a href="' . $signup_url . '">' . $signup_url . '</a>');
    echon('</p>');

    if ($assign_experiments)
    {
      $expt_choose_url = $URLS['EXPT_CHOICE'] . '?eventid=' . $eventid;
      echon('<p>');
      echon('  The experiment selection for this page can be found at');
      echon('  <a href="' . $expt_choose_url . '">' . $expt_choose_url .'</a>');
      echon('  Use this to pick which experiments you want to make available');
      echon('  for demonstrators to choose, and set limits on people that');
      echon('  can be assigned for each.');
      echon('</p>');
      
      $expt_choices_url = $URLS['VIEW_EXPT_LIST'] . '?eventid=' . $eventid;
      echon('<p>');
      echon('  Having asked demonstrators to return to the');
      echon('  <a href="' . $signup_url . '">original sign-up page</a>');      
      echon('  to choose experiments, you can view their choices at:');
      echon('  <a href="' . $expt_choices_url . '">'.$expt_choices_url .'</a>');
      echon('  and it may be easiest to copy-paste this page to a spreadsheet');
      echon('  to figure out how you want to do the assignment.');
      echon('</p>');   
            
      $expt_assign_url = $URLS['EXPT_ASSIGN'] . '?eventid=' . $eventid;
      echon('<p>');
      echon('  Experiments can be assigned to demonstrators at this page:');
      echon('  <a href="' . $expt_assign_url . '">' . $expt_assign_url .'</a>');
      echon('  Demonstrators can then see what they hve been assigned on the');
      echon('  <a href="' . $signup_url . '">original sign-up page</a>.');
      echon('</p>');

      $expt_list_url = $URLS['LIST_BY_EXPT'] . '?eventid=' . $eventid;
      echon('<p>');
      echon('  The following page is a convenient list of current experiment');
      echon('  assignment sorted by experiments, with highlighting of those');
      echon('  without the correct number of demonstrators assigned:');
      echon('  <a href="' . $expt_list_url . '">' . $expt_list_url . '</a>.');
      echon('</p>');
    }

    $all_signups = list_all_signups($eventid);

    if (!$all_signups)
    {
      echon("There are no signups for this event so far.");
      return FALSE;
    }

    $num_signups = count($all_signups);

    $user_columns = Array('fullname' => 'Name',
                          'mail'     => 'E-mail',
                          'college'  => 'College',
                          'subject'  => 'Subject',
                          'yeargroup' => 'Year');
    $other_columns = Array('session'  => 'Session',
                           'comments' => 'Comments');
                           
    if ($assign_experiments)
    {
      $other_columns['numexpts'] = 'Expts picked';
    }

    $num_cols = count($user_columns) + count($other_columns);

    echon('<h2>Volunteer signups</h2>');
    echon('There have been <b>' . (string)$num_signups . '</b> so far.');

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

    echon('    <th></th>');
    echon('  </tr>');

    #---------------------------------------------------------------------------
    # Generate main table content.
    #---------------------------------------------------------------------------
    foreach ($all_signups as $userid => $status)
    {
      if ($status == 'withdrawn')
      {
        $font_start_tag = '<del>';
        $font_end_tag   = '</del>';
      }

      echon('  <tr>');

      foreach ($user_columns as $col_key => $col_name)
      {
        echon('    <td>' . $font_start_tag . get_user_detail($userid, $col_key) . $font_end_tag . '</td>');
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
        elseif ($col_key == 'numexpts')
        {
          $numexpts = count(get_user_expt_choices($eventid, $userid));
          echon('    <td>' . $font_start_tag . $numexpts
                                                    . $font_end_tag . '</td>');
        }
      }

      $editlink = 'http://www.chaosscience.org.uk/committee/events/editsignup' .
                                  '?eventid=' . $eventid . '&userid=' . $userid;
      echon('    <td><a href="' . $editlink . '">Edit</a></td>');

      echon('  </tr>');
    }

    echon('</table>');

  }

  #-----------------------------------------------------------------------------
  # START OF MAIN FUNCTION
  #
  # Just calls into the main_event_page_default() function defined above.
  # This php file is intended to be pasted into a drupal page.
  #-----------------------------------------------------------------------------
  main_event_page_default()
?>
