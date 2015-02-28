<?php
  /** @file constants.php
   *
   * @author   Mark Durkee
   */

 /*
  * Domain name of the website to deploy this on.
  */
  $BASE_URL = 'http://www.chaosscience.org.uk';

  /**
   *  References to URLs of other pages within the site.
   */
  global $URLS;
  $URLS = Array('BASE'           => $BASE_URL,
                'EVENT_LIST'     => $BASE_URL . '/committee/events/list',
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
   *  E-mail addresses used within the system.
   */
  global $EMAILS;
  $EMAILS = Array('WEB'     => "webmaster@chaosscience.org.uk",
                  'CONTACT' => "contact@chaosscience.org.uk");

 /*
  * Prefix identifying signup system tables within the MySQL database.
  */
  $SIGNUP_PREFIX = 'signup_system_';
  
 /**
  * Database tables holding the data specific to the signup system.
  */
  global $TABLES;
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
