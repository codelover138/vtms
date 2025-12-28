<?php defined('BASEPATH') OR exit('No direct script access allowed');
ini_set('memory_limit', '-1');
// set max execution time 2 hours / mostly used for exporting PDF
ini_set('max_execution_time', 3600);

/**
 * Created by PhpStorm.
 * User: a.kader
 * Date: 12-Nov-18
 * Time: 9:55 AM
 */
class Document extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->permission_details = $this->site->checkPermissions();
        $this->lang->admin_load('document', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('document_model');
        $this->allowed_file_size = '20';
        $this->digital_file_types = 'jpg|jpeg|png|pdf|doc|docx';
    }


    public function file_manager()
    {
        if (!$this->Owner && !$this->Admin) {
            $get_permission = $this->permission_details[0];

            if ((!$get_permission['document-file_manager'])) {
                $this->session->set_flashdata('warning', lang('access_denied'));
                die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('file_manager')));
        $meta = array('page_title' => lang('file_manager'), 'bc' => $bc);
        $this->page_construct('filemanager/filemanager', $meta, $this->data);
    }

    public function elfinder_init()
    {
        if (!$this->Owner && !$this->Admin) {
            $get_permission = $this->permission_details[0];
            if ((!$get_permission['document-file_manager'])) {
                $this->session->set_flashdata('warning', lang('access_denied'));
                die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 10);</script>");
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
        $this->load->helper('path');
        $_allowed_files = explode('|', $this->digital_file_types);
        $config_allowed_files = array();
        if (is_array($_allowed_files)) {
            foreach ($_allowed_files as $v_extension) {
                array_push($config_allowed_files, '.' . $v_extension);
            }
        }
        $allowed_files = array();
        if (is_array($config_allowed_files)) {
            foreach ($config_allowed_files as $extension) {
                $_mime = get_mime_by_extension($extension);

                if ($_mime == 'application/x-zip') {
                    array_push($allowed_files, 'application/zip');
                }
                if ($extension == '.exe') {
                    array_push($allowed_files, 'application/x-executable');
                    array_push($allowed_files, 'application/x-msdownload');
                    array_push($allowed_files, 'application/x-ms-dos-executable');
                }
                array_push($allowed_files, $_mime);
            }
        }

        if ($this->Owner || $this->Admin) {
            $disabled = array();
            array_push($disabled, 'move','duplicate','cut');
            $root_options = array(
                'driver' => 'LocalFileSystem',
                'path' => set_realpath('assets/document/'),
                'URL' => base_url('assets/document/'),
                'uploadMaxSize' => $this->allowed_file_size . 'M',
                'accessControl' => 'access',
                'disabled' => $disabled,
                'uploadAllow' => array(
    // Documents
    'application/pdf',                   // PDF
    'application/msword',                // Word (.doc)
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // Word (.docx)
    'application/vnd.ms-excel',          // Excel (.xls)
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // Excel (.xlsx)
    'application/vnd.ms-powerpoint',     // PowerPoint (.ppt)
    'application/vnd.openxmlformats-officedocument.presentationml.presentation', // PowerPoint (.pptx)
    'text/plain',                        // Text files (.txt)

    // Images
    'image/jpeg',                        // JPEG (.jpg, .jpeg)
    'image/png',                         // PNG
    'image/gif',                         // GIF
    'image/bmp',                         // BMP
    'image/webp',                        // WebP
    'image/tiff',                        // TIFF

    // Archives (optional, for zipped files)
    'application/zip',                   // ZIP
    'application/x-rar-compressed',      // RAR
    'application/x-7z-compressed',       // 7z
    'application/x-tar',                 // TAR
    'application/gzip',                  // GZIP

    // Fallback for unknown types
    'application/octet-stream'           // Generic binary
),

                'uploadOrder' => array(
                    'allow',
                    'deny'
                ),
                'attributes' => array(
                    array(
                        'pattern' => '/.tmb/',
                        'hidden' => true
                    ),
                    array(
                        'pattern' => '/.quarantine/',
                        'hidden' => true
                    ),
                    array(
                        'read' => true,
                        'write' => true,
                    )
                )
            );

        } else {
            $user = $this->site->getUserById($this->session->userdata('user_id'));
            $disabled = array();
            $upload = array();
            if (!($get_permission['document-upload'])) array_push($upload, "all");
            if (!($get_permission['document-folder_download'])) array_push($disabled, "zipdl");
            if (!($get_permission['document-folder_create'])) array_push($disabled, 'extract', 'archive', 'mkdir');
            if (!($get_permission['document-file_delete'])) array_push($disabled, 'rename', 'locked','rm', 'cut');
            array_push($disabled, 'move','duplicate');

            $root_options = array(
                'driver' => 'LocalFileSystem',
                'path'          => FCPATH . 'assets/document/'.$user->username,
                'URL' => base_url('assets/document/'.$user->username),
                'uploadMaxSize' => $this->allowed_file_size . 'M',
                'accessControl' => 'access',
                'uploadAllow' => array(
    // Documents
    'application/pdf',                   // PDF
    'application/msword',                // Word (.doc)
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // Word (.docx)
    'application/vnd.ms-excel',          // Excel (.xls)
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // Excel (.xlsx)
    'application/vnd.ms-powerpoint',     // PowerPoint (.ppt)
    'application/vnd.openxmlformats-officedocument.presentationml.presentation', // PowerPoint (.pptx)
    'text/plain',                        // Text files (.txt)

    // Images
    'image/jpeg',                        // JPEG (.jpg, .jpeg)
    'image/png',                         // PNG
    'image/gif',                         // GIF
    'image/bmp',                         // BMP
    'image/webp',                        // WebP
    'image/tiff',                        // TIFF

    // Archives (optional, for zipped files)
    'application/zip',                   // ZIP
    'application/x-rar-compressed',      // RAR
    'application/x-7z-compressed',       // 7z
    'application/x-tar',                 // TAR
    'application/gzip',                  // GZIP

    // Fallback for unknown types
    'application/octet-stream'           // Generic binary
),

                'disabled' => $disabled,
                'uploadDeny' => $upload,
                'uploadOrder' => array(
                    'allow',
                    'deny'
                ),
                'attributes' => array(
                    array(
                        'pattern' => '/.tmb/',
                        'hidden' => true
                    ),
                    array(
                        'pattern' => '/.quarantine/',
                        'hidden' => true
                    ),
                    array(
                        'read' => true,
                        'write' => true,
                    )
                )
            );
        }
        $opts = array(
            'roots' => array(
                $root_options
            )
        );
        $this->load->library('elfinder_lib', $opts);
    }

}