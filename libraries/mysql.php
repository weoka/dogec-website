<?php
/* Modded by PDC (6/14/2017) */
namespace {

    if (!function_exists('\mysql_connect')) {
        define ('MYSQL_ASSOC', 1);
        define ('MYSQL_NUM', 2);
        define ('MYSQL_BOTH', 3);
        define ('MYSQL_CLIENT_COMPRESS', 32);
        define ('MYSQL_CLIENT_SSL', 2048);
        define ('MYSQL_CLIENT_INTERACTIVE', 1024);
        define ('MYSQL_CLIENT_IGNORE_SPACE', 256);

        function mysql_connect(
            $hostname = null,
            $username = null,
            $password = null,
            $new = false,
            $flags = 0
        ) {
            if ($new !== false) {
                trigger_error('Argument $new is no longer supported in PHP > 7', E_USER_WARNING);
            }
            if (null === $hostname) {
                $hostname = ini_get('mysqli.default_host') ?: null;
            }
            if (null === $username) {
                $username = ini_get('mysqli.default_user') ?: null;
            }
            if (null === $password) {
                $password = ini_get('mysqli.default_pw') ?: null;
            }

            $hash = sha1($hostname . $username . $flags);
            if ($hostname{1} != ':' && isset(\Dshafik\MySQL::$connections[$hash])) {
                \Dshafik\MySQL::$last_connection = \Dshafik\MySQL::$connections[$hash]['conn'];
                \Dshafik\MySQL::$connections[$hash]['refcount'] += 1;
                return \Dshafik\MySQL::$connections[$hash]['conn'];
            }

            if ($flags === 0) {
                \Dshafik\MySQL::$last_connection = $conn = mysqli_connect($hostname, $username, $password);
                $conn->hash = $hash;
                \Dshafik\MySQL::$connections[$hash] = ['refcount' => 1, 'conn' => $conn];

                return $conn;
            }

            try {
                \Dshafik\MySQL::$last_connection = $conn = mysqli_init();

                mysqli_real_connect(
                    $conn,
                    $hostname,
                    $username,
                    $password,
                    '',
                    null,
                    '',
                    $flags
                );

                // @codeCoverageIgnoreStart
                // PHPUnit turns the warning from mysqli_real_connect into an exception, so this never runs
                if ($conn === false) {
                    return false;
                }
                // @codeCoverageIgnoreEnd

                $conn->hash = $hash;
                \Dshafik\MySQL::$connections[$hash] = ['refcount' => 1, 'conn' => $conn];

                return $conn;
            } catch (\Throwable $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
                // @codeCoverageIgnoreStart
                // PHPUnit turns the warning into an exception, so this never runs
                return false;
                // @codeCoverageIgnoreEnd
            }
        }

        function mysql_pconnect(
            $hostname = null,
            $username = null,
            $password = null,
            $flags = 0
        ) {
            $hostname = 'p:' . $hostname;
            return mysql_connect($hostname, $username, $password, false, $flags);
        }

        function mysql_close(\mysqli $link = null)
        {
            $isDefault = ($link === null);

            $link = \Dshafik\MySQL::getConnection($link, __FUNCTION__);
            if ($link === null) {
                // @codeCoverageIgnoreStart
                // PHPUnit Warning -> Exception
                return false;
                // @codeCoverageIgnoreEnd
            }

            if (isset(\Dshafik\MySQL::$connections[$link->hash])) {
                \Dshafik\MySQL::$connections[$link->hash]['refcount'] -= 1;
            }

            $return = true;
            if (\Dshafik\MySQL::$connections[$link->hash]['refcount'] == 0) {
                $return = mysqli_close($link);
                unset(\Dshafik\MySQL::$connections[$link->hash]);
            }

            if ($isDefault) {
                Dshafik\MySQL::$last_connection = null;
            }

            return $return;
        }

        function mysql_select_db($databaseName, \mysqli $link = null)
        {
            $link = \Dshafik\MySQL::getConnection($link);

            return mysqli_query(
                $link,
                "USE `" . mysqli_real_escape_string($link, $databaseName) . "`"
            ) !== false;
        }

        function mysql_query($query, \mysqli $link = null) // [PDC MOD]
        {
			$con = \Dshafik\MySQL::getConnection($link);
            $sql = mysqli_query($con, $query);

			if (!$sql) {
				trigger_error(
					mysqli_error($con),
					E_USER_ERROR
				);
			}

			return $sql;
        }

        function mysql_unbuffered_query($query, \mysqli $link = null)
        {
            $link = \Dshafik\MySQL::getConnection($link);
            if (mysqli_real_query($link, $query)) {
                return mysqli_use_result($link);
            }

            return false;
        }

        function mysql_db_query($databaseName, $query, \mysqli $link = null)
        {
            if (mysql_select_db($databaseName, $link)) {
                return mysql_query($query, $link);
            }
            return false;
        }

        function mysql_list_dbs(\mysqli $link = null)
        {
            return mysql_query("SHOW DATABASES", $link);
        }

        function mysql_list_tables($databaseName, \mysqli $link = null)
        {
            $link = \Dshafik\MySQL::getConnection($link);
            $query = sprintf(
                "SHOW TABLES FROM `%s`",
                mysql_real_escape_string($databaseName, $link)
            );
            return mysql_query($query, $link);
        }

        function mysql_list_fields($databaseName, $tableName, \mysqli $link = null)
        {
            $link = \Dshafik\MySQL::getConnection($link);

            $query = sprintf(
                "SHOW COLUMNS FROM `%s`.`%s`",
                mysqli_real_escape_string($link, $databaseName),
                mysqli_real_escape_string($link, $tableName)
            );

            $result = mysql_query($query, $link);

            if ($result instanceof \mysqli_result) {
                $result->table = $tableName;
                return $result;
            }

            trigger_error("mysql_list_fields(): Unable to save MySQL query result", E_USER_WARNING);
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        function mysql_list_processes(\mysqli $link = null)
        {
            return mysql_query("SHOW PROCESSLIST", $link);
        }

        function mysql_error(\mysqli $link = null)
        {
            return mysqli_error(\Dshafik\MySQL::getConnection($link));
        }

        function mysql_errno(\mysqli $link = null)
        {
            return mysqli_errno(\Dshafik\MySQL::getConnection($link));
        }

        function mysql_affected_rows(\mysqli $link = null)
        {
            return mysqli_affected_rows(\Dshafik\MySQL::getConnection($link));
        }

        function mysql_insert_id($link = null) /*|*/
        {
            return mysqli_insert_id(\Dshafik\MySQL::getConnection($link));
        }

        function mysql_result($result, $row, $field = 0)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }

            if (!mysqli_data_seek($result, $row)) {
                trigger_error(
                    sprintf(
                        "mysql_result(): Unable to jump to row %d on MySQL result index %s",
                        $row,
                        spl_object_hash($result)
                    ),
                    E_USER_WARNING
                );
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }

            $found = true;
            if (strpos($field, ".") !== false) {
                list($table, $name) =  explode(".", $field);
                $i = 0;
                $found = false;
		        mysqli_field_seek($result, 0);
                while ($column = mysqli_fetch_field($result)) {
                    if ($column->table == $table && $column->name == $name) {
                        $field = $i;
                        $found = true;
                        break;
                    }
                    $i++;
                }
            }

            $row = mysql_fetch_array($result);
            if ($found && array_key_exists($field, $row)) {
                return $row[$field];
            }

            trigger_error(
                sprintf(
                    "%s(): %s not found in MySQL result index %s",
                    __FUNCTION__,
                    $field,
                    spl_object_hash($result)
                ),
                E_USER_WARNING
            );
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        function mysql_num_rows($result)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }

            $previous = error_reporting(0);
            $rows = mysqli_num_rows($result);
            error_reporting($previous);

            return $rows;
        }

        function mysql_num_fields($result)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
            return mysqli_num_fields($result);
        }

        function mysql_fetch_row($result)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
            return mysqli_fetch_row($result) ?: false;
        }

        function mysql_fetch_array($result)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
            return mysqli_fetch_array($result) ?: false;
        }

        function mysql_fetch_assoc($result) /* : array|null */
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }

            return mysqli_fetch_assoc($result) ?: false;
        }

        function mysql_fetch_object($result, $class = null, array $params = []) /* : object|null */
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }

            if ($class === null) {
                $object = mysqli_fetch_object($result);
            } else {
                $object = mysqli_fetch_object($result, $class, $params);
            }

            return $object ?: false;
        }

        function mysql_data_seek($result, $offset)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
            return mysqli_data_seek($result, $offset);
        }

        function mysql_fetch_lengths($result) /* : array|*/
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
            return mysqli_fetch_lengths($result);
        }

        function mysql_fetch_field($result) /* : object|*/
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
            return mysqli_fetch_field($result);
        }

        function mysql_field_seek($result, $field)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
            return mysqli_field_seek($result, $field);
        }

        function mysql_free_result($result)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
            return mysqli_free_result($result);
        }

        function mysql_field_name($result, $field)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
            return \Dshafik\MySQL::mysqlFieldInfo($result, $field, 'name');
        }

        function mysql_field_table($result, $field)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
            return \Dshafik\MySQL::mysqlFieldInfo($result, $field, 'table');
        }

        function mysql_field_len($result, $field)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
            return \Dshafik\MySQL::mysqlFieldInfo($result, $field, 'len');
        }

        function mysql_field_type($result, $field)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
            return \Dshafik\MySQL::mysqlFieldInfo($result, $field, 'type');
        }

        function mysql_field_flags($result, $field)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
            return \Dshafik\MySQL::mysqlFieldInfo($result, $field, 'flags');
        }

        function mysql_escape_string($unescapedString)
        {
            if (\Dshafik\MySQL::$last_connection === null) {
                trigger_error(
                    sprintf(
                        "%s() is insecure; use mysql_real_escape_string() instead!",
                        __FUNCTION__
                    ),
                    E_USER_NOTICE
                );

                return \Dshafik\MySQL::escapeString($unescapedString);
            }
            return mysql_real_escape_string($unescapedString, null);
        }

        function mysql_real_escape_string($unescapedString, \mysqli $link = null)
        {
            return mysqli_escape_string(\Dshafik\MySQL::getConnection($link), $unescapedString);
        }

        function mysql_stat(\mysqli $link = null)
        {
            return mysqli_stat(\Dshafik\MySQL::getConnection($link));
        }

        function mysql_thread_id(\mysqli $link = null)
        {
            return mysqli_thread_id(\Dshafik\MySQL::getConnection($link));
        }

        function mysql_client_encoding(\mysqli $link = null)
        {
            return mysqli_character_set_name(\Dshafik\MySQL::getConnection($link));
        }

        function mysql_ping(\mysqli $link = null)
        {
            return mysqli_ping($link);
        }

        function mysql_get_client_info(\mysqli $link = null)
        {
            return mysqli_get_client_info(\Dshafik\MySQL::getConnection($link));
        }

        function mysql_get_host_info(\mysqli $link = null)
        {
            return mysqli_get_host_info(\Dshafik\MySQL::getConnection($link));
        }

        function mysql_get_proto_info(\mysqli $link = null)
        {
            return mysqli_get_proto_info(\Dshafik\MySQL::getConnection($link));
        }

        function mysql_get_server_info(\mysqli $link = null)
        {
            return mysqli_get_server_info(\Dshafik\MySQL::getConnection($link));
        }

        function mysql_info(\mysqli $link = null)
        {
            return mysqli_info(\Dshafik\MySQL::getConnection($link));
        }

        function mysql_set_charset($charset, \mysqli $link = null)
        {
            return mysqli_set_charset(\Dshafik\MySQL::getConnection($link), $charset);
        }

        function mysql_db_name($result, $row)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }

            // Alias as per http://lxr.php.net/xref/PHP_5_6/ext/mysql/php_mysql.c#319
            return mysql_result($result, $row, 'Database');
        }

        function mysql_tablename($result, $row)
        {
            if (\Dshafik\MySQL::checkValidResult($result, __FUNCTION__)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }

            // Alias as per http://lxr.php.net/xref/PHP_5_6/ext/mysql/php_mysql.c#321
            return mysql_result($result, $row, 'Table');
        }

        /* Aliases */

        function mysql_fieldname(... $args)
        {
            return mysql_field_name(... $args);
        }

        function mysql_fieldtable(... $args)
        {
            return mysql_field_table(... $args);
        }

        function mysql_fieldlen(... $args)
        {
            return mysql_field_len(... $args);
        }

        function mysql_fieldtype(... $args)
        {
            return mysql_field_type(... $args);
        }

        function mysql_fieldflags(... $args)
        {
            return mysql_field_flags(... $args);
        }

        function mysql_selectdb(... $args)
        {
            return mysql_select_db(... $args);
        }

        function mysql_freeresult(... $args)
        {
            return mysql_free_result(... $args);
        }

        function mysql_numfields(... $args)
        {
            return mysql_num_fields(... $args);
        }

        function mysql_numrows(... $args)
        {
            return mysql_num_rows(... $args);
        }

        function mysql_listdbs(... $args)
        {
            return mysql_list_dbs(... $args);
        }

        function mysql_listtables(... $args)
        {
            return mysql_list_tables(... $args);
        }

        function mysql_listfields(... $args)
        {
            return mysql_list_fields(... $args);
        }

        function mysql_dbname(... $args)
        {
            return mysql_db_name(... $args);
        }

        function mysql_table_name(... $args)
        {
            return mysql_tablename(... $args);
        }
    }
}

namespace Dshafik {

    class MySQL
    {
        public static $last_connection = null;
        public static $connections = [];

        public static function getConnection($link = null, $func = null)
        {
            if ($link !== null) {
                return $link;
            }

            if (static::$last_connection === null) {
                $err = "A link to the server could not be established";
                if ($func !== null) {
                    $err = $func . "(): no MySQL-Link resource supplied";
                }
                trigger_error($err, E_USER_WARNING);
                return false;
            }

            return static::$last_connection;
        }

        public static function mysqlFieldInfo(\mysqli_result $result, $field, $what)
        {
            try {
                $field = mysqli_fetch_field_direct($result, $field);
            } catch (\Exception $e) {
                trigger_error(
                    sprintf(
                        "mysql_field_%s(): Field %d is invalid for MySQL result index %s",
                        $what,
                        $field,
                        spl_object_hash($result)
                    ),
                    E_USER_WARNING
                );
                // @codeCoverageIgnoreStart
                // PHPUnit turns the warning into an exception, so this never runs
                return false;
                // @codeCoverageIgnoreEnd
            }

            if ($what == 'name' || $what == 'table') {
                return $field->{$what};
            }

            if ($what == 'len') {
                return $field->length;
            }

            if ($what == 'type') {
                return static::getFieldType($field->type);
            }

            if ($what == 'flags') {
                return static::getFieldFlags($field->flags);
            }

            return false;
        }

        public static function checkValidResult($result, $function)
        {

			if (!($result instanceof \mysqli_result)) {
                if ($function != "mysql_fetch_object") {
                    trigger_error(
                        $function . "() expects parameter 1 to be resource, " . strtolower(gettype($result)) . " given",
                        E_USER_WARNING
                    );
                }

                if ($function == "mysql_fetch_object") {
                    trigger_error(
                        $function . "(): supplied argument is not a valid MySQL result resource",
                        E_USER_WARNING
                    );
                }
                return false;
            }

        }

        public static function escapeString($unescapedString)
        {
            $escapedString = "";
            for ($i = 0; $i < strlen($unescapedString); $i++) {
                $escapedString .= self::escapeChar($unescapedString{$i});
            }

            return $escapedString;
        }

        protected static function getFieldFlags($what)
        {
            // Order of flags taken from http://lxr.php.net/xref/PHP_5_6/ext/mysql/php_mysql.c#2507
            $flags = [
                MYSQLI_NOT_NULL_FLAG => "not_null",
                MYSQLI_PRI_KEY_FLAG => "primary_key",
                MYSQLI_UNIQUE_KEY_FLAG => "unique_key",
                MYSQLI_MULTIPLE_KEY_FLAG => "multiple_key",
                MYSQLI_BLOB_FLAG => "blob",
                MYSQLI_UNSIGNED_FLAG => "unsigned",
                MYSQLI_ZEROFILL_FLAG => "zerofill",
                MYSQLI_BINARY_FLAG => "binary",
                MYSQLI_ENUM_FLAG => "enum",
                MYSQLI_SET_FLAG => "set",
                MYSQLI_AUTO_INCREMENT_FLAG => "auto_increment",
                MYSQLI_TIMESTAMP_FLAG => "timestamp",
            ];

            $fieldFlags = [];
            foreach ($flags as $flag => $value) {
                if ($what & $flag) {
                    $fieldFlags[] = $value;
                }
            }

            return implode(" ", $fieldFlags);
        }

        protected static function getFieldType($what)
        {
            $types = [
                MYSQLI_TYPE_STRING => 'string',
                MYSQLI_TYPE_VAR_STRING => 'string',
                MYSQLI_TYPE_ENUM => 'string',
                MYSQLI_TYPE_SET => 'string',

                MYSQLI_TYPE_LONG => 'int',
                MYSQLI_TYPE_TINY => 'int',
                MYSQLI_TYPE_SHORT => 'int',
                MYSQLI_TYPE_INT24 => 'int',
                MYSQLI_TYPE_CHAR => 'int',
                MYSQLI_TYPE_LONGLONG => 'int',

                MYSQLI_TYPE_DECIMAL => 'real',
                MYSQLI_TYPE_FLOAT => 'real',
                MYSQLI_TYPE_DOUBLE => 'real',
                MYSQLI_TYPE_NEWDECIMAL => 'real',

                MYSQLI_TYPE_TINY_BLOB => 'blob',
                MYSQLI_TYPE_MEDIUM_BLOB => 'blob',
                MYSQLI_TYPE_LONG_BLOB => 'blob',
                MYSQLI_TYPE_BLOB => 'blob',

                MYSQLI_TYPE_NEWDATE => 'date',
                MYSQLI_TYPE_DATE => 'date',
                MYSQLI_TYPE_TIME => 'time',
                MYSQLI_TYPE_YEAR => 'year',
                MYSQLI_TYPE_DATETIME => 'datetime',
                MYSQLI_TYPE_TIMESTAMP => 'timestamp',

                MYSQLI_TYPE_NULL => 'null',

                MYSQLI_TYPE_GEOMETRY => 'geometry',
            ];

            return isset($types[$what]) ? $types[$what] : "unknown";
        }

        protected static function escapeChar($char)
        {
            switch ($char) {
                case "\0":
                    $esc = "\\0";
                    break;
                case "\n":
                    $esc = "\\n";
                    break;
                case "\r":
                    $esc = "\\r";
                    break;
                case '\\':
                case '\'':
                case '"':
                    $esc = "\\{$char}";
                    break;
                case "\032":
                    $esc = "\\Z";
                    break;
                default:
                    $esc = $char;
                    break;
            }

            return $esc;
        }
    }
}
