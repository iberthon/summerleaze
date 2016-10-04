<?php
/*
Copyright 2014 Ian Berthon (email: ian@summerleaze.biz)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

/*
	@Class logger
 	@Purpose: Logs text to a file
 	@Author: Ian Berthon
 	@copyright 2014 Summerleaze Computer Services
 	@version: 1.0

 	@example usage
 		$log = logger::getInstance('logfile');
 		$log->write('An error has occured', __FILE__, __LINE__);

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

 */
class logger {
	private static $instance = NULL;

	/**
	 *
	 * @Constructor is set to private to stop instantion
	 *
	 */
	private function __construct()
	{
	}

	/**
	 * Clone is set to private to stop cloning
	 *
	 */
	private function __clone()
	{
	}

	/**
	*
	* Return logger instance or create new instance
	*
	* @return object (PDO)
	*
	* @access public
	*
	*/
	public static function getInstance($fname=null)
	{
		if (!self::$instance) {
			self::$instance = new logger;
		}
		if(!empty($fname)) {
      self::$instance->logfile = $fname;
		}
		else {
      self::$instance->logfile = dirname(realpath(__FILE__)) . '/' . date('Y-m-d') . '.log';
		}
		self::$instance->write(sprintf("Path: %s", self::$instance->logfile));
		return self::$instance;
	}

	/**
	 *
	 * @settor
	 *
	 * @access public
	 *
	 * @param string $name
	 *
	 * @param mixed $value
	 *
	 */
	public function __set($name, $value) {
	  switch($name) {
	    case 'logfile':
/*
		    if(!file_exists($value) || !is_writeable($value)) {
					throw new Exception("$value is not a valid file path");
		    }
*/
		    $this->logfile = $value;
		    break;

	    default:
	  	  throw new Exception("$name must be set");
	  }
	}

	/**
	 *
	 * @write to the logfile
	 *
	 * @access public
	 *
	 * @param string $message
	 *
	 * @param string $file The filename that caused the error
	 *
	 * @param int $line The line that the error occurred on
	 *
	 * @return number of bytes written, false other wise
	 *
	 */
	public function write($message, $file=null, $line=null) {
	  $message = time() . ': ' . $message;
	  $message .= is_null($file) ? '' : " in $file";
	  $message .= is_null($line) ? '' : " on line $line";
	  $message .= "\n";
	  return file_put_contents( $this->logfile, $message, FILE_APPEND );
	}

	public function error() {
		$this->write('ERROR: ' . sprintf(func_get_args()));
	}

	public function log() {
		$this->write(sprintf(func_get_args()));
	}

function LogPrintf() {
	$fp = fopen('/tmp/ian.log', 'ab');
	fwrite($fp, sprintf("\n%s:: ", date(DATE_RFC822)));
	//fwrite($fp, var_export(func_get_args(), TRUE));
	fwrite($fp, sprintf(func_get_args()));
	fclose($fp);
}

function LogExport() {
	$fp = fopen('/tmp/ian.log', 'ab');
	fwrite($fp, sprintf("\n%s:: ", date(DATE_RFC822)));
	fwrite($fp, var_export(func_get_args(), TRUE));
	fclose($fp);
}

/* Hook to the 'all' action */
function backtrace_filters_and_actions() {
  /* The arguments are not truncated, so we get everything */
  $arguments = func_get_args();
  $tag = array_shift( $arguments ); /* Shift the tag */

  /* Get the hook type by backtracing */
  $backtrace = debug_backtrace();
  $hook_type = $backtrace[3]['function'];

  $output = "<pre>";
  $output .= "<i>$hook_type</i> <b>$tag</b>\n";
  foreach ( $arguments as $argument ) {
    $output .= "\t\t" . htmlentities(var_export( $argument, true )) . "\n";
	}
	$output .= "\n";
	$output .= "</pre>";

	$fp = fopen('/tmp/ian.log', 'ab');
	fwrite($fp, sprintf("\n%s:: ", date(DATE_RFC822)));
	fwrite($fp, $output);
	fclose($fp);
}
//add_action('all', 'backtrace_filters_and_actions');
	
}

?>
