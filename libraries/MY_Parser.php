<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Frisker
 *
 * An extension of CI_Parser.  You can use it the same way as normal, except
 * with additional features.
 *
 * You can now use PHP functions in your template.
 * Example:
 *   {date("F j, Y, g:i a")}
 *
 * You can also pass the parse() function a string of data, instead of a view.
 * You just need to set the 4th parameter to FALSE.
 * Example:
 *   $data = array();
 *   $body = $this->db->get_where('pages', array('id' => 4))->row()->body;
 *   $body = $this->parser->parse($body, $data, TRUE, FALSE);
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Frisker
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @copyright	2010 Dan Horrigan
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class MY_Parser extends CI_Parser
{
	/**
	 * Holds the data you send the parser.
	 */
	protected static $_data;
	
	/**
	 * Parse a template
	 *
	 * Parses pseudo-variables contained in the specified template view or
	 * template data replacing them with the data in the second param
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @param	bool
	 * @return	string
	 */
	function parse($template, $data, $return = FALSE, $use_view = TRUE)
	{
		if($use_view)
		{
			$CI =& get_instance();
			$template = $CI->load->view($template, $data, TRUE);
		}

		return $this->_parse($template, $data, $return);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Parse a template
	 *
	 * Parses pseudo-variables contained in the specified template view or
	 * template data replacing them with the data in the second param
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	function _parse($template, $data, $return = FALSE)
	{
		if ($template == '')
		{
			return FALSE;
		}
		self::$_data = $data;
		unset($data);
		
		foreach (self::$_data as $key => $val)
		{
			
			if (is_array($val))
			{
				$template = $this->_parse_pair($key, $val, $template);		
			}
			else
			{
				$template = $this->_parse_single($key, (string)$val, $template);
			}
		}

		// eval() any remaining tags
		$template = preg_replace_callback('/' . $this->l_delim . '(.*?)' . $this->r_delim . '/', 'MY_Parser::eval_callback', $template);

		if ($return == FALSE)
		{
			$CI =& get_instance();
			$CI->output->append_output($template);
		}

		return $template;
	}

	// --------------------------------------------------------------------

	/**
	 * Eval Callback
	 *
	 * Evaluates the code sent from preg_replace_callback()
	 *
	 * @access	public
	 * @param	array
	 * @return	mixed
	 */
	static function eval_callback($_matches)
	{
		// Skip these as they are replaced in the output class.
		if(in_array($_matches[0], array('{elapsed_time}', '{memory_usage}')) OR empty($_matches[1]))
		{
			return $_matches[0];
		}
		if(substr($_matches[1], -1) != ';')
		{
			$_matches[1] .= ';';
		}

		foreach (self::$_data as $_key => &$_val)
		{
			if(!isset(${$_key}))
			{
				${$_key} =& $_val;
			}
		}
		eval("\$_matches[1] = " . $_matches[1]);
		
		return $_matches[1];
	}

}

/* End of file MY_Parser.php */
/* Location: ./application/libraries/MY_Parser.php */