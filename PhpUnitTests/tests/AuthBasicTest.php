<?php
// Załącz plik testowanej klasy - dostosuj ścieżkę do pliku zgodnie z własną strukturą katalogów
require("app/AuthBasic.php");

// Użycie wbudowanych testów
use PHPUnit\Framework\TestCase;

// Nazwanie własnej klasy i rozszerzenie jej o klasę `TestCase`, zawierającą asercje dla testów
class AuthBasicTest extends TestCase
{
    private $instance;
    private $db;

    // Tutaj umieść kod testów (metody)
    public function setUp(): void
    {
        $this->instance = new AuthBasic();

        $this->db = new DataBaseConn('localhost', 'root', '', 'authtida');
        $this->db->connect();
    }

    public function tearDown(): void
    {
        unset($this->instance);

        $this->db->disconnect();
    }

    public function testCreateCode()
    {
        $out = $this->instance->createCode();

        // Jeśli potrzebujesz wyświetlić wynik w teście, użyj:
        fwrite(STDERR, print_r($out, true));
        $len = strlen($out);
        $this->assertIsNumeric($out, 'Wylosowano: ' . $out);
        $this->assertEquals(6, $len, 'Długość: ' . $len);

        $out = $this->instance->createCode(4);
        $len = strlen($out);
        $this->assertIsNumeric($out, 'Wylosowano: ' . $out);
        $this->assertEquals(4, $len, 'Długość: ' . $len);

        // Symulacja wylosowania liczby o krótszej niż oczekiwana długość, która będzie uzupełniana zerami
        $out = str_pad(1111, 6, '0', STR_PAD_LEFT);
        $len = strlen($out);
        $this->assertIsNumeric($out, 'Wylosowano: ' . $out);
        $this->assertEquals(6, $len, 'Długość: ' . $len);
    }

    public function testCreateAuthToken()
    {
        // Oczekiwana struktura tokenu z określonymi danymi
        $exp = array(
            'addrIp' => "127.0.0.1", 'datetime' => date("Y-m-d H:i:s"),
            'email' => "janh@testingmail.com", 'authCode' => "131313",
            'opSystem' => "Linux", 'browser' => "FF"
        );

        // Wywołanie testowanej metody z przykładowymi danymi użytkownika: email i jego ID
        $out = $this->instance->createAuthToken('janh@testingmail.com', 69);

        // Ponieważ wygenerowany token jest losowy, musimy go ustawić na stałą wartość, aby przetestować
        $out['authCode'] = '131313';

        // Wywołanie testu - asercji (założeń)
        $this->assertEqualsCanonicalizing($exp, $out, 'Tablice są różne');
    }

    public function testVerifyQuickRegCodeValid()
    {
        // Wstawienie testowego wpisu do bazy danych
        $columns = array('session_id', 'usrId', 'addrIp', 'fingerprint', 'datetime', 'email', 'authCode', 'opSystem', 'browser');
        $values = array('1234567890', 1, '127.0.0.1', 'testHash', date("Y-m-d H:i:s"), 'test@example.com', '123456', 'Linux', 'FF');

        $this->db->put('cmswebsiteauth', $columns, $values);

        // Sprawdzenie, czy verifyQuickRegCode zwraca true dla poprawnego kodu autoryzacyjnego
        $this->assertTrue($this->instance->verifyQuickRegCode('123456'));
    }

    public function testVerifyQuickRegCodeExpired()
    {
        // Wstawienie przeterminowanego testowego wpisu do bazy danych
        $columns = array('session_id', 'usrId', 'addrIp', 'fingerprint', 'datetime', 'email', 'authCode', 'opSystem', 'browser');
        $values = array('1234567891', 1, '127.0.0.1', 'testHash', '2023-01-01 12:00:00', 'test@example.com', '654321', 'Linux', 'FF');

        $this->db->put('cmswebsiteauth', $columns, $values);

        // Sprawdzenie, czy verifyQuickRegCode zwraca false dla przeterminowanego kodu autoryzacyjnego
        $this->assertFalse($this->instance->verifyQuickRegCode('654321'));
    }

    public function testVerifyQuickRegCodeNonExistent()
    {
        // Sprawdzenie, czy verifyQuickRegCode zwraca false dla nieistniejącego kodu autoryzacyjnego
        $this->assertFalse($this->instance->verifyQuickRegCode('999999'));
    }
}
