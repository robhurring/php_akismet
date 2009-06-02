<?php
/**
 * Basic Akismet API Wrapper
 * @author Rob Hurring <rob@ubrio.us>
 * @homepage http://github.com/robhurring/php_akismet
 * @see http://akismet.com/development/api/ for information
 * @license MIT
 */

class Akismet
{
  public $blog;
  public $api;
  public $comment;
  public $include_server_variables;
  
  private $host;
  private $port;
  private $akismet_version;
  
  public
  function __construct($blog, $api, $comment, $include_server_variables = true)
  {
    $this->blog = $blog;
    $this->api = $api;
    $this->host = 'rest.akismet.com';
    $this->port = 80;
    $this->akismet_version = '1.1';
    $this->comment = $comment;
    $this->fill_user_variables();
    if($include_server_variables)
      $this->fill_server_variables();
  }
  
  public
  function is_spam()
  {
		$response = $this->call($this->build_query(), 'comment-check');
		return ($response == "true");
  }
  
  public
  function submit_spam()
  {
    $this->call($this->build_query(), 'submit-spam');
  }

  public
  function submit_ham()
  {
		$this->call($this->build_query(), 'submit-ham');    
  }
  
  protected
  function build_query()
  {
    return http_build_query($this->comment);
  }
  
  protected
	function call($request, $path, $type = "post", $response_length = 1160)
  {
    $connection = @fsockopen($this->host, $this->port);
		
		$request  = 
				strToUpper($type)." /{$this->akismet_version}/$path HTTP/1.1\r\n" .
				"Host: ".((!empty($this->api)) ? $this->api."." : null)."{$this->host}\r\n" .
				"Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n" .
				"Content-Length: ".strlen($request)."\r\n" .
				"User-Agent: PHPAkismet/1.0\r\n" .
				"\r\n" .
				$request;
			$response = '';

			@fwrite($connection, $request);

			while(!feof($connection))
				$response .= @fgets($connection, $response_length);
			$response = explode("\r\n\r\n", $response, 2);
			
      @fclose($connection);
			return $response[1];      
  }

  protected
  function fill_user_variables()
  {
		if(!isset($this->comment['user_ip']))
			$this->comment['user_ip'] = ($_SERVER['REMOTE_ADDR'] != getenv('SERVER_ADDR')) ? $_SERVER['REMOTE_ADDR'] : getenv('HTTP_X_FORWARDED_FOR');

		if(!isset($this->comment['user_agent']))
			$this->comment['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

		if(!isset($this->comment['referrer']))
			$this->comment['referrer'] = $_SERVER['HTTP_REFERER'];

		if(!isset($this->comment['blog']))
			$this->comment['blog'] = $this->blog;		
  }
  
  protected
  function fill_server_variables()
  {
    $ignore = array(
  			'HTTP_COOKIE',
  			'HTTP_X_FORWARDED_FOR',
  			'HTTP_X_FORWARDED_HOST',
  			'HTTP_MAX_FORWARDS',
  			'HTTP_X_FORWARDED_SERVER',
  			'REDIRECT_STATUS',
  			'SERVER_PORT',
  			'PATH',
  			'DOCUMENT_ROOT',
  			'SERVER_ADMIN',
  			'QUERY_STRING',
  			'PHP_SELF',
  			'argv',
  			'argc'
  		);
  	    
  	foreach($_SERVER as $k => $v)
  	{
  	  if(!in_array($k, $ignore))
  	  {
  	    if($k == 'REMOTE_ADDR')
          $this->comment[$k] = $this->comment['user_ip'];
        else
          $this->comment[$k] = $v;
  	  }
  	}    
  }
  
}

?>