<?php
/* 
  Akismet API Example
  This is a very simple example which was written for a particular app. 
  
  =Example
  
    $ php example.php
    Comment 1:
    	[Spam Detected!]
    Comment 2:
    	[Clean!]
  
*/

require_once('lib/class.akismet.php');
define('BLOG', 'http://example.com');
define('AKISMET_API_TOKEN', 'yourakismetapitoken');

$comment1 = array(
  'comment_author'  => 'captain hook',
  'comment_content' => 'viagra 123'
  );

$comment2 = array(
  'comment_author'  => 'john smith',
  'comment_content' => 'Hey, this is pretty nifty'
  );

echo "Comment 1:\n\t";

  $akismet = new Akismet(BLOG, AKISMET_API_TOKEN, $comment1, false);
  if($akismet->is_spam())
  {
  	print "[Spam Detected!]\n";
  	// $akismet->submit_spam();
  }else{
  	print "[Clean!]\n";
  	// $akismet->submit_ham();
  }

echo "Comment 2:\n\t";
  
  $akismet->comment = $comment2;
  if($akismet->is_spam())
  {
  	print "[Spam Detected!]\n";
  }else{
  	print "[Clean!]\n";
  }

?>