<?php
defined("ABSPATH") or die("");

require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/ctrls/ctrl.base.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/class.scan.check.php');

/**
 * Controller for Tools 
 */
class DUP_PRO_CTRL_Tools extends DUP_PRO_CTRL_Base
{

    /**
     *  Init this instance of the object
     */
    function __construct()
    {
        add_action('wp_ajax_DUP_PRO_CTRL_Tools_runScanValidator', array($this, 'runScanValidator'));
        add_action('wp_ajax_DUP_PRO_CTRL_Tools_deleteInstallerFiles', array($this, 'deleteInstallerFiles'));
        add_action('wp_ajax_DUP_PRO_CTRL_Tools_migrationUploader', array($this, 'migrationUploader'));
        add_action('wp_ajax_DUP_PRO_CTRL_Tools_prepareArchiveForImport', array($this, 'prepareArchiveForImport'));
        add_action('wp_ajax_nopriv_DUP_PRO_CTRL_Tools_prepareArchiveForImport', array($this, 'prepareArchiveForImport'));
        add_action('wp_ajax_DUP_PRO_CTRL_Tools_runScanValidator', array($this, 'runScanValidator'));                        
    }

    /**
     * Calls the ScanValidator and returns a JSON result
     * 
     * @param string $_POST['scan-path']		The path to start scanning from, defaults to DUPLICATOR_WPROOTPATH
     * @param bool   $_POST['scan-recursive]	Recursively  search the path
     * 
     * @notes: Testing = /wp-admin/admin-ajax.php?action=DUP_PRO_CTRL_Tools_runScanValidator
     */
    public function runScanValidator($post)
    {
        @set_time_limit(0);
        $post = $this->postParamMerge($post);
        check_ajax_referer($post['action'], 'nonce');

        $result = new DUP_PRO_CTRL_Result($this);

        try {
            //CONTROLLER LOGIC
            $path = isset($post['scan-path']) ? $post['scan-path'] : DUPLICATOR_PRO_WPROOTPATH;
            if (!is_dir($path)) {
                throw new Exception("Invalid directory provided '{$path}'!");
            }
            $scanner = new DUP_PRO_ScanValidator();
            $scanner->recursion = (isset($post['scan-recursive']) && $post['scan-recursive'] != 'false') ? true : false;
            $payload = $scanner->run($path);

            //RETURN RESULT
            $test = ($payload->fileCount > 0) ? DUP_PRO_CTRL_Status::SUCCESS : DUP_PRO_CTRL_Status::FAILED;
            $result->process($payload, $test);
        } catch (Exception $exc) {
            $result->processError($exc);
        }
    }

    /**
     * Moves the specified archive to the root of the website and extracts the installer-backup.php file
     *
     * @param action $_POST["action"]		The action to use for this request
     * @param action $_POST["nonce"]		The param used for security
     * @param action $_POST["archive_filepath"]	Location of the archive
     * @param string $_FILES["file"]["name"]
     *
     * @notes: Testing = /wp-admin/admin-ajax.php?action=DUP_PRO_CTRL_Tools_migrationUploader
     */
    public function prepareArchiveForImport($post)
    {
        DUP_PRO_LOG::trace("prepare archive for import");
        @set_time_limit(0);
        $post = $this->postParamMerge($post);
      //  check_ajax_referer($post['action'], 'nonce');

        DUP_PRO_LOG::trace("1");
        $result = new DUP_PRO_CTRL_Result($this);

        DUP_PRO_LOG::trace("2");
        $payload = array();

        try {
            DUP_PRO_LOG::trace("3");
            DUP_PRO_LOG::traceObject("post", $post);
            if(isset($post['archive-filename'])) {

                DUP_PRO_LOG::trace("4");
                // 1. Move the archive
                $archive_filepath = DUPLICATOR_PRO_SSDIR_PATH_IMPORTS . '/' . $post['archive-filename'];

                $newArchiveFilepath = DUPLICATOR_PRO_WPROOTPATH . basename($archive_filepath);

                SnapLibIOU::rename($archive_filepath, $newArchiveFilepath, true);

                // 2. Extract the installer

                $extracted_installer_filepath = DUPLICATOR_PRO_WPROOTPATH . '/installer-backup.php';

                $relativeFilepaths = array();
                $relativeFilepaths[] = 'installer-backup.php';

                $fileExt = strtolower(pathinfo($newArchiveFilepath, PATHINFO_EXTENSION));

                if($fileExt == 'zip') {
                    /* @var $global DUP_PRO_Global_Entity */
                    $global = DUP_PRO_Global_Entity::get_instance();

                    // Assumption is that if shell exec zip works so does unzip
                 // RSR TODO: for now always use ziparchive   $useShellZip = ($global->get_auto_zip_mode() === DUP_PRO_Archive_Build_Mode::Shell_Exec);
                    $useShellZip = false;

                    DUP_PRO_Zip_U::extractFiles($newArchiveFilepath, $relativeFilepaths, DUPLICATOR_PRO_WPROOTPATH, $useShellZip);

                } else {
                    //DupArchiveEngine::init(new DUP_PRO_Dup_Archive_Logger());
                    //DupArchiveEngine::init(new DUP_PRO_Dup_Archive_Logger());

                    // TODO: DupArchive expand files
                    DupArchiveEngine::expandFiles($newArchiveFilepath, $relativeFilepaths, DUPLICATOR_PRO_WPROOTPATH);
                }

                if(!file_exists($extracted_installer_filepath)) {
                    throw new Exception(DUP_PRO_U::__("Couldn't extract backup installer {$extracted_installer_filepath} from archive!"));
                }

                //$final_installer_filepath= DUPLICATOR_PRO_WPROOTPATH . 'installer-'
                SnapLibIOU::rename($extracted_installer_filepath, DUPLICATOR_PRO_IMPORT_INSTALLER_FILEPATH);
            }
            else {
                throw new Exception("Archive filepath not set");
            }

            //RETURN RESULT
            $test = ($payload == true) ? DUP_PRO_CTRL_Status::SUCCESS : DUP_PRO_CTRL_Status::FAILED;
            $result->process($payload);
        } catch (Exception $ex) {
            DUP_PRO_LOG::trace("EXCEPTION: " . $ex->getMessage());
            $result->processError($ex);
        }
    }

    /**
     * Performs the upload process for site migration import
     *
     * @param action $_POST["action"]		The action to use for this request
     * @param action $_POST["nonce"]		The param used for security
     * @param action $_POST["$chunk_size"]	The byte count to read
     * @param string $_FILES["file"]["name"]
     *
     * @notes: Testing = /wp-admin/admin-ajax.php?action=DUP_PRO_CTRL_Tools_migrationUploader
     */
    public function migrationUploader($post)
    {
        @set_time_limit(0);
        $post = $this->postParamMerge($post);
        check_ajax_referer($post['action'], 'nonce');

        $result = new DUP_PRO_CTRL_Result($this);

        $out = array();

        try {
            if (!file_exists(DUPLICATOR_PRO_SSDIR_PATH_IMPORTS)) {
                SnapLibIOU::mkdir(DUPLICATOR_PRO_SSDIR_PATH_IMPORTS, 0755, true);
            }

            //CONTROLLER LOGIC
            $ext_types = array('daf', 'zip');
            $archive_filename = isset($_FILES["file"]["name"]) ? $_FILES["file"]["name"] : null;
            $temp_filename = isset($_FILES["file"]["tmp_name"]) ? $_FILES["file"]["tmp_name"] : null;
            $chunk_size = isset($_POST["chunk_size"]) ? $_POST["chunk_size"] : 2024;
            $chunk_mode = isset($_POST["chunk_mode"]) ? $_POST["chunk_mode"] : 'chunk';
            $file_ext = pathinfo($archive_filename, PATHINFO_EXTENSION);


            //	$ini_upload = ini_get('upload_max_filesize');
            //	$ini_post   = ini_get('post_max_size');
            //	$ini_upload = SnapLibUtil::convertToBytes($ini_upload);
            //	$ini_post	= SnapLibUtil::convertToBytes($ini_post);

            $chunk = $_POST["chunk"];
            $chunks = $_POST["chunks"];
            $archive_filepath = DUPLICATOR_PRO_SSDIR_PATH_IMPORTS . '/' . $_FILES["file"]["name"];

            //	$out['filename']	= $file_target;
            //	$out['chunk_mode']	= $chunk_mode;
            //	$out['ini_upload']	= $ini_upload;
            //	$out['ini_post']	= $ini_post;

            if (!in_array($file_ext, $ext_types)) {
                throw new Exception("Invalid file extention specified. Please use '.daf' or '.zip'!");
            }

            //CHUNK MODE
            if ($chunk_mode == 'chunked') {

                $archive_part_filepath = "{$archive_filepath}.part";
                $output = @fopen($archive_part_filepath, $chunks ? "ab" : "wb");
                $input = @fopen($temp_filename, "rb");

                if ($output === false) {
                    throw new Exception('Could not write output: ' . $archive_filepath);
                }

                if ($input === false) {
                    throw new Exception('Could not read input:' . $temp_filename);
                }

                while ($buffer = fread($input, $chunk_size)) {
                    fwrite($output, $buffer);
                }

                fclose($output);
                fclose($input);

                $out['mode'] = 'chunk';
                $out['status'] = 'chunking';

                if ($chunk == $chunks - 1) {
                    
					rename($archive_part_filepath, $archive_filepath);
					$out['status']   = 'chunk complete';
                    }
                //DIRECT MODE
            } else {
                move_uploaded_file($temp_filename, $archive_filepath);
                $out['status'] = 'complete';
                $out['mode'] = 'direct';
            }

            $payload = $out;

            //RETURN RESULT
            $test = ($payload == true) ? DUP_PRO_CTRL_Status::SUCCESS : DUP_PRO_CTRL_Status::FAILED;
            $result->process($payload, $test);
        } catch (Exception $exc) {
            DUP_PRO_LOG::trace("EXCEPTION: " . $exc->getMessage());
            $result->processError($exc);
        }
    }
}
