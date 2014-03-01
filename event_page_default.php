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
  function show_current_signups()
  {
    $eventid = arg(1);
    
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
        $font_start_tag = '<font color="grey">';
        $font_end_tag   = '</font>';
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
      }
      
      $editlink = 'http://www.chaosscience.org.uk/committee/events/editsignup' .
                                  '?eventid=' . $eventid . '&userid=' . $userid;
      echon('    <td><a href="' . $editlink . '">Edit</a></td>');

      echon('  </tr>');
    }
    
    echon('</table>');

  }
  
  #----------------------------------------------------------------------------
  # START OF MAIN FUNCTION
  #
  # Just calls into the main() function defined above.
  #---------------------------------------------------------------------------- 
  show_current_signups()

?>