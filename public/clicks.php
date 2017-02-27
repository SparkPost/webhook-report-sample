<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../store-pg.php';

function array_group_by($flds, $arr) {
  $groups = array();
  foreach ($arr as $rec) {
    $keys = array_map(function($f) use($rec) { return $rec[$f]; }, $flds);
    $k = implode('@', $keys);
    if (isset($groups[$k])) {
      $groups[$k][] = $rec;
    } else {
      $groups[$k] = array($rec);
    }
  }
  return $groups;
}

function summarise_events($inputpath, $types, $fields, $enrichfn, $groupbyflds) {
  // Accept JSON POST request
  $payload = json_decode(file_get_contents($inputpath), true);

  // Unpack event structure
  $unpack_event = function($event) {
    $evt = $event['msys'];
    $evtclass = array_keys($evt)[0];
    return $evt[$evtclass];
  };
  $allevents = array_map($unpack_event, $payload);

  // Filter out uninteresting event types
  $event_filter = function($event) use ($types) {
    return in_array($event['type'], $types);
  };
  $events = array_filter($allevents, $event_filter);

  // Filter out uninteresting fields
  $goodfieldmap = array_combine($fields, array_fill(0, count($fields), 1));
  $field_filter = function($event) use ($goodfieldmap) {
    return array_intersect_key($event, $goodfieldmap);
  };
  $leanevents = array_map($field_filter, $events);

  // Enrich events with supplied visitor function
  $richevents = array_map($enrichfn, $leanevents);

  // Group by OS
  $osgroups = array_group_by($groupbyflds, $richevents);

  // Count click events for each OS 
  return array_map(function($events) { return count($events); }, $osgroups);
}

// --------------------------------------------------------------------------------------
$interestingevents = ['click'];
$interestingfields = ['user_agent'];
$groupbyfields = ['os'];

// Enrich events with user agent details
$agent = new Jenssegers\Agent\Agent();
$parse_user_agent = function($event) use($agent) {
  $agent->setUserAgent($event['user_agent']);
  $event['os'] = $agent->platform() ? $agent->platform() : 'unknown';
  return $event;
};

$store = new Summaries(null);

// Enable testing from commandline
if (!isset($_SERVER['REQUEST_METHOD'])) {
  $ossummary = summarise_events('../../events.json', $interestingevents, $interestingfields, $parse_user_agent, $groupbyfields);
  $store->update($ossummary);
  header('Content-Type: application/json');
  echo(json_encode($store->get(), JSON_PRETTY_PRINT));
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ossummary = summarise_events('php://input', $interestingevents, $interestingfields, $parse_user_agent, $groupbyfields);
    $store->update($ossummary);
    header('Content-Type: application/json');
    echo(json_encode(array('ok'=>TRUE)));
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  header('Content-Type: application/json');
  echo(json_encode($store->get(), JSON_PRETTY_PRINT));
} else {
  header('HTTP/1.1 405 Method not allowed');
  header('Allow: GET, POST');
  echo('Not allowed');
}

?>
