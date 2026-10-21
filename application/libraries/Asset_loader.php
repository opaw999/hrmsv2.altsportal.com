<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Asset_loader {

    protected $CI;

    public function __construct()
    {
        // Gain access to the CodeIgniter super-object
        $this->CI =& get_instance();
        $this->CI->load->helper('url');
    }

    /**
     * Dynamically loads a JavaScript asset path with an auto-updating file modified timestamp
     * @param string $path Path to file relative to the root directory (e.g., 'assets/js/main.js')
     * @return string Complete HTML script element string
     */
    public function js($path)
    {
        $versioned_path = $this->_get_versioned_path($path);
        return '<script src="' . base_url($versioned_path) . '"></script>' . "\n";
    }

    /**
     * Dynamically loads a CSS asset path with an auto-updating file modified timestamp
     * @param string $path Path to file relative to the root directory (e.g., 'assets/css/style.css')
     * @return string Complete HTML link element string
     */
    public function css($path)
    {
        $versioned_path = $this->_get_versioned_path($path);
        return '<link rel="stylesheet" href="' . base_url($versioned_path) . '" />' . "\n";
    }

    /**
     * Internal helper to calculate the file modification timestamp
     */
    private function _get_versioned_path($path)
    {
        // FCPATH points strictly to your web server root directory folder path
        $physical_file = FCPATH . $path;

        if (file_exists($physical_file)) {
            // Fetch the exact UNIX epoch timestamp of when the file was last edited/saved
            $timestamp = filemtime($physical_file);
            return $path . '?v=' . $timestamp;
        }

        // Fallback gracefully to standard path if the file can't be physically located
        return $path;
    }
}