<?php
  /** @file constants.php
   *
   * @author   Mark Durkee
   * @version  V0.01
   */

  /**
   *  References to URLs of other pages within the site.
   */
  global $URLS;
  $BASE_URL = 'http://www.chaosscience.org.uk';
  $URLS = Array('BASE'           => $BASE_URL,
                'EVENT_LIST'     => $BASE_URL . '/demonstrator/event_list',
                'COMMITTEE_BASE' => $BASE_URL . '/committee/events',
                'USER_SIGNUP'    => $BASE_URL . '/demonstrator/signup',
                'EDIT_SIGNUP'    => $BASE_URL . '/committee/events/editsignup',
                'EXPT_CHOICE'    => $BASE_URL . '/committee/events/chooseexpts',
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
   */
  global $TABLES;
  $SIGNUP_PREFIX = 'signup_system_';
  $TABLES = Array('SIGNUP_EVENT' => 'drupal_content_type_signup_event',
                  'QUESTIONS'    => $SIGNUP_PREFIX . 'questions',
                  'VOLUNTEERS'   => $SIGNUP_PREFIX . 'volunteers',
                  'EXPERIMENTS'  => $SIGNUP_PREFIX . 'experiments',
                  'COMMENTS'     => $SIGNUP_PREFIX . 'othercomments',
                  'SESSIONS'     => $SIGNUP_PREFIX . 'sessions',
                  'EXPT_CHOICE'  => $SIGNUP_PREFIX . 'expt_choices');
  /**
   * List of subjects allowed for experiments (different from that for
   * volunteers).
   *
   * The number is the taxonomy tag ID for each subject.
   */
  global $EXPT_SUBJECTS;
  $EXPT_SUBJECTS = Array('15' => 'Chemistry',
                         '16' => 'Medicine',
                         '14' => 'Physics',
                         '37' => 'Engineering',
                         '17' => 'Biology',
                         '18' => 'Other');
?>