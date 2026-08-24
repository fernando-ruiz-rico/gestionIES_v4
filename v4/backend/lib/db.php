<?php
/**
 * Db — capa fina sobre mysqli para los endpoints del backend v4.
 *
 * Cubre los tres usos reales de la BD en el código:
 *
 *   $db = Db::open();
 *   $fila  = $db->fetchOne("SELECT ... WHERE id = ?", $id);   // una fila o null
 *   $filas = $db->fetchAll("SELECT ... ORDER BY nombre");    // todas las filas
 *   $n     = $db->execute("DELETE FROM ... WHERE id = ?", $id); // filas afectadas
 *
 * prepare → bind_param → execute → get_result → fetch → free → close
 * queda dentro de la clase; el endpoint solo escribe la consulta y lee
 * el resultado. Los tipos de bind_param se infieren de los parámetros
 * (int → 'i', float → 'd', null/otro → 's'), igual que hacían a mano
 * los bind_param del código anterior.
 *
 * Cualquier fallo SQL lanza DbException; el endpoint la captura y
 * responde con 500 JSON, como hacía antes.
 */

class DbException extends Exception
{
}

class Db
{

    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Abre la conexión (getDBConnection() de config.php) y devuelve un Db.
     * Si la conexión no puede abrirse, responde 500 JSON y termina,
     * igual que los endpoints hacían antes con `if (!$db) { ... }`.
     */
    public static function open()
    {
        $conn = getDBConnection();
        if (!$conn) {
            http_response_code(500);
            echo json_encode(['error' => 'Error de conexión a la base de datos']);
            exit;
        }
        return new self($conn);
    }

    /**
     * SELECT de una sola fila. Devuelve la fila como array, o null si no
     * hay resultados. Lanza DbException si la consulta falla.
     */
    public function fetchOne($sql, ...$params)
    {
        $stmt = $this->prepare($sql, $params);
        $this->executeStmt($stmt);
        $res   = $this->getResult($stmt);
        $fila  = mysqli_fetch_assoc($res);
        mysqli_free_result($res);
        mysqli_stmt_close($stmt);
        return $fila === null ? null : $fila;
    }

    /**
     * SELECT de varias filas. Devuelve todas las filas como array (vacio
     * si no hay resultados). Lanza DbException si la consulta falla.
     */
    public function fetchAll($sql, ...$params)
    {
        $stmt  = $this->prepare($sql, $params);
        $this->executeStmt($stmt);
        $res   = $this->getResult($stmt);
        $filas = [];
        while ($fila = mysqli_fetch_assoc($res)) {
            $filas[] = $fila;
        }
        mysqli_free_result($res);
        mysqli_stmt_close($stmt);
        return $filas;
    }

    /**
     * INSERT / UPDATE / DELETE. Devuelve el número de filas afectadas
     * (equivalente a mysqli_stmt_affected_rows). Lanza DbException si
     * la consulta falla.
     */
    public function execute($sql, ...$params)
    {
        $stmt      = $this->prepare($sql, $params);
        $this->executeStmt($stmt);
        $afectadas = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $afectadas;
    }

    /**
     * Devuelve el número de filas que devolvería el SELECT (equivalente a
     * mysqli_num_rows sobre el resultado). Lanza DbException si la
     * consulta falla.
     */
    public function count($sql, ...$params)
    {
        $stmt = $this->prepare($sql, $params);
        $this->executeStmt($stmt);
        $res  = $this->getResult($stmt);
        $n    = 0;
        while (mysqli_fetch_assoc($res) !== null) {
            $n++;
        }
        mysqli_free_result($res);
        mysqli_stmt_close($stmt);
        return $n;
    }

    /**
     * Último id auto-incremental generado en la conexión
     * (equivalente a mysqli_insert_id).
     */
    public function insertId()
    {
        return (int)mysqli_insert_id($this->conn);
    }

    /** Abre una transacción (equivalente a mysqli_begin_transaction). */
    public function begin()
    {
        try {
            mysqli_begin_transaction($this->conn);
        } catch (mysqli_sql_exception $e) {
            throw new DbException($e->getMessage());
        }
    }

    /** Confirma la transacción (equivalente a mysqli_commit). */
    public function commit()
    {
        try {
            mysqli_commit($this->conn);
        } catch (mysqli_sql_exception $e) {
            throw new DbException($e->getMessage());
        }
    }

    /** Deshace la transacción (equivalente a mysqli_rollback). */
    public function rollback()
    {
        try {
            mysqli_rollback($this->conn);
        } catch (mysqli_sql_exception $e) {
            throw new DbException($e->getMessage());
        }
    }

    /** Cierra la conexión (equivalente a mysqli_close). */
    public function close()
    {
        mysqli_close($this->conn);
    }

    // -----------------------------------------------------------------
    // Internos

    /** Prepara el statement; sin parámetros no hace nada más. */
    private function prepare($sql, array $params)
    {
        try {
            $stmt = mysqli_prepare($this->conn, $sql);
        } catch (mysqli_sql_exception $e) {
            throw new DbException($e->getMessage());
        }
        if (!$stmt) {
            $error = mysqli_error($this->conn);
            throw new DbException($error);
        }
        if (count($params)) {
            $this->bind($stmt, $params);
        }
        return $stmt;
    }

    /** Ejecuta el statement y lanza DbException si falla. */
    private function executeStmt($stmt)
    {
        try {
            $ok = mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            throw new DbException($e->getMessage());
        }
        if (!$ok) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new DbException($error);
        }
    }

    /**
     * mysqli_stmt_get_result, convirtiendo el mysqli_sql_exception de
     * PHP 8+ en DbException.
     */
    private function getResult($stmt)
    {
        try {
            return mysqli_stmt_get_result($stmt);
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            throw new DbException($e->getMessage());
        }
    }

    /**
     * mysqli_stmt_bind_param con los tipos inferidos de los parámetros:
     * int → 'i', float → 'd', null u otro → 's' (null se envía como NULL).
     */
    private function bind($stmt, array $params)
    {
        $tipos   = '';
        $valores = [];
        foreach ($params as $p) {
            if (is_int($p)) {
                $tipos .= 'i';
                $valores[] = $p;
            } elseif (is_float($p)) {
                $tipos .= 'd';
                $valores[] = $p;
            } else {
                $tipos .= 's';
                $valores[] = $p;
            }
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt, $tipos], $valores));
    }
}
?>
