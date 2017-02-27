<?php
class Summaries {
  function __construct($url) {
    if (!$url) {
      $url = getenv('DATABASE_URL');
    }
    extract(parse_url($url));
    $dbname = substr($path, 1);
    $this->db = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
  }

  function __destruct() {
    $this->db = null;
  }

  // Aggregate os => count summaries and store in SQLite db
  function update($summaries) {
    try {
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $this->db->beginTransaction();

      $this->db->query('CREATE TABLE IF NOT EXISTS summaries (os TEXT PRIMARY KEY, total INTEGER)');

      $oses = array_keys($summaries);
      foreach($oses as $os) {
        $cnt = $summaries[$os];
        $this->db->query("INSERT INTO summaries (os, total) VALUES ('$os', 0) ON CONFLICT DO NOTHING ");
        $this->db->query("UPDATE summaries SET total=total+$cnt WHERE os='$os'");
      }

      $this->db->commit();

      return TRUE;
    } catch (Exception $e) {
      $this->db->rollBack();
      echo($e->getMessage());
      return FALSE;
    }
  }

  function get() {
    $result = array();
    foreach ($this->db->query('SELECT os, total FROM summaries') as $row) {
      $result[] = array($row['os'], $row['total']);
    }
    return $result;
  }
}
?>
