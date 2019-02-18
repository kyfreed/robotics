<?php

class Sql {

	 private static $pdo;

	 // Set the PDO once to initialize
	 public static function setPDO($pdo) {
		  self::$pdo = $pdo;
	 }

	 // Or set the MySQL credentials to connect if a query is called
	 private static $auth_info;

	 public static function auth($host, $user, $pass, $db, $port = 3306) {
		  self::$auth_info = array(
				'host' => $host,
				'user' => $user,
				'pass' => $pass,
				'db' => $db,
				'port' => $port,
		  );
	 }

	 private static function getPDO() {
		  if (!self::$pdo) {
				self::$pdo = mysqli_connect(
						  self::$auth_info['host'], self::$auth_info['user'], self::$auth_info['pass'], self::$auth_info['db'], self::$auth_info['port']
				);
				if (strlen(self::$pdo->connect_error)) {
					 die('Database connection failed. ' . self::$pdo->connect_error);
				}
		  }
		  return self::$pdo;
	 }

	 // Safe ways to insert values into SQL statements
	 public static function now() {
		  return "'" . date('Y-m-d H:i:s') . "'";
	 }
	 public static function dateval($time) {
		  return "'" . date('Y-m-d', $time) . "'";
	 }
	 public static function timeval($time) {
		  return "'" . date('Y-m-d H:i:s', $time) . "'";
	 }
	 public static function val($str) {
		  if ($str === null)
				return 'NULL';
		  if (is_numeric($str) && substr($str, 0, 1) !== "0")
				return $str;
		  return "'" . str_replace("'", "\\'", $str) . "'";
	 }
	 public static function vallist(array $items) {
		  if (empty($items)) {
				return '(NULL)';
		  }
		  $list = '(';
		  $comma = '';
		  foreach ($items as $item) {
				$list .= $comma . self::val($item);
				$comma = ',';
		  }
		  return $list . ')';
	 }

	 /**
	  * Use this version for inserts, updates, deletes
	  * @param String $sql
	  * @return int
	  */
	 public static function exec($sql, $returnLastInsertId = false) {
		  try {
				$rid = self::getPDO()->query($sql);
				$rows = mysqli_affected_rows(self::getPDO());
				if ($rows == -1) {
					 $msg = mysqli_error(self::getPDO());
					 $msg = str_replace('You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use', 'SQL error', $msg);
					 throw new Exception($msg);
				}
				if ($returnLastInsertId) {
					 $return = mysqli_insert_id(self::getPDO());
				} else {
					 $return = $rows;
				}
				return $return;
		  } catch (\Exception $e) {
				if (function_exists('prd')) {
					 prd('SQL QUERY ERROR', ['message' => $e->getMessage(), 'sql' => trim($sql)]);
				}
				echo 'SQL ERROR';
				exit;
		  }
	 }

	 /**
	  * Return a full table of results in a two dimensional array
	  * @param string $sql
	  * @param boolean $include_column_names Default true
	  * @return recordset
	  */
	 public static function query($sql, $include_column_names = true) {
		  if ($include_column_names) {
				$fetch_type = MYSQLI_ASSOC;
				$fetch_type = PDO::FETCH_ASSOC;
		  } else {
				$fetch_type = MYSQLI_NUM;
				$fetch_type = PDO::FETCH_NUM;
		  }
		  $results = array();
		  try {
				$rid = self::getPDO()->query($sql);
				if (!$rid) {
					 $msg = mysqli_error(self::getPDO());
					 $msg = str_replace('You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use', 'SQL error', $msg);
					 throw new Exception($msg);
				} else if($rid === true){
                                    return [];
                                }
                        
			while ($row = $rid->fetch_assoc()) {
				$results[] = $row;
			}
			//	$results = mysqli_fetch_all($rid, $fetch_type);
		  } catch (\Exception $e) {
				if (function_exists('prd')) {
					 prd('SQL QUERY ERROR', ['message' => $e->getMessage(), 'sql' => trim($sql)]);
				}
				echo 'SQL ERROR';
				exit;
		  }
		  return $results;
	 }

	 /**
	  * Return the first record in a one dimensional array.
	  * Or the if_missing value if there is no first record
	  * @param string $sql
	  * @param mixed $if_missing
	  * @return array
	  */
	 public static function queryRow($sql, $if_missing = []) {
		  $qry = self::query($sql);
		  if (count($qry)) {
				return $qry[0];
		  }
		  return $if_missing;
	 }

	 /**
	  * Return the first value of the record.
	  * Or the if_missing value if there is no first record
	  * @param string $sql
	  * @param mixed $if_missing
	  * @return array
	  */
	 public static function queryValue($sql, $if_missing = null) {
		  $qry = self::query($sql);
		  if (count($qry)) {
				$row = $qry[0];
				$columns = array_keys($row);
				return $row[$columns[0]];
		  }
		  return $if_missing;
	 }

	 /// Returns one-dimension array with the first column data as the key and the second column data as the value
	 public static function queryGrouped($sql, $groupColumn) {
		  $results = [];
		  foreach (self::query($sql) as $row) {
				if (isset($results[$row[$groupColumn]])) {
					 $results[$row[$groupColumn]][] = $row;
				} else {
					 $results[$row[$groupColumn]] = [$row];
				}
		  }
		  return $results;
	 }

	 /// Returns one-dimension array with the first column data as the key and the second column data as the value
	 public static function queryGroupedReversed($sql, $groupColumn) {
		  $results = [];
		  foreach (self::query($sql) as $row) {
				if (isset($results[$row[$groupColumn]])) {
					 array_unshift($results[$row[$groupColumn]], $row);
				} else {
					 $results[$row[$groupColumn]] = [$row];
				}
		  }
		  return $results;
	 }

	 /// Returns one-dimension array with the first column data as the key and the second column data as the value
	 public static function queryKeyValue($sql) {
		  $results = [];
		  foreach (self::query($sql, false) as $row) {
				$results[$row[0]] = $row[1];
		  }
		  return $results;
	 }

}