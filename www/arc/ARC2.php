<?php
/*
homepage: http://arc.semsol.org/
license:  http://arc.semsol.org/license

class:    ARC2 core class (static, not instantiated)
author:   Benjamin Nowack
version:  2009-02-12 (
                      Fix in ARC2_RDFXMLSerializer
                      Fix in ARC2_TurtleParser
                      Tweak in ARC2_JSONParser
)
*/

class ARC2 {

  function getVersion() {
    return '2009-02-12';
  }

  /*  */
  
  function setStatic($val) {
    static $arc_static = '';
    if ($val) $arc_static = $val;
    if (!$val) return $arc_static;
  }
  
  function getStatic() {
    return ARC2::setStatic('');
  }
  
  /*  */
  
  function getIncPath($f = '') {
    $r = realpath(dirname(__FILE__)) . '/';
    $dirs = array(
      'plugin' => 'plugins',
      'trigger' => 'triggers',
      'store' => 'store', 
      'serializer' => 'serializers', 
      'extractor' => 'extractors', 
      'sparqlscript' => 'sparqlscript', 
      'parser' => 'parsers', 
    );
    foreach ($dirs as $k => $dir) {
      if (preg_match('/' . $k . '/i', $f)) {
        return $r .= $dir . '/';
      }
    }
    return $r;
  }
  
  function getScriptURI() {
    if (isset($_SERVER) && isset($_SERVER['SERVER_NAME'])) {
      return preg_replace('/^([a-z]+)\/.*$/', '\\1', strtolower($_SERVER['SERVER_PROTOCOL'])) . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
    }
    elseif (isset($_SERVER['SCRIPT_FILENAME'])) {
      return 'file://' . realpath($_SERVER['SCRIPT_FILENAME']);
    }
    return 'http://localhost/unknown_path';
  }
  
  function inc($f, $path = '') {
    $prefix = 'ARC2';
    if (preg_match('/^([^\_]+)\_(.*)$/', $f, $m)) {
      $prefix = $m[1];
      $f = $m[2];
    }
    $inc_path = $path ? $path : ARC2::getIncPath($f);
    $path = $inc_path . $prefix . '_' . urlencode($f) . '.php';
    if (file_exists($path)) {
      include_once($path);
      return 1;
    }
    if ($prefix != 'ARC2') {
      $path = $inc_path . strtolower($prefix) . '/' . $prefix . '_' . urlencode($f) . '.php';
      if (file_exists($path)) {
        include_once($path);
        return 1;
      }
    }
    return 0;
  }
  
  /*  */

  function mtime(){
    list($msec, $sec) = explode(" ", microtime());
    return ((float)$msec + (float)$sec);
  }
  
  function x($re, $v, $options = 'si') {
    return preg_match("/^\s*" . $re . "(.*)$/" . $options, $v, $m) ? $m : false;
  }

  /*  */

  function getFormat($val, $mtype = '', $ext = '') {
    ARC2::inc('getFormat');
    return ARC2_getFormat($val, $mtype, $ext);
  }
  
  function getPreferredFormat($default = 'plain') {
    ARC2::inc('getPreferredFormat');
    return ARC2_getPreferredFormat($default);
  }
  
  /*  */
  
  function toUTF8($v) {
    if (utf8_decode($v) == $v) return $v;
		$v = (strpos(utf8_decode(str_replace('?', '', $v)), '?') === false) ? utf8_decode($v) : $v;
    return preg_replace_callback('/([\x00-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xf7][\x80-\xbf]{3}|[\xf8-\xfb][\x80-\xbf]{4}|[\xfc-\xfd][\x80-\xbf]{5}|[^\x00-\x7f])/', array('ARC2', 'getUTF8Char'), $v);
  }
  
  function getUTF8Char($v) {
    $val = $v[1];
    return (strlen(trim($val)) === 1) ? utf8_encode($val) : $val;
  }

  /*  */

  function splitURI($v) {
    $parts = preg_match('/^(.*[\/\#])([^\/\#]+)$/', $v, $m) ? array($m[1], $m[2]) : array($v);
    $specials = array(
      'http://www.w3.org/XML/1998/namespace',
      //'http://www.w3.org/1999/xhtml',
    );
    foreach ($specials as $ns) {
      if (strpos($ns, $parts[0]) === 0) {
        $suffix = substr($ns, strrpos($ns, '/')+1);
        $parts[0] .= $suffix;
        $parts[1] = substr($parts[1], strlen($suffix));
      }
    }
    return $parts;
  }
  
  /*  */

  function getSimpleIndex($triples, $flatten_objects = 1, $vals = '') {
    $r = array();
    foreach ($triples as $t) {
      $skip_t = 0;
      foreach (array('s', 'p', 'o') as $term) {
        $$term = $t[$term];
        /* template var */
        if (isset($t[$term . '_type']) && ($t[$term . '_type'] == 'var')) {
          $val = isset($vals[$$term]) ? $vals[$$term] : '';
          $skip_t = isset($vals[$$term]) ? $skip_t : 1;
          $type = '';
          $type = !$type && isset($vals[$$term . ' type']) ? $vals[$$term . ' type'] : $type;
          $type = !$type && preg_match('/^\_\:/', $val) ? 'bnode' : $type;
          if ($term == 'o') {
            $type = !$type && (preg_match('/\s/s', $val) || !preg_match('/\:/', $val)) ? 'literal' : $type;
            $type = !$type && !preg_match('/[\/]/', $val) ? 'literal' : $type;
          }
          $type = !$type ? 'uri' : $type;
          $t[$term . '_type'] =  $type;
          $$term = $val;
        }
      }
      if ($skip_t) {
        continue;
      }
      if (!isset($r[$s])) $r[$s] = array();
      if (!isset($r[$s][$p])) $r[$s][$p] = array();
      if ($flatten_objects) {
        if (!in_array($o, $r[$s][$p])) $r[$s][$p][] = $o;
      }
      else {
        $o = array('value' => $o);
        foreach (array('lang', 'type', 'datatype') as $suffix) {
          if (isset($t['o_' . $suffix]) && $t['o_' . $suffix]) {
            $o[$suffix] = $t['o_' . $suffix];
          }
          elseif (isset($t['o ' . $suffix]) && $t['o ' . $suffix]) {
            $o[$suffix] = $t['o ' . $suffix];
          }
        }
        if (!in_array($o, $r[$s][$p])) {
          $r[$s][$p][] = $o;
        }
      }
    }
    return $r;
  }
  
  function getTriplesFromIndex($index) {
    $r = array();
    foreach ($index as $s => $ps) {
      foreach ($ps as $p => $os) {
        foreach ($os as $o) {
          $r[] = array(
            's' => $s,
            'p' => $p,
            'o' => $o['value'],
            's_type' => preg_match('/^\_\:/', $s) ? 'bnode' : 'uri',
            'o_type' => $o['type'],
            'o_datatype' => isset($o['datatype']) ? $o['datatype'] : '',
            'o_lang' => isset($o['lang']) ? $o['lang'] : '',
          );
        }
      }
    }
    return $r;
  }
  
  function getMergedIndex() {
    $r = array();
    foreach (func_get_args() as $index) {
      foreach ($index as $s => $ps) {
        if (!isset($r[$s])) $r[$s] = array();
        foreach ($ps as $p => $os) {
          if (!isset($r[$s][$p])) $r[$s][$p] = array();
          foreach ($os as $o) {
            if (!in_array($o, $r[$s][$p])) {
              $r[$s][$p][] = $o;
            }
          }
        }
      }
    }
    return $r;
  }
  
  function getCleanedIndex() {/* removes triples from a given index */
    $indexes = func_get_args();
    $r = $indexes[0];
    for ($i = 1, $i_max = count($indexes); $i < $i_max; $i++) {
      $index = $indexes[$i];
      foreach ($index as $s => $ps) {
        if (!isset($r[$s])) continue;
        foreach ($ps as $p => $os) {
          if (!isset($r[$s][$p])) continue;
          $r_os = $r[$s][$p];
          $new_os = array();
          foreach ($r_os as $r_o) {
            $r_o_val = is_array($r_o) ? $r_o['value'] : $r_o;
            $keep = 1;
            foreach ($os as $o) {
              $del_o_val = is_array($o) ? $o['value'] : $o;
              if ($del_o_val == $r_o_val) {
                $keep = 0;
                break;
              }
            }
            if ($keep) {
              $new_os[] = $r_o;
            }
          }
          if ($new_os) {
            $r[$s][$p] = $new_os;
          }
          else {
            unset($r[$s][$p]);
          }
        }
      }
    }
    /* check r */
    $has_data = 0;
    foreach ($r as $s => $ps) {
      if ($ps) {
        $has_data = 1;
        break;
      }
    }
    return $has_data ? $r : array();
  }
  
  /*  */

  function getStructType($v) {
    /* string */
    if (is_string($v)) return 'string';
    /* triples */
    if (isset($v[0]) && isset($v[0]['s']) && isset($v[0]['p'])) return 'triples';
    /* index */
    foreach ($v as $s => $ps) {
      if (is_array($ps)) {
        foreach ($ps as $p => $os) {
          if (is_array($os) && isset($os[0]) && isset($os[0]['value'])) return 'index';
        }
      }
    }
    /* array */
    return 'array';
  }

  /*  */

  function getComponent($name, $a = '') {
    ARC2::inc($name);
    $prefix = 'ARC2';
    if (preg_match('/^([^\_]+)\_(.+)$/', $name, $m)) {
      $prefix = $m[1];
      $name = $m[2];
    }
    $cls = $prefix . '_' . $name;
    return new $cls($a, new stdClass());
  }
  
  /* parsers */
  
  function getParser($prefix, $a = '') {
    return ARC2::getComponent($prefix . 'Parser', $a);
  }

  function getRDFParser($a = '') {
    return ARC2::getParser('RDF', $a);
  }

  function getRDFXMLParser($a = '') {
    return ARC2::getParser('RDFXML', $a);
  }

  function getTurtleParser($a = '') {
    return ARC2::getParser('Turtle', $a);
  }

  function getRSSParser($a = '') {
    return ARC2::getParser('RSS', $a);
  }

  function getSemHTMLParser($a = '') {
    return ARC2::getParser('SemHTML', $a);
  }

  function getSPARQLParser($a = '') {
    return ARC2::getComponent('SPARQLParser', $a);
  }

  function getSPARQLPlusParser($a = '') {
    return ARC2::getParser('SPARQLPlus', $a);
  }

  function getSPARQLXMLResultParser($a = '') {
    return ARC2::getParser('SPARQLXMLResult', $a);
  }

  function getJSONParser($a = '') {
    return ARC2::getParser('JSON', $a);
  }

  function getSGAJSONParser($a = '') {
    return ARC2::getParser('SGAJSON', $a);
  }

  function getCBJSONParser($a = '') {
    return ARC2::getParser('CBJSON', $a);
  }

  function getSPARQLScriptParser($a = '') {
    return ARC2::getParser('SPARQLScript', $a);
  }

  /* store */

  function getStore($a = '') {
    return ARC2::getComponent('Store', $a);
  }

  function getRemoteStore($a = '') {
    return ARC2::getComponent('RemoteStore', $a);
  }

  function getStoreEndpoint($a = '') {
    return ARC2::getComponent('StoreEndpoint', $a);
  }
  
  /* serializers */

  function getSer($prefix, $a = '') {
    return ARC2::getComponent($prefix . 'Serializer', $a);
  }

  function getTurtleSerializer($a = '') {
    return ARC2::getSer('Turtle', $a);
  }

  function getRDFXMLSerializer($a = '') {
    return ARC2::getSer('RDFXML', $a);
  }

  function getNTriplesSerializer($a = '') {
    return ARC2::getSer('NTriples', $a);
  }

  function getRDFJSONSerializer($a = '') {
    return ARC2::getSer('RDFJSON', $a);
  }

  function getPOSHRDFSerializer($a = '') {
    return ARC2::getSer('POSHRDF', $a);
  }

  /* sparqlscript */

  function getSPARQLScriptProcessor($a = '') {
    return ARC2::getComponent('SPARQLScriptProcessor', $a);
  }

  /*  */
  
}
