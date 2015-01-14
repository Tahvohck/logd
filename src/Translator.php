<?php

class Translator
{
    public function tlschema($schema = false)
    {
        global $translation_namespace,$translation_namespace_stack,$REQUEST_URI;
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
}