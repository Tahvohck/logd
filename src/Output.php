<?php

class Output
{
    public function foutput($args)
    {
        global $block_new_output,$translator;

        if ($block_new_output) {
            return;
        }

        if (is_array($args[0])) {
            $args = $args[0];
        }
        if (is_bool($args[0]) && array_shift($args)) {
            $schema = array_shift($args);
            $args[0] = $translator->translate($args[0], $schema);
        } else {
            $args[0] = $translator->translate($args[0]);
        }
        call_user_func_array(array($this,"output_notl"), $args);
    }
    public function output_notl($indata){
        global $output, $block_new_output,$translator;

        if ($block_new_output) return;

        $args = func_get_args();
        //pop true off the end if we have it
        $last = array_pop($args);
        if ($last!==true){
            array_push($args,$last);
            $priv = false;
        }else{
            $priv = true;
        }
        $out = $indata;
        $args[0]=&$out;
        //apply variables
        if (count($args)>1){
            //special case since we use `% as a color code so often.
            $out = str_replace("`%","`%%",$out);
            $out = call_user_func_array("sprintf",$args);
        }
        //holiday text
        if ($priv==false) $out = holidayize($out,'output');
        //`1`2 etc color & formatting
        $out = $this->appoencode($out,$priv);
        //apply to the page.
        $output.=$translator->tlbutton_pop().$out;
        $output.="\n";
    }
    public function appoencode($data,$priv=false){
        global $nestedtags,$session;
        $start = 0;
        $out="";
        if( ($pos = strpos($data, "`")) !== false) {
            global $nestedtags;
            if (!isset($nestedtags['font'])) $nestedtags['font']=false;
            if (!isset($nestedtags['div'])) $nestedtags['div']=false;
            if (!isset($nestedtags['i'])) $nestedtags['i']=false;
            if (!isset($nestedtags['b'])) $nestedtags['b']=false;
            if (!isset($nestedtags['<'])) $nestedtags['<']=false;
            if (!isset($nestedtags['>'])) $nestedtags['>']=false;
            if (!isset($nestedtags['h'])) $nestedtags['h']=false;

            static $colors = array(
                "1" => "colDkBlue",
                "2" => "colDkGreen",
                "3" => "colDkCyan",
                "4" => "colDkRed",
                "5" => "colDkMagenta",
                "6" => "colDkYellow",
                "7" => "colDkWhite",
                "~" => "colBlack",
                "!" => "colLtBlue",
                "@" => "colLtGreen",
                "#" => "colLtCyan",
                "\$" => "colLtRed",
                "%" => "colLtMagenta",
                "^" => "colLtYellow",
                "&" => "colLtWhite",
                ")" => "colLtBlack",
                "e" => "colDkRust",
                "E" => "colLtRust",
                "g" => "colXLtGreen",
                "G" => "colXLtGreen",
                "j" => "colMdGrey",
                "J" => "colMdBlue",
                "k" => "colaquamarine",
                "K" => "coldarkseagreen",
                "l" => "colDkLinkBlue",
                "L" => "colLtLinkBlue",
                "m" => "colwheat",
                "M" => "coltan",
                "p" => "collightsalmon",
                "P" => "colsalmon",
                "q" => "colDkOrange",
                "Q" => "colLtOrange",
                "R" => "colRose",
                "T" => "colDkBrown",
                "t" => "colLtBrown",
                "V" => "colBlueViolet",
                "v" => "coliceviolet",
                "x" => "colburlywood",
                "X" => "colbeige",
                "y" => "colkhaki",
                "Y" => "coldarkkhaki",
            );
            do {
                ++$pos;
                if ($priv === false){
                    $out .= HTMLEntities(substr($data, $start, $pos - $start - 1), ENT_COMPAT, getsetting("charset", "ISO-8859-1"));
                } else {
                    $out .= substr($data, $start, $pos - $start - 1);
                }
                $start = $pos + 1;
                if(isset($colors[$data[$pos]])) {
                    if ($nestedtags['font']) $out.="</span>";
                    else $nestedtags['font']=true;
                    $out.="<span class='".$colors[$data[$pos]]."'>";
                } else {
                    switch($data[$pos]){
                        case "n":
                            $out.="<br>\n";
                            break;
                        case "0":
                            if ($nestedtags['font']) $out.="</span>";
                            $nestedtags['font'] = false;
                            break;
                        case "b":
                            if ($nestedtags['b']){
                                $out.="</b>";
                                $nestedtags['b']=false;
                            }else{
                                $nestedtags['b']=true;
                                $out.="<b>";
                            }
                            break;
                        case "i":
                            if ($nestedtags['i']) {
                                $out.="</i>";
                                $nestedtags['i']=false;
                            }else{
                                $nestedtags['i']=true;
                                $out.="<i>";
                            }
                            break;
                        case "c":
                            if ($nestedtags['div']) {
                                $out.="</div>";
                                $nestedtags['div']=false;
                            }else{
                                $nestedtags['div']=true;
                                $out.="<div align='center'>";
                            }
                            break;
                        case "h":
                            if ($nestedtags['h']) {
                                $out.="</em>";
                                $nestedtags['h']=false;
                            }else{
                                $nestedtags['h']=true;
                                $out.="<em>";
                            }
                            break;
                        case ">":
                            if ($nestedtags['>']){
                                $nestedtags['>']=false;
                                $out.="</div>";
                            }else{
                                $nestedtags['>']=true;
                                $out.="<div style='float: right; clear: right;'>";
                            }
                            break;
                        case "<":
                            if ($nestedtags['<']){
                                $nestedtags['<']=false;
                                $out.="</div>";
                            }else{
                                $nestedtags['<']=true;
                                $out.="<div style='float: left; clear: left;'>";
                            }
                            break;
                        case "H":
                            if ($nestedtags['div']) {
                                $out.="</span>";
                                $nestedtags['div']=false;
                            }else{
                                $nestedtags['div']=true;
                                $out.="<span class='navhi'>";
                            }
                            break;
                        case "w":
                            global $session;
                            if(!isset($session['user']['weapon']))
                                $session['user']['weapon']="";
                            $out.=$this->appoencode($session['user']['weapon'],$priv);
                            break;
                        case "`":
                            $out.="`";
                            ++$pos;
                            break;
                        default:
                            $out.="`".$data[$pos];
                    }
                }
            } while( ($pos = strpos($data, "`", $pos)) !== false);
        }
        if ($priv === false){
            $out .= HTMLEntities(substr($data, $start), ENT_COMPAT, getsetting("charset", "ISO-8859-1"));
        } else {
            $out .= substr($data, $start);
        }
        return $out;
    }
}