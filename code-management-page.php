<p>As of June 2014, the CHaOS signup system code is managed through the Github repo
https://github.com/mnd22/chaos-signup.git'</p>

<p>The directory ../signup-system is a git clone of that remote repo.</p>

<p>To make changes, talk to Mark to get the ability to push changes up to Github (you can get a copy of the code to play with on your local machine just by visiting Github.  Having pushed changes to Github, they can then be easily pulled to here.  Note that the code is public, but information such as database passwords are stored securely in the config file of our drupal installation (the signup system doesn't have its own)</p>

<p>Enter the password (name of favourite skeleton in lower case) and click the following button to update the code to the latest commit on github.</p>

<p><i>Note that this is not in any sense a secure password - it is intended to prevent accidental updating of code (e.g. by a search engine) rather than to prevent malicious attack.</i></p>

<?php
    echo '<form action="http://www.chaosscience.org.uk/committee/signupcode" method="post">';
    echo '<b>Password:</b> <input type="text" name="password" size="20" maxlength="20" />';
    echo '<input type="submit" value="Update code" />';
    echo '</form>'; 
    
    if (isset($_POST['password']) && $_POST['password'] == 'boris')
    {
      echo '<br>----------------------------------------------------------<br>';
      echo 'Updating code to match latest checked-in version.';
      echo '<br>----------------------------------------------------------<br>';
      echo shell_exec('cd ../signup_system; git pull origin');
      echo '<br>----------------------------------------------------------<br>';
      echo shell_exec('cd ../signup_system; git status');
      echo '<br>----------------------------------------------------------<br>';
      $dirlist = shell_exec('ls -l ../signup_system');
      echo '<b>Current working code directory is:<br></b>' . nl2br($dirlist);
      echo '<br>----------------------------------------------------------<br>';
    }
    else
    {
      if (isset($_POST['password']))
      {
        echo "<b>Incorrect password!</b>";
      }
      
      echo '<br>----------------------------------------------------------<br>';
      echo shell_exec('cd ../signup_system; git status');
      echo '<br>----------------------------------------------------------<br>';
      $dirlist = shell_exec('ls -l ../signup_system');
      echo '<b>Current working code directory is:<br></b>' . nl2br($dirlist);
      echo '<br>----------------------------------------------------------<br>';
    }


  # The code below was for a legacy approach to code deployment and is retained
  # for reference only.
  # shell_exec('cp sites/default/files/signup-code-020.tar ../signup_system/new-signup-code.tar');
  # shell_exec('tar cf ../signup_system/old-signup-code.tar ../signup_system/*.php');
  # shell_exec('cd ../signup_system; tar xf new-signup-code.tar');
  # echo shell_exec('ls -lt ../signup_system');
  # echo shell_exec('cat ../signup_system/useful_functions.php');
?>

