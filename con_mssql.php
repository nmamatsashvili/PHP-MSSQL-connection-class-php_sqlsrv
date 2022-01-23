<?php

class Mssql
{

    static function OpenConnection()
    {
        $connectionOptions = array("Database" => "your_DB", "ReturnDatesAsStrings" => true/*date as string not an array*/, "Uid" => "mssql user", "PWD" => "mssql pass");
        $MssqlServer = "your server address";
        $conn = sqlsrv_connect($MssqlServer, $connectionOptions);
        if($conn === FALSE)
            throw new Exception(print_r(sqlsrv_errors(), true));

        return $conn;
    }

	/**
	 * @param string $query A query where each parameter is equal to question mark (?)
	 * @param array $params one demension array of query parameters. Each element is matched to query parameters by sequence
	 * @return array array  of query result
	 * @example
	   $query = "SELECT * FROM DB.dbo.table1 WHERE id = ? AND description = ? AND date = ?";
	   $params = array(1, "some descr", "2022-01-01");
     $result = mssql::Select($query, $params);
	 * */
    static function Select($query, $params = array()): bool|array|null
    {
        try {
            $conn = self::OpenConnection();
            $arr = null;

            $result = sqlsrv_query($conn, $query, $params);

            if ($result === FALSE)
                throw new Exception(print_r(sqlsrv_errors(), true));

            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $arr[] = $row;
            }

            sqlsrv_free_stmt($result);
            sqlsrv_close($conn);

            return $arr;
        }
        catch(Exception $e)
        {
            error_log("Select DB Exception in ".__FILE__);
            error_log(print_r($e, true));
            return false;
        }
    }

    static function Run($query, $params = array()): bool|int
    {
        try {
            $conn = self::OpenConnection();
            $result = sqlsrv_query($conn, $query, $params);

            if ($result === FALSE)
                throw new Exception(print_r(sqlsrv_errors(), true));

            $rows_affected = sqlsrv_rows_affected($result);

            if( $rows_affected === FALSE)
                throw new Exception(print_r(sqlsrv_errors(), true));
            elseif( $rows_affected == -1)
                throw new Exception("Error no rows affected");

            sqlsrv_free_stmt($result);
            sqlsrv_close($conn);

            return $rows_affected;
        }
        catch(Exception $e)
        {
            error_log("Run DB Exception in ".__FILE__);
            error_log(print_r($e, true));
            return false;
        }
    }

}