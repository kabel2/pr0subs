<?php

Flight::register('view', 'Smarty', [], function( $smarty ) {
    $smarty->template_dir = SMARTY . '/templates/';
    $smarty->compile_dir = SMARTY . '/templates_c/';
    $smarty->config_dir = SMARTY . '/config/';
    $smarty->cache_dir = SMARTY . '/cache/';
});

Flight::map('render', function( $template, $data ) {
    Flight::view()
            ->assign($data);
    Flight::view()
            ->display($template);
});

/* Seesion handling */

Flight::map('sessionStart', function() {
    Session::sessionStart(SESSION_NAME);
});

Flight::map('sessionStop', function() {
    Session::sessionStop();
});

Flight::map('logout', function() {
    Flight::sessionStop();
    Flight::redirect('/login', 303);
});

Flight::map('select', function( $table, $columns = '*', $where = NULL, $limit = NULL, $opt = NULL, $debug = FALSE ) {
    return DB::select($table, $columns, $where, $limit, $opt, $debug);
});

Flight::map('update', function( $table, $id, $data, $debug = FALSE ) {
    DB::update($table, $id, $data, $debug);
});

Flight::map('delete', function( $table, $id, $debug = FALSE, $col = NULL ) {
    return DB::delete($table, $id, $col, $debug);
});

Flight::map('insert', function( $table, $data, $debug = FALSE ) {
    return DB::insert($table, $data, $debug);
});

// wenn die beta vorbei ist, wird die funktion wieder benutzt
Flight::map('is_user_allowed', function() {
    $user_ip = md5(Flight::request()->ip);
    $user_data = Flight::select('pr0verter', '*', 'tstamp = ' . "'" . $user_ip . "'", 1);
    if (count($user_data) <= 0) {
        $data['tstamp'] = $user_ip;
        $data['datetime'] = 0;
        Flight::insert('pr0verter', $data);

        return TRUE;
    } else {
        if (time() - $user_data[0]['datetime'] < TIME_TO_WAIT) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
});

Flight::map('set_user_time', function() {
    $user_ip = md5(Flight::request()->ip);
    $user_data = Flight::select('pr0verter', '*', 'tstamp = ' . "'" . $user_ip . "'", 1);
    $data['datetime'] = time();
    Flight::update('pr0verter', $user_data[0]['id'], $data);
});

Flight::map('random_string', function() {
    $milliseconds = round(microtime(TRUE) * 1000);

    return md5($milliseconds . uniqid(SESSION_NAME . '_', TRUE));
});

Flight::map('get_url_file_size', function( $url ) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);

    curl_exec($ch);
    $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

    curl_close($ch);

    return $size;
});

Flight::map('is_supported', function( $format ) {
    $supportedTypes = [ 'webm', 'mp4', 'mkv', 'mov', 'avi', 'wmv', 'flv', '3gp', 'gif', 'gifv'];
    foreach ($supportedTypes as $type) {
        if (strcasecmp($type, $format) == 0) {
            return TRUE;
        }
    }

    return FALSE;
});

Flight::map('download', function( $url, $save_to ) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'progress');
    $file_content = curl_exec($ch);
    curl_close($ch);

    $downloaded_file = fopen($save_to, 'w');
    fwrite($downloaded_file, $file_content);
    fclose($downloaded_file);

    function progress($resource, $download_size, $downloaded) {
        if ($download_size > 0) {
            echo $downloaded / $download_size * 100;
        }
    }

});

Flight::map('hasSound', function($file) {
    $output = exec("/./home/ffmpeg/ffprobe -v error -of flat=s=_ -show_entries stream=codec_type " . DOWNLOAD_PATH . $file);
    // streams_stream_0_codec_type="video"
    // streams_stream_1_codec_type="audio"

    if (preg_match('/audio/', $output)) {
        return "on";
    } else {
        return "off";
    }
});

Flight::map('get_bitrate', function( $duration, $limit, $sound ) {

    if ($limit == 0) {
        $limit = DEFAULT_MB;
    }

    $duration = min($duration, MAX_DURATION_IN_SEC);


    $bitrate = ($limit * BITS_IN_KILOBYTE) / $duration;

    if ($sound === 'on') {
        $bitrate = $bitrate - AUDIO_BITRATE;
    }

    return $bitrate . 'k';
});

Flight::map('get_resolution', function( $px, $py, $duration ) {
    if ($duration > 30) {
        if ($duration < 60) {
            if (( $px > 490 ) AND ( $px < 800 )) {
                $py /= 1.5;
                $px /= 1.5;
            }
            if ($px > 800) {
                $py /= 2;
                $px /= 2;
            }
        }
        if ($duration > 60) {
            if (( $px > 450 ) AND ( $px < 800 )) {
                $py /= 2;
                $px /= 2;
            }
            if ($px > 800) {
                $py /= 2.7;
                $px /= 2.7;
            }
        }
    }
    $py = round($py);
    $px = round($px);
    // resolution has to be even
    if ($py % 2 != 0) {
        $py++;
    }
    if ($px % 2 != 0) {
        $px++;
    }
    return $py . 'x' . ($px);
});

Flight::map('resize', function( $random_name, $format, $bitrate, $max_size, $resolution, $sound, $videoResolution, $subCommand ) {
    exec('mv ' . DOWNLOAD_PATH . $random_name . '.' . $format . ' ' . DOWNLOAD_PATH . $random_name . '.source.' . $format);
    $max_size = round((($max_size / 8192) * 1047576));
    $log1 = DOWNLOAD_PATH . $random_name . '.log1';
    $log2 = DOWNLOAD_PATH . $random_name . '.log';
    $logfile = DOWNLOAD_PATH . $random_name;

    $firstPass = ' -vb ' . $bitrate . ' -preset fast -t 179 -profile:v baseline -level 3.1 -passlogfile ' . $logfile . ' -pass 1 ';
    $secondPass = ' -fs ' . $max_size . ' -vb ' . $bitrate . ' -preset fast -t 179 -profile:v baseline -level 3.1 -passlogfile ' . $logfile . ' -pass 2 ';

    if ($sound === 'on') {
        $firstPass = $firstPass . " -c:a aac -b:a 120k -strict -2 ";
        $secondPass = $secondPass . " -c:a aac -b:a 120k -strict -2 ";
    } else {
        $firstPass = $firstPass . " -an ";
        $secondPass = $secondPass . " -an ";
    }

    switch ($videoResolution) {
        case 'Automatisch':
            $firstPass = $firstPass . ' -s ' . $resolution . ' ';
            $secondPass = $secondPass . ' -s ' . $resolution . ' ';
            break;
        case '720p':
            $firstPass = $firstPass . ' -s 1280x720 ';
            $secondPass = $secondPass . ' -s 1280x720 ';
            break;
        case '480p':
            $firstPass = $firstPass . ' -s 854x480 ';
            $secondPass = $secondPass . ' -s 854x480 ';
            break;
        case '360p':
            $firstPass = $firstPass . ' -s 640x360 ';
            $secondPass = $secondPass . ' -s 640x360 ';
            break;
    }

    if ($subCommand !== '') {
        $secondPass = $secondPass . " " . $subCommand . " ";
        $outputFile = DOWNLOAD_PATH . $random_name . '.subbed.mp4'; // chrome & firefox cachen video vom editor-> "altes" video ohne subs, deshalb anderer dateiname
    } else {
        $outputFile = DOWNLOAD_PATH . $random_name . '.mp4';
    }

    shell_exec('/./home/ffmpeg/ffmpeg -y -i ' . DOWNLOAD_PATH . $random_name . '.source.' . $format . $firstPass . $outputFile . ' 2>' . $log1 . ' && /./home/ffmpeg/ffmpeg -y -i ' . DOWNLOAD_PATH . $random_name . '.source.' . $format . $secondPass . $outputFile . ' > /dev/null 2>' . $log2 . ' &');
});

Flight::map('remove_unused_files', function($random_name) {
    $random_name = explode(".", $random_name)[0];
    if (file_exists(DOWNLOAD_PATH . $random_name . ".log")) {
        $remove_files = 'rm ' . DOWNLOAD_PATH . $random_name . ".log "
                . DOWNLOAD_PATH . $random_name . ".log1 "
                . DOWNLOAD_PATH . $random_name . "-0.log.mbtree "
                . DOWNLOAD_PATH . $random_name . "-0.log "
                . DOWNLOAD_PATH . $random_name . ".source.mp4 ";
        shell_exec($remove_files);
    }
});

Flight::map('go_to_editor', function( $file ) {
    echo '' . '<form action="editor/' . $file . '" method="post" name="editorForm">' . '</form>' . '<script type="text/javascript">' . 'document.editorForm.submit();' . '</script>';
});

Flight::map('go_to_status', function( $random_name, $format, $duration ) {
    echo '' . '<form action="http://pr0verter.de/subs/status" method="post" name="form">' . '<input type="hidden" name="random_name" value="' . $random_name . '" />' . '<input type="hidden" name="format" value="' . $format . '" />' . '<input type="hidden" name="duration" value="' . $duration . '" />' . '</form>' . '<script type="text/javascript">' . 'document.form.submit();' . '</script>';
});

Flight::map('convert', function( $random_name, $format, $max_size, $limit, $sound, $videoResolution, $subCommand ) {
    // limit = 0, sound, video resolution
    if (strcasecmp($format, 'gif') == 0) {
        // ID3 unterstützt das natürlich nicht..
        // ffprobe auch nicht, ffmpeg codiert das video um die Dauer zu bekommen
        shell_exec('/./home/ffmpeg/ffmpeg -i ' . DOWNLOAD_PATH . $random_name . '.gif' . ' -f null - 2>' . DOWNLOAD_PATH . $random_name . '.duration.log');
        // duration_string = hours:minutes:seconds.ms 
        $duration_string = '';
        // todo: regex so anpassen dass duration_string wirklich ein string ist... 
        preg_match('/(\d+:\d+:\d+.\d+)/', file_get_contents(DOWNLOAD_PATH . $random_name . '.duration.log'), $duration_string);
        $duration_array = explode(":", $duration_string[0]);
        $duration = $duration_array[0] * 3600 + $duration_array[1] * 60 + $duration_array[2];
        $bitrate = Flight::get_bitrate($duration, $limit, '');
        //Flight::set_user_time();
        // baseline profile doesn't support 4:4:4, deshalb der "workaround" mit scale
        // baseline weil pr0 nur das unterstützt 
        shell_exec('/./home/ffmpeg/ffmpeg -y -i ' . DOWNLOAD_PATH . $random_name . '.gif' . ' -vb ' . $bitrate . ' -an -t 119 -profile:v baseline -level 3.0 -vf scale=' . '"trunc(in_w/2)*2:trunc(in_h/2)*2"' . ' -pix_fmt yuv420p ' . DOWNLOAD_PATH . $random_name . '.mp4 2>' . DOWNLOAD_PATH . $random_name . '.log');
        Flight::go_to_status($random_name, $format, $duration);
    } elseif (strcasecmp($format, 'webm') == 0) {
        $getID3 = new getID3;
        $meta_data = $getID3->analyze(DOWNLOAD_PATH . $random_name . '.' . $format);
        $height = explode('=', exec('/./home/ffmpeg/ffprobe -v error -of flat=s=_ -select_streams v:0 -show_entries stream=height ' . DOWNLOAD_PATH . $random_name . '.' . $format))[1];
        $width = explode('=', exec('/./home/ffmpeg/ffprobe -v error -of flat=s=_ -select_streams v:0 -show_entries stream=width ' . DOWNLOAD_PATH . $random_name . '.' . $format))[1];
        $bitrate = Flight::get_bitrate($meta_data['playtime_seconds'], $limit, $sound);
        $resolution = Flight::get_resolution($height, $width, $meta_data['playtime_seconds']);
        Flight::resize($random_name, $format, $bitrate, $max_size, $resolution, $sound, $videoResolution, $subCommand);
        //Flight::set_user_time();
        Flight::go_to_status($random_name, $format, $meta_data['playtime_seconds']);
    } else {
        $getID3 = new getID3;
        $meta_data = $getID3->analyze(DOWNLOAD_PATH . $random_name . '.' . $format);
        $bitrate = Flight::get_bitrate($meta_data['playtime_seconds'], $limit, $sound);
        $resolution = Flight::get_resolution($meta_data['video']['resolution_y'], $meta_data['video']['resolution_x'], $meta_data['playtime_seconds']);
        Flight::resize($random_name, $format, $bitrate, $max_size, $resolution, $sound, $videoResolution, $subCommand);
        //Flight::set_user_time();
        if ($subCommand === '') {
            Flight::go_to_status($random_name, $format, $meta_data['playtime_seconds']);
        }
    }
});

Flight::map('parse_url', function( $url, $format ) {
    // add yt downloader?
    if (strcasecmp($format, 'gifv') == 0) {
        return preg_replace('gifv', 'mp4', $url);
    }
    return $url;
});

Flight::map('parse_format', function( $url, $format ) {
    // $url wird später gebraucht um das format zu bekommen 
    if (strcasecmp($format, 'gifv') == 0) {
        return 'mp4';
    }
    return $format;
});
Flight::map('createSRT', function( $jsonArray) {
    $jsonInfos = $jsonArray[0]; // sound on/off, resolution...
    $jsonSubs = $jsonArray[1]; // subs
    $random_name = filter_var($jsonInfos['file'], FILTER_SANITIZE_STRING);
    $srtFile = fopen("/var/www/html/subs/data/" . $random_name . ".srt", "a") or die("Unable to open file!");

    for ($i = 0; $i < count($jsonSubs); $i++) {
        $id = floatval($jsonSubs[$i]['id']) + 1;
        $startString = $jsonSubs[$i]['start'];
        $start = "00:" . floatval($startString / 60 % 60) . ":" . floatval($startString) % 60 . "." . (int) (($startString - floor($startString)) * 1000);
        $stopString = $jsonSubs[$i]['end'];
        $stop = "00:" . floatval($stopString / 60 % 60) . ":" . floatval($stopString) % 60 . "." . (int) (($stopString - floor($stopString)) * 1000);
        $text = filter_var($jsonSubs[$i]['text'], FILTER_SANITIZE_STRING);
        // h: m : s.ms
        //00:01:52,840 --> 00:01:55,308
        if (is_numeric($id) && $text !== null) {
            if (strpos($text, "/n") !== FALSE) {
                $text = explode("/n", $text);
                fwrite($srtFile, $id . PHP_EOL . $start . " --> " . $stop . PHP_EOL . $text[0] . PHP_EOL . PHP_EOL);
                for ($b = 1; $b < count($text); $b++) {
                    $text[$b] = str_replace("/n", "", $text[$b]);
                    fwrite($srtFile, $text[$b] . PHP_EOL . PHP_EOL);
                }
            } else {
                fwrite($srtFile, $id . PHP_EOL . $start . " --> " . $stop . PHP_EOL . $text . PHP_EOL . PHP_EOL);
            }
        }
    }
    fclose($srtFile);
    $fontSize = $jsonInfos['fontSize'];
    $targetVideoSize = $jsonInfos['targetVideoSize']; // $jsonInfos->targetVideoSize funtzt nicht??
    $sound = $jsonInfos['sound'];
    $videoResolution = $jsonInfos['videoResolution'];
    $supportedTypes = array("Automatisch", "nix machen/beibehalten", "720p", "480p", "360p");

    if (!is_int($fontSize) && !is_int($targetVideoSize) && !($sound === 'on' || $sound === 'off') && !in_array($videoResolution, $supportedTypes)) {
        echo ":/";
        return;
    }

    $subCommand = '-vf "subtitles=' . DOWNLOAD_PATH . $random_name . '.srt:force_style=' . 'Fontsize=' . $fontSize . '"';

    // wenn 1 log da ist sind auch die anderen da, ausser es gab einen fehler 'Conversation Failed'
    // dann stehts im php5-fpm.log 
    if (file_exists(DOWNLOAD_PATH . $random_name . ".log")) {
        shell_exec('rm ' . DOWNLOAD_PATH . $random_name . ".log");
        shell_exec('rm ' . DOWNLOAD_PATH . $random_name . ".log1");
        shell_exec('rm ' . DOWNLOAD_PATH . $random_name . "-0.log.mbtree");
        shell_exec('rm ' . DOWNLOAD_PATH . $random_name . "-0.log");
    }
    
    Flight::convert($random_name, 'mp4', $targetVideoSize * 8192, $targetVideoSize, $sound, $videoResolution, $subCommand);
    $getID3 = new getID3;
    $meta_data = $getID3->analyze(DOWNLOAD_PATH . $random_name . '.subbed.mp4');
    Flight::go_to_status($random_name . ".subbed", 'mp4', $meta_data['playtime_seconds']);
});
