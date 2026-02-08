<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Extended Upload library: allow skipping strict MIME check (validate by extension only)
 * when 'ignore_mime' => TRUE is passed in config, or when uploading to communication path
 * (fixes "filetype not allowed" for recorded voice on iPhone/Safari).
 */
class MY_Upload extends CI_Upload {

	public $ignore_mime = FALSE;

	public function initialize(array $config = array(), $reset = TRUE)
	{
		if (isset($config['ignore_mime'])) {
			$this->ignore_mime = (bool) $config['ignore_mime'];
			unset($config['ignore_mime']);
		}
		return parent::initialize($config, $reset);
	}

	public function is_allowed_filetype($ignore_mime = FALSE)
	{
		if ($this->ignore_mime) {
			$ignore_mime = TRUE;
		}
		if (!$ignore_mime && !empty($this->upload_path) && strpos($this->upload_path, 'communication') !== FALSE) {
			$ignore_mime = TRUE;
		}
		return parent::is_allowed_filetype($ignore_mime);
	}
}
