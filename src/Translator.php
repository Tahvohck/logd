<?php

class Translator
{
    public function tlschema($schema = false)
    {
        global $translation_namespace, $translation_namespace_stack, $REQUEST_URI;
        if ($schema === false) {
            $translation_namespace = array_pop($translation_namespace_stack);
            if ($translation_namespace == "") {
                $translation_namespace = translator_uri($REQUEST_URI);
            }
        } else {
            array_push($translation_namespace_stack, $translation_namespace);
            $translation_namespace = $schema;
        }

    }

    public function  translate(
        $indata,
        $namespace = false
    ) {
        if (getsetting("enabletranslation", true) == false) {
            return $indata;
        }
        global $session, $translation_table, $translation_namespace;
        if (!$namespace) {
            $namespace = $translation_namespace;
        }
        $outdata = $indata;
        if (!isset($namespace) || $namespace == "") {
            tlschema();
        }

        $foundtranslation = false;
        if ($namespace != "notranslate") {
            if (!isset($translation_table[$namespace]) ||
                !is_array($translation_table[$namespace])
            ) {
                //build translation table for this page hit.
                $translation_table[$namespace] =
                    translate_loadnamespace($namespace, (isset($session['tlanguage']) ? $session['tlanguage'] : false));
            }
        }

        if (is_array($indata)) {
            //recursive translation on arrays.
            $outdata = array();
            while (list($key, $val) = each($indata)) {
                $outdata[$key] = translate($val, $namespace);
            }
        } else {
            if ($namespace != "notranslate") {
                if (isset($translation_table[$namespace][$indata])) {
                    $outdata = $translation_table[$namespace][$indata];
                    $foundtranslation = true;
                    // Remove this from the untranslated texts table if it is
                    // in there and we are collecting texts
                    // This delete is horrible on very heavily translated games.
                    // It has been requested to be removed.
                    /*
                    if (getsetting("collecttexts", false)) {
                        $sql = "DELETE FROM " . db_prefix("untranslated") .
                            " WHERE intext='" . addslashes($indata) .
                            "' AND language='" . LANGUAGE . "'";
                        db_query($sql);
                    }
                    */
                } elseif (getsetting("collecttexts", false)) {
                    $sql = "INSERT IGNORE INTO " . db_prefix("untranslated") . " (intext,language,namespace) VALUES ('" . addslashes($indata) . "', '" . LANGUAGE . "', " . "'$namespace')";
                    db_query($sql, false);
                }
                tlbutton_push($indata, !$foundtranslation, $namespace);
            } else {
                $outdata = $indata;
            }
        }
        return $outdata;
    }

    public function tlbutton_pop()
    {
        global $translatorbuttons, $session;
        if ($session['user']['superuser'] & SU_IS_TRANSLATOR) {
            return array_pop($translatorbuttons);
        } else {
            return "";
        }
    }
}