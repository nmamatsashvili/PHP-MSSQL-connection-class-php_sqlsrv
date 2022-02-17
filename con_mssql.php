<?php

class Mssql
{

    private static string $DefaultDB = "YOUR_DEFAULT_DB";
    private static function OpenConnection()
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
     * @param string $DB A database name where query is executed
     * @return bool|array|null array of query result (row by row)
     * @example
     * $query = "SELECT * FROM DB.dbo.table1 WHERE id = ? AND description = ? AND date = ?";
     * $params = array(1, "some descr", "2022-01-01");
     */

static function Select(string $query, array $params = array(), string $DB = ""): bool|array|null
    {
	$result = FALSE;
        $conn = FALSE;
        try {
            if($DB !== "")
                self::$DefaultDB = $DB;

            $arr = null;
            $conn = self::OpenConnection();
            $result = sqlsrv_query($conn, $query, $params);

            if ($result === FALSE)
                throw new Exception(print_r(sqlsrv_errors(), true));

            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC))
                $arr[] = $row;

            return $arr;
        }
        catch(Exception $e)
        {
            error_log("Select DB Exception in ".__FILE__);
            error_log(print_r($e, true));
            return false;
        }
	finally
        {
            if($result !== FALSE)
                sqlsrv_free_stmt($result);
            if($conn !== FALSE)
                sqlsrv_close($conn);
        }
    }

	
    /**
     * @param string $query A query where each parameter is equal to question mark (?)
     * @param array $params one demension array of query parameters. Each element is matched to query parameters by sequence
     * @param string $DB A database name where query is executed
     * @return bool|int Affected rows or false in case of exception or  no rows affected
     */
    static function Run($query, $params = array(), string $DB = ""): bool|int
    {
        $result = FALSE;
        $conn = FALSE;
        try {
            if($DB !== "")
                self::$DefaultDB = $DB;

            $conn = self::OpenConnection();
            $result = sqlsrv_query($conn, $query, $params);

            if ($result === FALSE)
                throw new Exception(print_r(sqlsrv_errors(), true));

            $rowsAffected = sqlsrv_rows_affected($result);

            if( $rowsAffected === FALSE)
                throw new Exception(print_r(sqlsrv_errors(), true));
            elseif( $rowsAffected == -1 || $rowsAffected == 0)
                throw new Exception("Error $rowsAffected rows affected");

            return $rowsAffected;
        }
        catch(Exception $e)
        {
            error_log("Run DB Exception in ".__FILE__);
            error_log(print_r($e, true));
            return false;
        }
	finally
        {
            if($result !== FALSE)
                sqlsrv_free_stmt($result);
            if($conn !== FALSE)
                sqlsrv_close($conn);
        }
    }

}
