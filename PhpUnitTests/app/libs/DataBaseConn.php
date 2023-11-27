<?php
/**
 * Klasa odpowiedzialna za obsługę połączeń z bazą danych
 * @author Jakub Daniluk
 * @since 0.2
 */
class DataBaseConn
{
    private $host;
    private $user;
    private $pass;
    private $database;
    private $conn;

    /**
     * Konstruktor klasy
     * @param string $host Adres hosta bazy danych
     * @param string $user Nazwa użytkownika
     * @param string $pass Hasło użytkownika
     * @param string $database Nazwa bazy danych
     */
    public function __construct($host, $user, $pass, $database)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->database = $database;
    }

    /**
     * Inicjuje połączenie z bazą danych
     */
    public function connect()
    {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->database);

        if ($this->conn->connect_error) {
            die("Błąd połączenia: " . $this->conn->connect_error);
        }
    }

    /**
     * Zamyka połączenie z bazą danych
     */
    public function disconnect()
    {
        $this->conn->close();
    }

    /**
     * Wstawia dane do bazy danych
     * @param string $table Nazwa docelowej tabeli
     * @param array $columns Nazwy kolumn w tabeli
     * @param array $values Wartości do wstawienia
     * @return string ID wstawionego rekordu
     */
    public function put($table, $columns = null, $values = null)
    {
        if ($columns === null || $values === null) {
            die("Należy podać kolumny i wartości.");
        }

        $columnString = implode(", ", $columns);
        $valueString = "'" . implode("', '", $values) . "'";

        $sql = "INSERT INTO $table ($columnString) VALUES ($valueString)";
        $result = $this->conn->query($sql);

        if ($result === false) {
            die("Błąd zapytania: " . $this->conn->error);
        }

        return $this->conn->insert_id;
    }

    /**
     * Pobiera dane z bazy
     * @param string $table Nazwa docelowej tabeli
     * @param array|null $columns Wybrane kolumny
     * @param array|null $options Opcje filtrowania
     * @return array Dane pobrane z bazy
     */
    public function get($table, $columns = null, $options = array())
    {
        $columnString = $columns ? implode(", ", $columns) : '*';
        $whereClause = isset($options['where']) ? 'WHERE ' . $options['where'] : '';

        $sql = "SELECT $columnString FROM $table $whereClause";
        $result = $this->conn->query($sql);

        if ($result === false) {
            die("Błąd zapytania: " . $this->conn->error);
        }

        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }
}
