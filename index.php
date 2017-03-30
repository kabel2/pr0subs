<?php

use MatthiasMullie\Minify;

define('DIRECTORY', __DIR__);
require 'vendor/autoload.php';
require 'php/config.php';

$css = new Minify\CSS();
$css->add(CSS . 'bootstrap/bootstrap.min.css');
$css->add(CSS . 'pr0verter.css');
$css->minify(CSS . 'main.min.css');

$js = new Minify\JS();
$js->add(JS . 'jquery.min.js');
$js->add(JS . 'upload_form.js');
$js->add(JS . 'editor.js');
$js->add(JS . 'jquery.form.min.js');
$js->add(JS . 'bootstrap/bootstrap.min.js');
$js->minify(JS . 'main.min.js');
Flight::set('flight.log_errors', true);

//Flight::sessionStart();

Flight::route('/', function() {
    Flight::view()
            ->assign('title', TITLE);
    Flight::view()
            ->assign('base_url', BASE_URL);
    Flight::view()
            ->display('html_header.tpl');
    Flight::view()
            ->assign('base_url', BASE_URL);
    Flight::view()
            ->display('html_upload_form.tpl');
    Flight::view()
            ->display('html_footer.tpl');
});

Flight::route('/sendJson', function() {
    $json = json_decode(Flight::request()->data->jsonData, true);
    if ($json !== null) {
        Flight::createSRT($json);
    }
});

Flight::route('/editor/@file', function( $file ) {
    if (file_exists(DOWNLOAD_PATH . $file . ".mp4")) {
        Flight::view()
                ->assign('title', TITLE);
        Flight::view()
                ->assign('base_url', BASE_URL);
        Flight::view()
                ->display('html_header.tpl');
        Flight::view()
                ->assign('file', $file);
        Flight::view()
                ->assign('base_url', BASE_URL);
        Flight::view()
                ->display('html_editor.tpl');
        Flight::view()
                ->display('html_footer.tpl');
    } else {
        echo "Datei existiert nicht";
    }
});

Flight::route('/error', function() {
    Flight::view()
            ->assign('title', TITLE);
    Flight::view()
            ->assign('base_url', BASE_URL);
    Flight::view()
            ->display('html_header.tpl');
    Flight::view()
            ->assign('base_url', BASE_URL);
    Flight::view()
            ->display('html_error.tpl');
    Flight::view()
            ->display('html_footer.tpl');
});

Flight::route('/duration', function() {
    $file_name = Flight::request()->data->file_name;
    $duration = Flight::request()->data->duration;

    if ($file_name !== NULL && $duration !== NULL) {
        if(strpos($file_name, '.') !== FALSE){
            $file_name += explode($file_name, ".")[0];
        }
        if (file_exists(DOWNLOAD_PATH . $file_name . '.log')) {
            $file = file_get_contents(DOWNLOAD_PATH . $file_name . '.log');
            if ($file) {
                if (strpos($file, 'muxing overhead') !== FALSE) {
                    echo 100;
                } else {
                    if (strpos($file, 'Conversion failed') !== FALSE) {
                        echo 420;
                        return;
                    }
                    preg_match_all('/time=(.*?) bitrate/', $file, $last_convert_time);
                    $last_convert_time = array_pop($last_convert_time);
                    if (is_array($last_convert_time)) {
                        $last_convert_time = array_pop($last_convert_time);
                        $time_array = array_reverse(explode(':', $last_convert_time));
                        $convert_time = (float) $time_array[0];
                        if (!empty($time_array[1])) {
                            $convert_time += ( (int) $time_array[1] ) * 60;
                        }
                        if (!empty($time_array[2])) {
                            $convert_time += ( (int) $time_array[2] ) * 60 * 60;
                        }
                        echo round(( $convert_time / $duration ) * 100);
                    } else {
                        echo 0;
                    }
                }
            } else {
                echo 'error';
            }
        } else {
            echo 'error';
        }
    } else {
        echo 'error';
    }
});

Flight::route('/status', function() {
    $random_name = Flight::request()->data->random_name;
    $format = Flight::request()->data->format;
    $duration = Flight::request()->data->duration;
    $url = "editor/";
    $sub = "";
    if ($random_name !== NULL && $format !== NULL && $duration !== NULL) {
        if(strpos($random_name, '.') !== FALSE){
            $random_name = explode('.', $random_name)[0];
            $url = "show/";
            $sub = ".subbed";
        } 
        if (file_exists(DOWNLOAD_PATH . $random_name . '.log')) {
            Flight::view()
                    ->assign('title', TITLE);
            Flight::view()
                    ->assign('base_url', BASE_URL);
            Flight::view()
                    ->display('html_header.tpl');
            Flight::view()
                    ->assign('duration', $duration);
            Flight::view()
                    ->assign('file_name', $random_name);
            Flight::view()
                    ->assign('subbed', $sub);
            Flight::view()
                    ->assign('url', $url);
            Flight::view()
                    ->assign('base_url', BASE_URL);
            Flight::view()
                    ->display('html_status.tpl');
            Flight::view()
                    ->display('html_footer.tpl');
        }
    } else {
        Flight::redirect('/');
    }
});

Flight::route('/show/@file', function( $file ) {
    if (file_exists(DOWNLOAD_PATH . $file . '.mp4')) {
        Flight::remove_unused_files($file);
        Flight::view()
                ->assign('title', TITLE);
        Flight::view()
                ->assign('base_url', BASE_URL);
        Flight::view()
                ->display('html_header.tpl');
        Flight::view()
                ->assign('file', $file);
        Flight::view()
                ->assign('base_url', BASE_URL);
        Flight::view()
                ->display('html_show.tpl');
        Flight::view()
                ->display('html_footer.tpl');
    } else {
        Flight::redirect('/');
    }
});

Flight::route('/download/@file', function( $file ) {
    if (file_exists(DOWNLOAD_PATH . $file . '.mp4')) {
        header('Content-type: video');
        header('Content-Disposition: attachment; filename="' . basename($file . '.mp4') . '"');
        header('Content-Transfer-Encoding: binary');
        readfile(DOWNLOAD_PATH . $file . '.mp4');
    } else {
        Flight::redirect('/');
    }
});

Flight::route('/upload', function() {
    $file = Flight::request()->data->file;
    $url = Flight::request()->data->url;

    $random_name = Flight::random_string();
    if ($url !== '') {
        $format = substr($url, strrpos($url, '.') + 1);
        $size = Flight::get_url_file_size($url);
        if ($size < 104857600) {
            if (Flight::is_supported($format)) {
                Flight::download($url, DOWNLOAD_PATH . $random_name . '.' . $format);
                if (strtolower($format) === 'mp4') {
                    //Flight::convert($random_name, $format, $max_size, $limit, $sound, $autoResolution);
                    Flight::go_to_editor($random_name);
                } else {
                    // $max_size, $limit, $sound, $autoResolution unbekannt
                    // möglichst verlustfrei ins mp4 format konvertieren, 
                    // input format kann möglicherweise nicht vom browser angezeigt werden, e.g .mkv
                    // video sollte gleich bleiben, also bitrate, sound, resolution...
                    $max_size = $size;
                    $limit = $size / 1048576;
                    $sound = Flight::hasSound($random_name . "." . $format);
                    $autoResolution = 'off';
                    Flight::convert($random_name, $format, $max_size, $limit, $sound, $autoResolution, '');
                }
            } else {
                Flight::redirect('/error');
            }
        } else {
            Flight::redirect('/error');
        }
    } elseif ($file !== '') {
        $format = pathinfo(Flight::request()->files->file['name'], PATHINFO_EXTENSION);
        $size = Flight::request()->files->file['size'];
        if (Flight::is_supported($format) && $size < 104857600) {
            // 1MB=1024KB=1048576BYTE, 100MB=104857600
            if (move_uploaded_file(Flight::request()->files->file['tmp_name'], DOWNLOAD_PATH . $random_name . '.' . $format)) {
                if (strtolower($format) === 'mp4') {
                    //Flight::convert($random_name, $format, $max_size, $limit, $sound, $autoResolution);
                    Flight::go_to_editor($random_name);
                } else {
                    // $max_size, $limit, $sound, $autoResolution unbekannt
                    // möglichst verlustfrei ins mp4 format konvertieren, 
                    // input format kann möglicherweise nicht vom browser angezeigt werden, e.g .mkv
                    // video sollte gleich bleiben, also bitrate, sound, resolution...
                    $max_size = $size;
                    $limit = Flight::request()->files->file['size'] / 1048576;
                    $sound = Flight::hasSound($random_name . "." . $format);
                    $autoResolution = 'off';
                    Flight::convert($random_name, $format, $max_size, $limit, $sound, $autoResolution, '');
                }
            } else {
                Flight::redirect('/error');
            }
        } else {
            Flight::redirect('/error');
        }
    } else {
        Flight::redirect('/');
    }
});

Flight::route('/help', function() {
    Flight::view()
            ->assign('title', TITLE);
    Flight::view()
            ->assign('base_url', BASE_URL);
    Flight::view()
            ->display('html_header.tpl');
    Flight::view()
            ->assign('base_url', BASE_URL);
    Flight::view()
            ->display('html_help.tpl');
    Flight::view()
            ->display('html_footer.tpl');
});

Flight::route('/contact', function() {
    Flight::view()
            ->assign('title', TITLE);
    Flight::view()
            ->assign('base_url', BASE_URL);
    Flight::view()
            ->display('html_header.tpl');
    Flight::view()
            ->assign('base_url', BASE_URL);
    Flight::view()
            ->display('html_contact.tpl');
    Flight::view()
            ->display('html_footer.tpl');
});

Flight::route('/support', function() {
    Flight::view()
            ->assign('title', TITLE);
    Flight::view()
            ->assign('base_url', BASE_URL);
    Flight::view()
            ->display('html_header.tpl');
    Flight::view()
            ->assign('base_url', BASE_URL);
    Flight::view()
            ->display('html_support.tpl');
    Flight::view()
            ->display('html_footer.tpl');
});



Flight::route('/*', function() {
    Flight::redirect('/');
});

Flight::start();
