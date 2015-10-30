<?php
/*
 * Directory Reader by George Whitcher http://georgewhitcher.com
 * Version 0.1
 * Original source from http://www.abeautifulsite.net/php-file-tree/
 */

//CONFIGURE
$foldername = '..'.$_SERVER['REQUEST_URI'].'';  //Set to same directory as index.php.  Set $foldername = 'YOURDIRECTORY/'; for a different directory.
const not_allowed = array('.', '..', '.git', '.idea', '.gitignore', 'README.md'); //Files to exclude
$allowed_extensions = array();  //Allowed extensions (leave blank for all) ie array("gif", "jpg", "jpeg", "png"); for images only.

//DO NOT EDIT BELOW THIS LINE
function php_file_tree($directory, $return_link, $extensions = array()) {
    // Generates a valid XHTML list of all directories, sub-directories, and files in $directory
    // Remove trailing slash
    if( substr($directory, -1) == "/" ) $directory = substr($directory, 0, strlen($directory) - 1);
    $code = php_file_tree_dir($directory, $return_link, $extensions);
    return $code;
}

function php_file_tree_dir($directory, $return_link, $extensions = array(), $first_call = true) {

    $php_file_tree = '';
    // Recursive function called by php_file_tree() to list directories/files

    // Get and sort directories/files
    if( function_exists("scandir") ) $file = scandir($directory); else $file = php4_scandir($directory);
    natcasesort($file);
    // Make directories first
    $files = $dirs = array();
    foreach($file as $this_file) {
        if( is_dir("$directory/$this_file" ) ) $dirs[] = $this_file; else $files[] = $this_file;
    }
    $file = array_merge($dirs, $files);

    // Filter unwanted extensions
    if( !empty($extensions) ) {
        foreach( array_keys($file) as $key ) {
            if( !is_dir("$directory/$file[$key]") ) {
                $ext = substr($file[$key], strrpos($file[$key], ".") + 1);
                if( !in_array($ext, $extensions) ) unset($file[$key]);
            }
        }
    }

    if( count($file) > 2 ) { // Use 2 instead of 0 to account for . and .. "directories"
        $php_file_tree = "<ul";
        if( $first_call ) { $php_file_tree .= " class=\"php-file-tree\""; $first_call = false; }
        $php_file_tree .= ">";
        foreach( $file as $this_file ) {
            if(!in_array( $this_file, not_allowed) ) {
                if( is_dir("$directory/$this_file") ) {
                    // Directory
                    $php_file_tree .= "<li class=\"pft-directory\"><a href=\"#\">" . htmlspecialchars($this_file) . "</a>";
                    $php_file_tree .= php_file_tree_dir("$directory/$this_file", $return_link ,$extensions, false);
                    $php_file_tree .= "</li>";
                } else {
                    // File
                    // Get extension (prepend 'ext-' to prevent invalid classes from extensions that begin with numbers)
                    $ext = "ext-" . substr($this_file, strrpos($this_file, ".") + 1);
                    $link = str_replace("[link]", "$directory/" . urlencode($this_file), $return_link);
                    $file_url = $directory.'/'.urlencode($this_file);
                    $php_file_tree .= "<li class=\"pft-file " . strtolower($ext) . "\"><a onclick='return get_file(\"$file_url\")' href=\"$link\">" . htmlspecialchars($this_file) . "</a></li>";
                }
            }
        }
        $php_file_tree .= "</ul>";
    }
    return $php_file_tree;
}

// For PHP4 compatibility
function php4_scandir($dir) {
    $dh  = opendir($dir);
    while( false !== ($filename = readdir($dh)) ) {
        $files[] = $filename;
    }
    sort($files);
    return($files);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Directory Reader</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <style type="text/css">
        .php-file-tree {
            font-family: Georgia;
            font-size: 12px;
            letter-spacing: 1px;	line-height: 1.5;
        }

        .php-file-tree A {
            color: #000000;
            text-decoration: none;
        }

        .php-file-tree A:hover {
            color: #666666;
        }

        .php-file-tree .open {
            font-style: italic;
        }

        .php-file-tree .closed {
            font-style: normal;
        }

        .php-file-tree .pft-directory {
            font-weight: bold;
        }
    </style>

    <!-- Makes the file tree(s) expand/collapsae dynamically -->
    <script type="text/javascript">
        function init_php_file_tree() {
            if (!document.getElementsByTagName) return;

            var aMenus = document.getElementsByTagName("LI");
            for (var i = 0; i < aMenus.length; i++) {
                var mclass = aMenus[i].className;
                if (mclass.indexOf("pft-directory") > -1) {
                    var submenu = aMenus[i].childNodes;
                    for (var j = 0; j < submenu.length; j++) {
                        if (submenu[j].tagName == "A") {

                            submenu[j].onclick = function() {
                                var node = this.nextSibling;

                                while (1) {
                                    if (node != null) {
                                        if (node.tagName == "UL") {
                                            var d = (node.style.display == "none")
                                            node.style.display = (d) ? "block" : "none";
                                            this.className = (d) ? "open" : "closed";
                                            return false;
                                        }
                                        node = node.nextSibling;
                                    } else {
                                        return false;
                                    }
                                }
                                return false;
                            }

                            submenu[j].className = (mclass.indexOf("open") > -1) ? "open" : "closed";
                        }

                        if (submenu[j].tagName == "UL")
                            submenu[j].style.display = (mclass.indexOf("open") > -1) ? "block" : "none";
                    }
                }
            }
            return false;
        }

        window.onload = init_php_file_tree;

        //Link to file
        function get_file($var) {
            var file_fix = $var.split('+').join('%20');
            var baseurl = window.location.origin+window.location.pathname;
            window.open(baseurl + file_fix);
            return false;
        }
    </script>
</head>

<body>

<h1>Directory Reader</h1>
<?php
$host = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
// This links the user to http://example.com/?file=filename.ext
echo php_file_tree($foldername, "".$host."?file=[link]/", $allowed_extensions);

// This links the user to http://example.com/?file=filename.ext and only shows image files
//$allowed_extensions = array("gif", "jpg", "jpeg", "png");
//echo php_file_tree($_SERVER['DOCUMENT_ROOT'], "http://example.com/?file=[link]/", $allowed_extensions);

// This displays a JavaScript alert stating which file the user clicked on
//echo php_file_tree("demo/", "javascript:alert('You clicked on [link]');");

?>

</body>

</html>
