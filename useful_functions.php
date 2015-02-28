<?php
  /** @file useful_functions.php
   *
   * @author   Mark Durkee
   * @version  V0.03
   */

  include_once("../signup_system/constants.php");

  /**
   * Adds an experiment to the list of expts chosen by a demonstrator for
   * the specified event.
   *
   * @param $eventid    The node ID of the event.
   * @param $userid     The user's ID.
   * @param $exptid     The node ID of the experiment
   *
   * @returns Boolean   TRUE if successful, FALSE otherwise.
   */
  function add_expt_choice_to_event($eventid, $userid, $exptid)
  {
    global $TABLES;

    #--------------------------------------------------------------------------
    # Check whether the experiment and event IDs given correspond to actual
    # experiments and events.  If not, do nothing.
    #--------------------------------------------------------------------------
    if (is_experiment($exptid) && is_event($eventid))
    {
      $query = "INSERT INTO " . $TABLES['EXPT_CHOICE']
                      . " (eventid, userid, exptid) VALUES"
                      . "('" . $eventid .  "', '" . $userid . "', '"
                                                  . $exptid . "')";
      $successful = db_query($query);
    }
    else
    {
      $successful = FALSE;
    }

    return $successful;
  }

  /**
   * Checks if we are asking various standard question types for this event.
   * Can only be used to check answers to Yes/No/Later questions.
   *
   * @param $nid         The node ID.
   * @param $question    The field name of the question, must lie in the
   *                     $known_questions array defined below.
   *
   * @return String      "Yes", "No" or "Later" as appropriate.  False if we've
   *                     passed invalid parameters.
   */
  function check_standard_questions($nid, $question)
  {
    global $TABLES;

    $known_questions = Array('session'  => 'field_event_session_assign_value',
                             'expts'    => 'field_event_exptassign_value',
                             'dates'    => 'field_dateassignment_value');

    if (!is_event($nid))
    {
      #-------------------------------------------------------------------------
      # If this node is not an event, we're not going to get very far, so give
      # up straight away.
      #-------------------------------------------------------------------------
      return FALSE;
    }

    if (array_key_exists($question, $known_questions))
    {
      $field = $known_questions[$question];
      $latest_ver = get_latest_revision($nid);
      $query = 'SELECT ' . $field . ' FROM ' . $TABLES['SIGNUP_EVENT']
                  . ' WHERE nid="' . $nid . '" AND vid = "' . $latest_ver . '"';

      $query_result = db_query($query);
      $row = db_fetch_array($query_result);

      if (isset($row[$field]))
      {
        return $row[$field];
      }
      else
      {
        return FALSE;
      }
    }
    else
    {
      return FALSE;
    }
  }

  /**
   * Returns uid (which is an integer) for currently logged in user.
   *
   * @return Int   User ID of current user.  Note that uid = 0 indicates an
   *               anonymous user.
   */
  function current_user()
  {  
    global $user;
    
    if ($user->uid)
    {
      return $user->uid;
    }
    else
    {
      return 0;
    }
  }

  /**
   * Extracts a user's additional comments for a given event from the database.
   *
   * @param  $eventid   The event we're looking for the questions from.
   * @param  $userid        The user ID of the user.
   *
   * @return String      The user's additional comments, or a blank string if
   *                     none.
   */
  function get_comments($eventid, $userid)
  {
    global $TABLES;
    
    if (!signup_exists($eventid, $userid))
    {
      return "";
    }
    
    $query = "SELECT comment FROM " . $TABLES['COMMENTS'] .
           " WHERE eventid = '" . $eventid . "' AND userid = '" . $userid . "'";
    $query_result = db_query($query);
    $row = db_fetch_array($query_result);

    if (isset($row['comment']))
    {
      return $row['comment'];
    }
    else
    {
      return "";
    }

  }
  
  /**
   * Check if a comment row exists
   *
   * @param  $eventid   The event we're looking for the questions from.
   * @param  $userid     The user ID of the user.
   *
   * @return Boolean     TRUE if an existing comment exists, else FALSE
   */
  function comment_exists($eventid, $userid)
  {
    global $TABLES;

    if (!signup_exists($eventid, $userid))
    {
      return FALSE;
    }

    $query = "SELECT comment FROM " . $TABLES['COMMENTS'] .
           " WHERE eventid = '" . $eventid . "' AND userid = '" . $userid . "'";
    $result = db_result(db_query($query));

    if ($result->num_rows > 0)
    {
      return TRUE;
    }
    else
    {
      return FALSE;
    }
  }
  
  /**
   * Extracts a list of questions to ask for a given event from the database.
   *
   * @param  $eventid   The event we're looking for the questions from.
   *
   * @todo Implement
   */
  function get_questions($eventid)
  {
    global $TABLES;
    if (is_event($eventid))
    {
      $query = "SELECT * FROM " . $TABLES['QUESTIONS'] . " WHERE eventid = "
                                                                    . $eventid;
      $query_result = db_query($query);
      $row = db_fetch_array($query_result);

      return True;
    }
    else
    {
      return False;
    }

  }
  
  /**
   * Gets the current status of a signup.
   *
   * @param  $eventid    The node ID of the event.
   * @param  $userid     The user ID of the user.
   *
   * @return String      String representing the status, or FALSE if not set.
   */
  function get_signup_status($eventid, $userid)
  {
    global $TABLES;
    $query = 'SELECT status FROM ' . $TABLES['VOLUNTEERS'] .
                          ' WHERE userid="' . $userid .
                          '"AND eventid="' . $eventid . '"';
    $result = db_query($query);
    $row = db_fetch_array($query_result);

    if (isset($row['status']))
    {
      return $row['status'];
    }
    else
    {
      return FALSE;
    }
  }

  /**
   * Prints input string followed by newline.  Good for creating nice HTML.
   *
   * @param  $echo_string   The string to echo.
   * @return None
   */
  function echon($echo_string)
  {
     echo $echo_string;
     echo "\n";
  }
  
  /**
   * Gets the node type of a node with the given ID.
   *
   * @param $nid   The node ID.
   *
   * @return String The node type as a string, or FALSE if it fails to find it.
   */
  function get_node_type($nid)
  {
    $latest_ver = get_latest_revision($nid);
    
    $query = 'SELECT type FROM drupal_node WHERE nid = "' . $nid .
                                           '" AND vid = "' . $latest_ver . '"';
    
    $query_result = db_query($query);
    $row = db_fetch_array($query_result);
    
    if (isset($row['type']))
    {
      #-------------------------------------------------------------------------
      # Looks like query has succeeded, so return the type as required (note
      # that query can only ever return one row, as nid is the unique node 
      # identifier in drupal).
      #-------------------------------------------------------------------------
      return $row['type'];
    }
    else
    {
      return FALSE;
    }
  }
  

  /**
   * Get the revision ID for the latest revision of this node.
   *
   * @param $nid   Node ID.
   *
   * @return Int  Version ID.
   */
  function get_latest_revision($nid)
  {
    $query = 'SELECT MAX(vid) AS vid FROM drupal_node_revisions WHERE nid = "'
                                                                   . $nid . '"';
    $query_result = db_query($query);
    $row = db_fetch_array($query_result);
    
    if (isset($row['vid']))
    {
      return $row['vid'];
    }
    else
    {
      return FALSE;
    }
  }

  /**
   * Get session assigned to user for this event
   *
   * @param $eventid  Event ID
   * @param $userid      User ID
   *
   * @return String Session wanted, or empty string if no record exists.
   */
  function get_session_assigned($eventid, $userid)
  {
    global $TABLES;

    if (!signup_exists($eventid, $userid))
    {
      return "";
    }

    $query = "SELECT assigned FROM " . $TABLES['SESSIONS'] .
          " WHERE eventid = '" . $eventid . "' AND userid = '" . $userid . "'";
    $query_result = db_query($query);
    $row = db_fetch_array($query_result);

    if (isset($row['assigned']))
    {
      return $row['assigned'];
    }
    else
    {
      return "";
    }
  }
  
 /**
  * Get session wanted by user for this event
  *
  * @param $eventid   Event ID
  * @param $userid    User ID
  *
  * @return String Session wanted, or empty string if no record exists.
  */
  function get_session_wanted($eventid, $userid)
  {
    global $TABLES;

    if (!signup_exists($eventid, $userid))
    {
      return "";
    }

    $query = "SELECT wanted FROM " . $TABLES['SESSIONS'] .
          " WHERE eventid = '" . $eventid . "' AND userid = '" . $userid . "'";
    $query_result = db_query($query);
    $row = db_fetch_array($query_result);

    if (isset($row['wanted']))
    {
      return $row['wanted'];
    }
    else
    {
      return "";
    }
  }
  
  /**
   * Function for extracting user information safely, either from the user table
   * or the user profile table as appropriate.
   *
   * @param $userid  User ID.
   * @param $key     The keyname for the property being extracted.  Must be one
   *                 of those names listed in $profile_keys or $user_keys below.
   * @return String  The value of the field if it exists.
   *                 An empty string if the field does not exist.
   */
  function get_user_detail($userid, $key)
  {
    global $EMAILS;

    $user_keys = array('uid', 'name', 'mail', 'hostname');
    $profile_keys = array('fullname'  => '1',
                          'college'   => '3',
                          'subject'   => '9',
                          'yeargroup' => '20');

    $col_name = "";

    if (in_array($key, $user_keys))
    {
      $query = "SELECT " . $key . " FROM drupal_users WHERE uid=" . $userid;
      $col_name = $key;
    }
    elseif (array_key_exists($key, $profile_keys))
    {
      $fid = $profile_keys[$key];
      $query = "SELECT value FROM drupal_profile_values WHERE fid=" . $fid
                                                        . " AND uid=" . $userid;
      $col_name = 'value';
    }
    else
    {
      echon("An error has occurred.  Please report this to " . $EMAILS['WEB']);
    }

    $result = db_query($query);
    $row = db_fetch_array($result);
    $return_value = $row[$col_name];

    #---------------------------------------------------------------------------
    # Check error cases.
    #---------------------------------------------------------------------------
    if (!isset($return_value) or !$return_value)
    {
      #-------------------------------------------------------------------------
      # Either the value has not been set for some reason (eg call to
      # DB failed) or there is no row in the DB with this value.  In
      # which case, we return an empty string.
      #-------------------------------------------------------------------------
      return "";
    }
    elseif (db_fetch_array($result))
    {
      #-------------------------------------------------------------------------
      # There is a second row with the same field and user IDs.  This
      # means that something has gone wrong in the DB, so return an error.
      #-------------------------------------------------------------------------
      return "ERROR: Multiple entries in DB";
    }
    else
    {
      #-------------------------------------------------------------------------
      # Success, return the value that we've found
      #-------------------------------------------------------------------------
      return $return_value;
    }
  }

 /**
  * Gets the user's current experiment choice from the DB.
  *
  * @param $eventid    The node ID of the event.
  * @param $userid     The user's ID.
  *
  * @returns Array   Simple list of experiment IDs, empty if none known.
  */
  function get_user_expt_choices($eventid, $userid)
  {
    global $TABLES;

    $query = 'SELECT exptid FROM '. $TABLES['EXPT_CHOICE'] . ' WHERE userid="'
                 . (string)$userid . '" AND eventid="' . (string)$eventid . '"';

    $query_result = db_query($query);

    $output_array = Array();

    while ($row = db_fetch_array($query_result))
    {
      if (isset($row['exptid']))
      {
        $output_array[] = $row['exptid'];
      }
    }

    return $output_array;
  }
  
 /**
  * Returns a link to the node with the given ID.  No checking is performed on
  * whether the node link is valid.
  *
  * @param $nid   The node ID.
  *
  * @return String  The node link.
  */
  function get_node_link($nid)
  {
    global $URLS;
    
    return $URLS['BASE'] . '/node/' . $eventid;
  }
  
 /**
  * Gets the node title of a node with the given ID.
  *
  * @param $nid   The node ID.
  *
  * @return String  The node title, or FALSE if it fails to find it.
  */
  function get_node_title($nid)
  {
    $latest_ver = get_latest_revision($nid);

    $query = 'SELECT title FROM {node} WHERE nid = "' . $nid .
                                           '" AND vid = "' . $latest_ver . '"';
    $query_result = db_query($query);
    $row = db_fetch_array($query_result);

    if (isset($row['title']))
    {
      #-------------------------------------------------------------------------
      # Looks like query has succeeded, so return the title as required (note
      # that query can only ever return one row, as nid is the unique node
      # identifier in drupal).
      #-------------------------------------------------------------------------
      return $row['title'];
    }
    else
    {
      return FALSE;
    }
  }

  /**
   * Gets the thank you message for an event.
   *
   * @param $nid         The node ID.
   *
   * @return String      The thanks message as a string, or FALSE if it fails
   *                     to find it.
   */
  function get_thanks_message($nid)
  {
    global $TABLES;
    
    if (!is_event($nid))
    {
      #-------------------------------------------------------------------------
      # If this node is not an event, we're not going to get very far, so give
      # up straight away.
      #-------------------------------------------------------------------------
      return FALSE;
    }
    
    $query = "SELECT nid, field_signup_thanks_value " . 
             "FROM " . $TABLES['SIGNUP_EVENT'] . " WHERE nid = '" . $nid . "'";
    $query_result = db_query($query);
    $row = db_fetch_array($query_result);
    
    if (isset($row['nid']) && isset($row['field_signup_thanks_value'])
                           && $row['nid'] == $nid)
    {
      #-------------------------------------------------------------------------
      # Looks like query has succeeded, so return the title as required (note
      # that query can only ever return one row.
      #-------------------------------------------------------------------------
      return $row['field_signup_thanks_value'];
    }
    else
    {
      return FALSE;
    }
  }
  
 /**
  * Checks if user with $userid supplied is a committee member.
  *
  * @todo Implement
  */
  function is_committee($userid)
  {
    #  global $user;
    #  if (in_array('committee', $user->roles))
    #  {
    #    echo "Current user is a committee member";
    #  }
    #  else
    #  {
    #    echo "Current user is not a committee member";
    #  }
    #  echo "<br>";
    #  print_r($user->roles);
  }

  /**
   * Checks whether a node ID corresponds to a "signup event".
   *
   * @param $nid         The node ID.
   *
   * @return Boolean     TRUE or FALSE, as appropriate.
   */
  function is_event($nid)
  {
    if (get_node_type($nid) == "signup_event")
    {
      return TRUE;
    }
    else
    {
      return FALSE;
    }
  }

  /**
   * Checks whether a node ID corresponds to an "experiment".
   *
   * @param $nid         The node ID.
   *
   * @return Boolean     TRUE or FALSE, as appropriate.
   */
  function is_experiment($nid)
  {
    if (get_node_type($nid) == "experiment")
    {
      return TRUE;
    }
    else
    {
      return FALSE;
    }
  }

 /**
  * Add this signup to the database.
  *
  * @param $current_time   The current time in seconds past the epoch.
  * @param $eventid        The node ID of the current event.
  * @param $userid         The user ID of the user signing up for it.
  * @param $status         The current status of the signup.
  *                        Defaults to "new".
  *
  * @return Boolean        True if successful, False otherwise.
  * @todo   Add better error handling.
  */
  function save_signup_to_db($current_time,
                             $eventid,
                             $userid,
                             $status = "new")
  {
    global $TABLES;

    if (!is_event($eventid) or !user_exists($userid))
    {
      #-------------------------------------------------------------------------
      # This function has been called with an invalid event or user (should
      # never happen).
      #-------------------------------------------------------------------------
      echon("No changes submitted");
      return False;
    }
    
    #---------------------------------------------------------------------------
    # The allowed signup statuses are as follows:
    #  new        = Signed up, no changes
    #  edited     = Sign-up has been edited, but not yet assigned.
    #  assigned   = Demonstrator has been assigned to the event.
    #  rejected   = Demonstrator not assigned to the event, e.g no space left.
    #  withdrawn  = Demonstrator pulled out.
    #  reassigned = Demonstrator's assignment has been changed.
    #---------------------------------------------------------------------------

    $time_string = (string)$current_time;

    if (signup_exists($eventid, $userid))
    {
      #-------------------------------------------------------------------------
      # Update an existing signup.  There are two things to edit here:
      # - The date changed.
      # - The current status.
      #-------------------------------------------------------------------------
      db_query('UPDATE ' . $TABLES['VOLUNTEERS'] .
               ' SET datechanged="' . $time_string . ', status="' . $status .
              '" WHERE userid="' . $userid . '"AND eventid="' . $eventid . '"');
    }
    else
    {
      #-------------------------------------------------------------------------
      # Create a new signup
      #-------------------------------------------------------------------------
      db_query('INSERT INTO ' . $TABLES['VOLUNTEERS'] .
                      ' (eventid, userid, datecreated, datechanged, status) ' .
               ' VALUES (' . $eventid . ', ' . $userid . ', ' .
                    $time_string . ', ' . $time_string . ', "' .$status . '")');
    }

    return True;
  }

 /**
  * Store demonstrators' other comments in the DB.
  *
  * @param $eventid  The event ID
  * @param $userid   The user ID to store
  * @param $comment  The comment to store.
  *
  * @return Boolean  True if (we think we are) successful, else False.
  */
  function save_comments($eventid, $userid, $comment)
  {
    global $TABLES;

    if (!signup_exists($eventid, $userid))
    {
      #-------------------------------------------------------------------------
      # This function has been called with an invalid event or user (should
      # never happen).
      #-------------------------------------------------------------------------
      echon("ERROR: No such event, could not save additional comments.");
      return False;
    }

    $query = 'DELETE FROM ' . $TABLES['COMMENTS'] .
               ' WHERE userid="' . $userid . '" AND eventid="' . $eventid . '"';
    db_query($query);

    db_query('INSERT INTO ' . $TABLES['COMMENTS'] .
                      ' (eventid, userid, comment)' .
                      ' VALUES ("' . $eventid . '", "' . $userid . '", "%s")',
               $comment);

    return True;
  }
  
 /**
  * Store details about sessions wanted or assigned in the database.
  *
  * @param $eventid  The event ID
  * @param $userid   The user ID to store
  * @param $wanted   The session(s) requested by the user.
  * @param $assigned The session(s) that the user has been assigned to.
  *
  * @return Boolean  True if (we think we are) successful, else False.
  */
  function save_sessions($eventid, $userid, $wanted, $assigned)
  {
    global $TABLES;

    if (!signup_exists($eventid, $userid))
    {
      #-------------------------------------------------------------------------
      # This function has been called with an invalid event or user (should
      # never happen).
      #-------------------------------------------------------------------------
      return False;
    }

    if (get_session_wanted($eventid, $userid))
    {
      #-------------------------------------------------------------------------
      # Update an existing comment
      #-------------------------------------------------------------------------
      db_query('UPDATE ' . $TABLES['SESSIONS'] .
               ' SET wanted="' . $wanted . '" WHERE userid="' . $userid .
                                            '" AND eventid="' . $eventid . '"');
      db_query('UPDATE ' . $TABLES['SESSIONS'] .
               ' SET assigned="' . $assigned . '" WHERE userid="' . $userid .
                                            '" AND eventid="' . $eventid . '"');
    }
    else
    {
      #-------------------------------------------------------------------------
      # Create a new comment.
      #-------------------------------------------------------------------------
      db_query('INSERT INTO ' . $TABLES['SESSIONS'] .
                      ' (eventid, userid, wanted, assigned) ' .
                      ' VALUES ("' . $eventid . '", "' . $userid . '", "'
                                    . $wanted . '", "' . $assigned . '")');
    }

    return True;
  }

 /**
  * Checks whether a sign-up for this user at this event already exists in the
  * database.
  *
  * @param  $eventid    The node ID of the event.
  * @param  $userid     The user ID of the user.
  *
  * @return Boolean     True or False as appropriate.
  */
  function signup_exists($eventid, $userid)
  {
    global $TABLES;
    $query = 'SELECT userid, eventid FROM ' . $TABLES['VOLUNTEERS'] .
                                ' WHERE userid="' . $userid .
                                '"AND eventid="' . $eventid . '"';
    $result = db_query($query);

    if ($result->num_rows > 0)
    {
      return TRUE;
    }
    else
    {
      return FALSE;
    }
  }
  
 /**
  * Check whether a user actually exists.
  *
  * @param  userid     User ID to check
  *
  * @return Boolean TRUE or FALSE as appropriate
  */
  function user_exists($userid)
  {
    $query = 'SELECT uid FROM drupal_users WHERE uid=' . $userid;
    $result = db_query($query);

    if ($result->num_rows > 0)
    {
      return TRUE;
    }
    else
    {
      return FALSE;
    }
  }
  
 /**
  * Gets a list of user IDs for all signups for an event.
  *
  * @param $eventid  The ID of the event we're interested in.
  *
  * @returns Array of form userid => signup status.
  */
  function list_all_signups($eventid)
  {
    global $TABLES;
    $query = 'SELECT userid, status FROM ' . $TABLES['VOLUNTEERS'] .
                                            ' WHERE eventid="' . $eventid . '"';
    $result = db_query($query);

    $output_array = Array();

    if ($result->num_rows == 0)
    {
      #-------------------------------------------------------------------------
      # No-one has signed up for this event yet.
      #-------------------------------------------------------------------------
      return FALSE;
    }

    while ($row = db_fetch_array($result))
    {
      #-------------------------------------------------------------------------
      # Add user ID to the list.
      #-------------------------------------------------------------------------
      $output_array[$row['userid']] = $row['status'];
    }

    return $output_array;
  }
 /**
  * Gets a list of experiments that are available for selection for this event.
  * Note that this has been already set by the responsible committee member by
  * choosing from the choose_experiments page.
  *
  * @param $eventid   The event in question.
  * @returns Array    Format Array(exptid => Array(mindems, maxdems)).
  *                   Empty array if no expts selected.
  */
  function get_event_expt_list($eventid)
  {
    global $TABLES;

    $output_array = Array();

    $query = 'SELECT * FROM ' . $TABLES['EXPERIMENTS']
                   . ' WHERE eventid="' . $eventid . '"';
    $query_result = db_query($query);

    while ($row = db_fetch_array($query_result))
    {
      if (isset($row['exptid']) and isset($row['mindems']) 
                                and isset($row['maxdems']))
      {
        $output_array[$row['exptid']] = Array('mindems' => $row['mindems'],
                                              'maxdems' => $row['maxdems']);
      }

    }

    return $output_array;

  }

 /**
  * Gets the intro text for the experiment specified.
  *
  * @param $exptid    The experiment to check.
  * @param $versionid If an experiment has been updated, multiple versions of
  *                   this intro text will exist in the database.  Must make 
  *                   sure that the most recent set is there.
  * @param $verifyid  Boolean determining whether we should verify that the
  *                   exptid is a legitimate experiment.  Defaults to TRUE; we
  *                   might want to set it to FALSE to avoid additional
  *                   processing power being used if verification happens
  *                   before the function is called.
  *
  * @returns String   The intro, or empty string if it can't find it.
  */
  function get_expt_intro($exptid, $versionid = 0, $verifyid = TRUE)
  {

    if ($versionid == 0)
    {
      #-------------------------------------------------------------------------
      # No version ID specified, so need to look it up.
      #-------------------------------------------------------------------------
      $query = "SELECT vid FROM {node} WHERE type = 'experiment' " .
                                                 " AND nid =" . $exptid;
      $query_result = db_query($query);
      $row = db_fetch_array($query_result);

      if (isset($row['vid']))
      {
        $versionid = $row['vid'];
      }
    }

    if ((!$verifyid) or is_experiment($exptid))
    {
      $query = 'SELECT field_intro_value FROM {content_type_experiment} ' .
                'WHERE nid="' . $exptid . '" AND vid ="' . $versionid . '"';

      $intro_row = db_fetch_array(db_query($query));

      if (isset($intro_row['field_intro_value']))
      {
        return $intro_row['field_intro_value'];
      }
      else
      {
        return '';
      }
    }
    else
    {
      return '';
    }
  }

 /**
  * Gets the subject of the experiment given
  *
  * @param $exptid    The experiment to check.
  * @param $versionid The version ID of the experiment.  If this is not set
  *                   then we need to find the expt record first.
  * @param $verifyid  Boolean determining whether we should verify that the
  *                   exptid is a legitimate experiment.  Defaults to TRUE; we
  *                   might want to set it to FALSE to avoid additional
  *                   processing power being used if verification happens
  *                   before the function is called.
  *
  * @returns String   The subject.  'Unknown' if we can't find it, FALSE if this
  *                   is not actually an experiment.
  */
  function get_expt_subject($exptid, $versionid = 0, $verifyid = TRUE)
  {
    global $EXPT_SUBJECTS;

    if ($versionid == 0)
    {
      #-------------------------------------------------------------------------
      # No version ID specified, so need to look it up.
      #-------------------------------------------------------------------------
      $query = "SELECT vid FROM {node} WHERE type = 'experiment' " .
                                                 " AND nid =" . $exptid;
      $query_result = db_query($query);
      $row = db_fetch_array($query_result);
      
      if (isset($row['vid']))
      {
        $versionid = $row['vid'];
      }
    }

    if ((!$verifyid) or is_experiment($exptid))
    {
      #-------------------------------------------------------------------------
      # Build a string representing the list of allowed subjects, for use in
      # MySQL query.
      #-------------------------------------------------------------------------
      $subjects_string = '(';

      foreach ($EXPT_SUBJECTS as $tid => $subject)
      {
        $subjects_string = $subjects_string . '"' . (string)$tid . '", ';
      }

      $subjects_string = substr($subjects_string, 0, -2) . ')';
      
      #-------------------------------------------------------------------------
      # Run the query.
      #-------------------------------------------------------------------------
      $query = 'SELECT tid FROM {term_node} ' .
                   'WHERE vid = ' . $versionid . ' AND nid = ' . $exptid .
                   ' AND tid IN ' . $subjects_string;

      $term_row = db_fetch_array(db_query($query));

      if (isset($term_row['tid']) and array_key_exists($term_row['tid'],
                                                       $EXPT_SUBJECTS))
      {
        #-------------------------------------------------------------------
        # This is a subject.
        #-------------------------------------------------------------------
        return $EXPT_SUBJECTS[$term_row['tid']];
      }
      else
      {
        return 'Unknown';
      }
    }
    else
    {
      return 'Unknown';
    }
  }

 /**
  * Lists all experiments in the database.
  *
  * @returns Array   List of experiments, in an array of the form:
  *                  Array( ExperimentID => Array(Title, Subject, Intro))
  * @todo Make the intro bit actually work!
  */
  function list_all_experiments()
  {
    global $TABLES;

    $output_array = Array();

    #-------------------------------------------------------------------------
    # No event ID specified, so we're looking for all experiments in the DB.
    #-------------------------------------------------------------------------
    $query = "SELECT nid, vid, title FROM {node} WHERE type = 'experiment'";
    $query_result = db_query($query);

    while ($row = db_fetch_array($query_result))
    {
      if (isset($row['nid']) and isset($row['title']) and isset($row['vid']))
      {
        $versionid = $row['vid'];
        $exptid = $row['nid'];

        $output_array[$exptid] = Array('title'   => $row['title'],
                                       'subject' => get_expt_subject($exptid,
                                                                     $versionid,
                                                                     FALSE));
      }
    }

    return $output_array;
  }

 /**
  * Save a user's experiment choice to the database.  Requires $_POST access.
  *
  * @param $eventid    The node ID of the event.
  * @param $userid     The user's ID.
  */
  function save_user_expt_choices($eventid, $userid)
  {
    global $TABLES, $CONSTANTS;

    $query = 'DELETE FROM '. $TABLES['EXPT_CHOICE'] . ' WHERE userid="'
                 . (string)$userid . '" AND eventid="' . (string)$eventid . '"';
    $successful = db_query($query);

    if (isset($_POST['exptlist']))
    {
      $new_expt_list = $_POST['exptlist'];

      if (count($new_expt_list) > 0 and 
          count($new_expt_list) < $CONSTANTS['MIN_EXPTS_CHOSEN'])
      {
        echon('<b>Please select at least ' .
          (string)($CONSTANTS['MIN_EXPTS_CHOSEN']) . ' experiment choices</b>');
      }

      foreach ($new_expt_list as $exptid)
      {
        add_expt_choice_to_event($eventid, $userid, $exptid);
      }
    }
  }

 /**
  *  Assigns a user's experiment allocation.
  *
  * @param $eventid
  * @param $userid
  * @param $mornexptid
  * @param $afterexptid
  *
  * @return Boolean  True if successful, False otherwise
  */
  function assign_user_expts($eventid, $userid, $mornexptid, $afterexptid)
  {
    global $TABLES;

    $TABLES['EXPT_ASSIGN'] = 'signup_system_expt_assign';

    #--------------------------------------------------------------------------
    # Check whether the experiment and event IDs given correspond to actual
    # experiments and events.  If not, do nothing.
    #--------------------------------------------------------------------------
    if (is_event($eventid) && (is_experiment($mornexptid) || $mornexptid == 0)
                          && (is_experiment($afterexptid) || $afterexptid == 0))
    {
      $query = 'DELETE FROM ' . $TABLES['EXPT_ASSIGN'] .
               ' WHERE userid="' . $userid . '" AND eventid="' . $eventid . '"';

      db_query($query);

      $query = "INSERT INTO " . $TABLES['EXPT_ASSIGN']
                      . " (eventid, userid, mornexptid, afterexptid) VALUES"
                      . "('" . $eventid .  "', '" . $userid . "', '"
                             . $mornexptid . "', '" . $afterexptid . "')";
      $successful = db_query($query);
    }
    else
    {
      $successful = FALSE;
    }

    return $successful;
  }

 /**
  * Gets a user's experiment allocation.
  *
  * @param $eventid
  * @param $userid
  *
  * @return Array of form ('mornexptid' => mornexptid,
  *                        'afterexptid' => aftexptid)
  */
  function get_expt_assignment($eventid, $userid)
  {
    global $TABLES;

    #--------------------------------------------------------------------------
    # Check whether the experiment and event IDs given correspond to actual
    # experiments and events.  If not, do nothing.
    #--------------------------------------------------------------------------
    $query = 'SELECT * FROM ' . $TABLES['EXPT_ASSIGN'] .
               ' WHERE userid="' . $userid . '" AND eventid="' . $eventid . '"';

    $query_result = db_query($query);
    $row = db_fetch_array($query_result);

    return Array('mornexptid' => $row['mornexptid'],
                 'afterexptid' => $row['afterexptid']);
  }
?>
