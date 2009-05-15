<?php

/**
 * @package SL Field Formatting
 * @version 1.0.1
 * @author Stephen Lewis (http://www.experienceinternet.co.uk/)
 * @copyright Copyright (c) 2009, Stephen Lewis
 * @license http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons Attribution-Share Alike 3.0 Unported
 * @link http://www.experienceinternet.co.uk/resources/details/sl-field-formatting/
*/

if ( ! defined('EXT'))
{
	exit('Invalid file request');
}

if ( ! defined('SL_FF_version'))
{
	define('SL_FF_version', '1.0.1');
	define('SL_FF_docs_url', 'http://www.experienceinternet.co.uk/resources/details/sl-field-formatting/');
	define('SL_FF_name', 'SL Field Formatting');
}


class Sl_field_formatting {
	
	/**
	 * Extension settings.
	 * @var array
	 */
   var $settings;

	/**
	 * Extension name.
	 * @var string
	 */
   var $name = SL_FF_name;

	/**
	 * Extension version.
	 * @var string
	 */
   var $version = SL_FF_version;

	/**
	 * Extension description.
	 * @var string
	 */
   var $description = "Pain-free customisation of ExpressionEngine's default field formatting options.";

	/**
	 * If $settings_exist = 'y', the settings page will be displayed in EE admin.
	 * @var string
	 */
   var $settings_exist = 'y';

	/**
	 * Link to extension documentation.
	 * @var string
	 */
   var $docs_url = SL_FF_docs_url;
   

	/**
	 * PHP4 constructor.
	 * @see __construct
	 */
	function Sl_field_formatting($settings = '')
	{
		$this->__construct($settings);
	}
	
	
	/**
	 * PHP5 constructor
	 * @param 	array|string 		$settings 	Extension settings; associative array or empty string.
	 */
	function __construct($settings = '')
	{		
		// Retrieve the settings from the database.
		$this->_refresh_settings();
	}
	
	
	/**
	 * Registers a new addon.
	 * @param			array 		$addons			The existing addons.
	 * @return 		array 		The new addons list.
	 */
	function lg_addon_update_register_addon($addons)
	{
		global $EXT;
		
		// Retrieve the data from the previous call, if applicable.
		if ($EXT->last_call !== FALSE)
		{
			$addons = $EXT->last_call;
		}
		
		// Register a new addon.
		if ($this->settings['update_check'] == 'y')
		{
			$addons[SL_FF_name] = $this->version;
		}
		
		return $addons;
	}
	
	
	/**
	 * Registers a new addon source.
	 * @param			array 		$sources		The existing sources.
	 * @return		array 		The new source list.
	 */
	function lg_addon_update_register_source($sources)
	{
		global $EXT;
		
		// Retrieve the data from the previous call, if applicable.
		if ($EXT->last_call !== FALSE)
		{
			$sources = $EXT->last_call;
		}
		
		// Register a new source.
		if ($this->settings['update_check'] == 'y')
		{
			$sources[] = 'http://www.experienceinternet.co.uk/addon-versions.xml';
		}
		
		return $sources;
	}
	
	
	/**
	 * Builds the breadcrumbs part of the settings form.
	 * @access	private
	 * @return  string    The "Breadcrumbs" HTML.
	 */
	function _settings_form_breadcrumbs()
	{
		global $DSP, $LANG;
		
		$r = '';
		$r .= $DSP->anchor(BASE . AMP . 'C=admin' . AMP . 'P=utilities', $LANG->line('utilities'));
		$r .= $DSP->crumb_item($DSP->anchor(BASE . AMP . 'C=admin' . AMP . 'M=utilities' . AMP . 'P=extensions_manager', $LANG->line('extensions_manager')));
		$r .= $DSP->crumb_item($LANG->line('extension_name'));
		
		$r .= $DSP->right_crumb(
			$LANG->line('disable_extension'),
			BASE . AMP . 'C=admin' . AMP . 'M=utilities' . AMP . 'P=toggle_extension' . AMP . 'which=disable' . AMP . 'name=' . strtolower(get_class($this))
		);
		
		return $r;
	}
	
	
	/**
	 * Builds the plugins list part of the settings form.
	 * @access  private
	 * @return  string    The "Plugins" HTML.
	 */
	function _settings_form_plugins()
	{
	  global $DSP, $LANG;
	  
	  /**
	   * We fake the default types. Not too happy about doing it this way,
	   * but from what I can tell, these are hard-coded in the core files
	   * anyway...
	   */
	   
	  $plugins = array(
	    array('class' => 'none', 'name' => $LANG->line('formatting_none')),
	    array('class' => 'br', 'name' => $LANG->line('formatting_br')),
	    array('class' => 'xhtml', 'name' => $LANG->line('formatting_xhtml'))
	    );
	  
	  if ($directory_handle = @opendir(PATH_PI))
	  {
	    $pattern = '/^pi\.([\w_]+)\\' . EXT . '$/';	    
	    while (($file = readdir($directory_handle)) !== FALSE)
	    {    
	      if (preg_match($pattern, $file, $matches))
	      {
	        if ( ! @include_once(PATH_PI . $file))
	        {
	          continue;
	        }
	        
	        $plugins[] = array(
	          'class' => $matches[1],
	          'name'  => $plugin_info['pi_name']
	        );
	      }
	    }
	    
	    closedir($directory_handle);
	  }
	  
	  // Output all the available plugins.	  
	  $r = $DSP->table_open(
			array(
				'class' 	=> 'tableBorder',
				'border' 	=> '0',
				'style' 	=> 'width : 100%; margin-top : 1em;',
				)
			);
			
		$r .= $DSP->tr();
		$r .= $DSP->td('tableHeading', '', '2');
		$r .= $LANG->line('plugins_title');
		$r .= $DSP->td_c();
		$r .= $DSP->tr_c();
		
		$r .= $DSP->tr();
		$r .= $DSP->td('', '', '2');
		$r .= "<div class='box' style='border-width : 0 0 1px 0; margin : 0; padding : 10px 5px'><p>" . $LANG->line('plugins_info'). "</p></div>";
		$r .= $DSP->td_c();
		$r .= $DSP->tr_c();	
		
		$count = 1;
		
		foreach ($plugins AS $p)
		{		  
		  $td_class = ($count++ % 2) ? 'tableCellOne' : 'tableCellTwo';		  
		  $checked = isset($this->settings['plugins'][$p['class']]) ? 'y' : 'n';
		  
		  $r .= $DSP->tr();
		  $r .= $DSP->td($td_class);
		  $r .= $DSP->input_checkbox('plugin_' . $p['class'], $p['class'], $checked, 'id="plugin_' . $p['class'] . '"');
		  $r .= '<label class="defaultBold" for="plugin_' . $p['class'] . '">' . $p['name'] . '</label>';
		  $r .= $DSP->td_c();

  		$r .= $DSP->tr_c();
		}
		
		$r .= $DSP->table_c();
	  
	  return $r;
	}
	
	
	/**
	 * Builds the "Check for Updates" part of the settings form.
	 * @access	private
	 * @return  string    The "Check for Updates" HTML.
	 */	
	function _settings_form_updates()
	{
		global $DSP, $LANG;
		
		$r  = '';
		
		// Automatic updates.
		$r .= $DSP->table_open(
			array(
				'class' 	=> 'tableBorder',
				'border' 	=> '0',
				'style' 	=> 'width : 100%; margin-top : 1em;',
				)
			);
			
		$r .= $DSP->tr();
		$r .= $DSP->td('tableHeading', '', '2');
		$r .= $LANG->line('update_check_title');
		$r .= $DSP->td_c();
		$r .= $DSP->tr_c();
		
		$r .= $DSP->tr();
		$r .= $DSP->td('', '', '2');
		$r .= "<div class='box' style='border-width : 0 0 1px 0; margin : 0; padding : 10px 5px'><p>" . $LANG->line('update_check_info'). "</p></div>";
		$r .= $DSP->td_c();
		$r .= $DSP->tr_c();	
		
		$r .= $DSP->tr();
		$r .= $DSP->td('tableCellOne', '40%');
		$r .= $DSP->qdiv('defaultBold', $LANG->line('update_check_label'));
		$r .= $DSP->td_c();
		
		$update_check = isset($this->settings['update_check']) ? $this->settings['update_check'] : 'y';
		
		$r .= $DSP->td('tableCellOne', '60%');
		$r .= $DSP->input_select_header('update_check', '', 3, '', 'id="update_check"');
		$r .= $DSP->input_select_option('y', 'Yes', ($update_check == 'y' ? 'selected' : ''));
		$r .= $DSP->input_select_option('n', 'No', ($update_check == 'n' ? 'selected' : ''));
		$r .= $DSP->input_select_footer();
		$r .= $DSP->td_c();
		
		$r .= $DSP->tr_c();
		$r .= $DSP->table_c();
		
		return $r;
	}
	
	
	/**
	 * Builds the "Save Settings" part of the settings form.
	 * @access  private
	 * @return  string    The "Save Settings" HTML.
	 */
	function _settings_form_save()
	{
	  global $DSP, $LANG;
	  
		return $DSP->qdiv('itemWrapperTop', $DSP->input_submit($LANG->line('save_settings'), 'save_settings', 'id="save_settings"'));
	}
	
	
	/**
	 * Renders the settings screen.
	 */
	function settings_form()
	{
		global $DSP, $LANG;
			
		// Start building the page.
		// $headers 				= $this->_settings_form_headers();			  // Additional CSS and JS headers.		
		$breadcrumbs 		= $this->_settings_form_breadcrumbs();			  // Breadcrumbs.
		$browser_title 	= $LANG->line('extension_settings');		// Browser title.

		// Body
		$body  = '';
		$body .= $DSP->heading($LANG->line('extension_name') . " <small>v{$this->version}</small>");		// Main title.
		
		// Open the form.
		$body .= $DSP->form_open(
			array(
				'action'	=> 'C=admin' . AMP . 'M=utilities' . AMP . 'P=save_extension_settings',
				'id'			=> 'form_save_settings',
				'name'		=> 'form_save_settings'
				),
			array(
				'name' 			=> strtolower(get_class($this)),		// Must be lowercase.
				'action'		=> 'save_settings',
				)
			);
			
		// Plugins list.
		$body .= $this->_settings_form_plugins();
		
		// Check for updates / save settings.
		$body .= $this->_settings_form_updates() . $this->_settings_form_save();
		
		// Close the form.
		$body .= $DSP->form_c();
		
		// Output everything.
		// $DSP->extra_header	.= $headers;
		$DSP->title 				= $browser_title;
		$DSP->crumbline 		= TRUE;
		$DSP->crumb 				= $breadcrumbs;
		$DSP->body 					= $body;
	}
	
	
	/**
	 * Refreshes the settings. If this is the first time here, the settings are
	 * pulled from the database. If there is POST data to process, we do the
	 * necessary, and update the settings array accordingly.
	 *
	 * @access  private
   */
  function _refresh_settings()
  {
    global $DB, $REGX;
		
		// Do we need to retrieve the settings from the database?
		if (isset($this->settings) === FALSE)
		{
		  
		  $result = $DB->query("SELECT settings FROM exp_extensions WHERE enabled='y' AND class='" . get_class($this) . "' LIMIT 1");
		  
		  if ($result->num_rows == 1 && $result->row['settings'] != '')
		  {
		    $this->settings = $REGX->array_stripslashes(unserialize($result->row['settings']));
		  }
		}
		
		// If we have POST data, process it.
		if (isset($_POST['save_settings']))
		{		  
		  // - Update check.
  		if (isset($_POST['update_check']) === TRUE) $this->settings['update_check'] = $_POST['update_check'];
  		
  		// - Plugins
  		$this->settings['plugins'] = array();  		
  		$post_keys = implode("\n", array_keys($_POST));

  		if (preg_match_all("/^plugin_[\w_]+$/im", $post_keys, $matches, PREG_PATTERN_ORDER))
  		{
  		  foreach ($matches[0] AS $plugin)
  		  {
  		    $this->settings['plugins'][$_POST[$plugin]] = TRUE;
  		  }
  		}
		}
		else
		{
		  // If we don't have a plugins array, create an empty one.
		  if ( ! isset($this->settings['plugins'])) $this->settings['plugins'] = array();
		}
  }
	
	
	/**
	 * Saves the extension settings.
	 */
	function save_settings()
	{
		global $DB, $REGX, $PREFS;
		
		// Refresh the settings.
		$this->_refresh_settings();
		
		// Serialise the settings, and save them to the database.
		$sql[] = "UPDATE exp_extensions SET settings = '" . addslashes(serialize($this->settings)) . "' WHERE class = '" . get_class($this) . "'";
		
		// Update the available formatting options for all the weblog fields.
		// Make a note of all the available formatting options, as selected by the user.
		$options = array_keys($this->settings['plugins']);
		
		// Initialise our insert statement for the field formatting table.
		$f_sql = 'INSERT INTO `' . $PREFS->ini('db_prefix') . '_field_formatting` (`field_id`, `field_fmt`) VALUES';
		
		$fields = $DB->query("SELECT field_id, field_fmt FROM exp_weblog_fields");
		if ($fields->num_rows > 0)
		{
		  foreach ($fields->result AS $f)
  		{
  		  $f_options = array_unique(array_merge($options, array($f['field_fmt'])));

  		  // Delete all the existing field formatting options.
  		  $sql[] = "DELETE FROM exp_field_formatting WHERE field_id = '" . $f['field_id'] . "'";

  		  // Add the new field formatting options.
  		  foreach ($f_options AS $o)
  		  {
  		    $f_sql .= "(" . $f['field_id'] . ",'{$o}'),";
  		  }
  		}

  		// Add the field formatting SQL to our queries array.
  		$f_sql = rtrim($f_sql, ',');
  		$sql[] = $f_sql;
		}
		
		// Run all our queries.
		foreach ($sql AS $query)
		{
		  $DB->query($query);
		}
	}
	
	
	/**
	 * Activate the extension.
	 */
	function activate_extension()
	{
		global $DB;
		
		$hooks = array(
			'lg_addon_update_register_source'		=> 'lg_addon_update_register_source',
			'lg_addon_update_register_addon'		=> 'lg_addon_update_register_addon'
			);
			
		foreach ($hooks AS $hook => $method)
		{
			$sql[] = $DB->insert_string('exp_extensions', array(
					'extension_id' => '',
					'class'        => get_class($this),
					'method'       => $method,
					'hook'         => $hook,
					'settings'     => '',
					'priority'     => 10,
					'version'      => $this->version,
					'enabled'      => 'y'
					));
		}
		
		// Run all the SQL queries.
		foreach ($sql AS $query)
		{
			$DB->query($query);
		}		
	}


	/**
	 * Updates the extension.
	 * @param string $current Contains the current version if the extension is already installed, otherwise empty.
	 * @return bool FALSE if the extension is not installed, or is the current version.
	 */
	function update_extension($current='')
	{
		global $DB;

		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		if ($current < $this->version)
		{
			$DB->query("UPDATE exp_extensions
				SET version = '" . $DB->escape_str($this->version) . "' 
				WHERE class = '" . get_class($this) . "'");
		}
	}


	/**
	 * Disables the extension, and deletes settings from the database.
	 */
	function disable_extension()
	{
		global $DB;	
		$DB->query("DELETE FROM exp_extensions WHERE class = '" . get_class($this) . "'");
	}
				
}

?>