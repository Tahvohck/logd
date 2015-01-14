<?php

class Pageparts {
    public function page_header(){
    global $header,$SCRIPT_NAME,$session,$template, $runheaders, $nopopups;
    $nopopups["login.php"]=1;
    $nopopups["motd.php"]=1;
    $nopopups["index.php"]=1;
    $nopopups["create.php"]=1;
    $nopopups["about.php"]=1;
    $nopopups["mail.php"]=1;

    //in case this didn't already get called (such as on a database error)
    translator_setup();
    prepare_template();
    $script = substr($SCRIPT_NAME,0,strrpos($SCRIPT_NAME,"."));
    if ($script) {
        if (!array_key_exists($script,$runheaders))
            $runheaders[$script] = false;
        if (!$runheaders[$script]) {
            modulehook("everyheader", array('script'=>$script));
            if ($session['user']['loggedin']) {
                modulehook("everyheader-loggedin", array('script'=>$script));
            }
            $runheaders[$script] = true;
            modulehook("header-$script");
        }
    }

    $arguments = func_get_args();
    if (!$arguments || count($arguments) == 0) {
        $arguments = array("Legend of the Green Dragon");
    }
    $title = call_user_func_array("sprintf_translate", $arguments);
    $title = holidayize($title,'title');
    $title = sanitize($title);
    calculate_buff_fields();

    $header = $template['header'];
    $header=str_replace("{title}",$title,$header);
    $header.=tlbutton_pop();
}
}