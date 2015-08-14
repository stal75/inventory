<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

// error_reporting(E_ALL | E_STRICT);
// require('UploadHandler.php');
// $urlHolder = NULL;
 
 if( isset($_POST['fuPath']) ) {
     $urlHolder = filter_var($_POST['fuPath'], FILTER_SANITIZE_URL);
 }
 else if( isset($_GET['fuPath']) ){
     $urlHolder = filter_var($_GET['fuPath'], FILTER_SANITIZE_URL);
}
// $upload_handler = new UploadHandler(null , true , null, $urlHolder);

error_reporting(E_ALL | E_STRICT);
require('UploadHandler.php');
$upload_handler = new UploadHandler(array('user_dirs' => true ));