<?php
  /** @file constants.php
   *
   * @author   Mark Durkee
   */

  /**
   *  References to URLs of other pages within the site.
   */
  global $URLS;
  $BASE_URL = 'http://www.chaosscience.org.uk';
  $URLS = Array('BASE'           => $BASE_URL,
                'EVENT_LIST'     => $BASE_URL . '/demonstrator/event_list',
                'USER_SIGNUP'    => $BASE_URL . '/demonstrator/signup',
                'COMMITTEE_BASE' => $BASE_URL . '/committee/events',
                'EDIT_SIGNUP'    => $BASE_URL . '/committee/events/editsignup',
                'EXPT_CHOICE'    => $BASE_URL . '/committee/events/chooseexpts',
                'EXPT_ASSIGN'    => $BASE_URL . '/committee/events/assignexpts',
                'VIEW_EXPT_LIST' => $BASE_URL . '/committee/events/viewexptchoices',
                'LIST_BY_EXPT'   => $BASE_URL . '/committee/events/listbyexperiment',
                'REGISTER'       => $BASE_URL . '/user/register',
                'LOGIN'          => $BASE_URL . '/user/login',
                'EXPT_LIST'      => $BASE_URL . '/experiments',
                'CONTACT'        => $BASE_URL . '/contact');

  /**
   *  Webmaster's e-mail address.
   */
  global $EMAILS;
  $EMAILS = Array('WEB'     => "webmaster@chaosscience.org.uk",
                  'CONTACT' => "contact@chaosscience.org.uk");
  
  /**
   * Database tables holding the data for the signup system.
   * 
   * signup_system_expt_assign (eventid INT UNSIGNED NOT NULL,
   *                            userid INT UNSIGNED NOT NULL,
   *                            mornexptid INT UNSIGNED, 
   *                            afterexptid INT UNSIGNED)      
   */
  global $TABLES;
  $SIGNUP_PREFIX = 'signup_system_';
  $TABLES = Array('SIGNUP_EVENT' => 'drupal_content_type_signup_event',
                  'QUESTIONS'    => $SIGNUP_PREFIX . 'questions',
                  'VOLUNTEERS'   => $SIGNUP_PREFIX . 'volunteers',
                  'EXPERIMENTS'  => $SIGNUP_PREFIX . 'experiments',
                  'COMMENTS'     => $SIGNUP_PREFIX . 'othercomments',
                  'SESSIONS'     => $SIGNUP_PREFIX . 'sessions',
                  'EXPT_CHOICE'  => $SIGNUP_PREFIX . 'expt_choices',
                  'EXPT_ASSIGN'  => $SIGNUP_PREFIX . 'expt_assign');
  /**
   * List of subjects allowed for experiments (different from that for
   * volunteers).
   *
   * The number is the taxonomy tag ID for each subject.
   */
  global $EXPT_SUBJECTS;
  $EXPT_SUBJECTS = Array('15'  => 'Chemistry',
                         '16'  => 'Medicine',
                         '14'  => 'Physics',
                         '37'  => 'Engineering',
                         '17'  => 'Biology',
                         '134' => 'Maths',
                         '149' => 'Geology',
                         '18'  => 'Other');
                         
  /**
   * A collection of other useful constants.  They are gathered into an array to
   * ease importing large numbers of global variables into other scripts.
   */                         
  global $CONSTANTS;
  $CONSTANTS = Array('MIN_EXPTS_CHOSEN' => 5,
                     'MORNING_TIMES' => '9.30am-1.30pm',
                     'AFTERNOON_TIMES' => '12.30pm - 5pm');                       
?>
