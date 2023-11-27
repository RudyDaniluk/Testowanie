<?php

require("libs/DataBaseConn.php");

/**
 * Klasa służąca do autoryzacji jednorazowego dostępu do fragmentu serwisu
 * @author Grzegorz Petri
 * @since 0.2
 */

class AuthBasic
{

    /**
     * Generuje kod uwierzytelniający
     * @param string $algo Algorytm kodowania
     * @return string Wygenerowany kod uwierzytelniający
     */
    public function genFingerprint($algo)
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $remoteAddress = $_SERVER['REMOTE_ADDR'];
        $uniqueHash = uniqid();
        $isSecure = true;

        $dataToHash = $userAgent . $remoteAddress . $uniqueHash . $isSecure;
        return hash_hmac($algo, $dataToHash, 'TwójTajnyKlucz');
    }

    /**
     * Generuje kod, który jest wymagany podczas autoryzacji dostępu, na podstawie określonych parametrów
     * @param int $length Długość kodu (liczba znaków)
     * @param int $min Minimalna wartość do wygenerowania
     * @param int $max Maksymalna wartość do wygenerowania
     * @return int Wygenerowana liczba, która może być uzupełniana zerami, jeśli ma osiągnąć daną długość
     */
    public function createCode($length = 6, $min = 1, $max = 999999)
    {
        $max = substr($max, 0, $length);
        return str_pad(mt_rand($min, $max), $length, '0', STR_PAD_LEFT);
    }

    public function compAuthCode($emlAuth, $idzAuth, $authCode)
    {
    }
    public function doAuthByEmail($person, $email)
    {
    }
    public function checkIfValidRequest($person, $email)
    {
    }
    private function checkIfValidRequest2f($emlAuth, $idzAuth)
    {
    }

    /**
     * Weryfikuje kod autoryzacyjny dla autoryzacji jednorazowego dostępu.
     * @param string $codeNo Kod autoryzacyjny do weryfikacji.
     * @return bool True, jeśli kod autoryzacyjny jest poprawny i ważny; False, jeśli jest niepoprawny lub wygasł.
     */
    public function verifyQuickRegCode($codeNo)
    {
        $tbl = 'cmswebsiteauth';
        $cols = array('email', 'authCode', 'datetime');
        $options = array('where' => "authCode = '$codeNo'");

        $db = new DataBaseConn('localhost', 'root', '', 'authtida');
        $db->connect();

        $data = $db->get($tbl, $cols, $options);

        $db->disconnect();

        if (count($data) === 1) {
            $authData = $data[0];
            $authDate = strtotime($authData['datetime']);
            $currentTime = strtotime(date("Y-m-d H:i:s"));

            if ($authDate >= $currentTime) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tworzy wpis w bazie danych z numerem pozwalającym na uwierzytelnienie żądania.
     * Tworzony jest token uwierzytelniający zapisujący adres e-mail oraz ID użytkownika.
     * Token ten musi zostać wysłany na adres e-mail użytkownika. Funkcja zwraca odpowiedni obiekt informacyjny.
     * @param string $email Adres e-mail użytkownika do uwierzytelnienia
     * @param int $userId Numer ID użytkownika do uwierzytelnienia
     * @return array|false Wygenerowany token LUB fałsz
     */
    public function createAuthToken($email, $userId)
    {
        $authCode = $this->createCode();
        $authDate = date("Y-m-d H:i:s");

        $addrIp = '127.0.0.1'; 
        $opSys = 'Linux';
        $browser = 'FF';
        $fingerprint = $this->genFingerprint("sha512");
        $session_id = "1234567891";

        $content = array(
            'addrIp' => $addrIp, 'datetime' => $authDate,
            'email' => $email, 'authCode' => $authCode,
            'opSystem' => $opSys, 'browser' => $browser
        );

        $tbl = 'cmswebsiteauth';
        $cols = array(
            'session_id', 'usrId', 'addrIp', 'fingerprint', 'datetime', 'email', 'authCode', 'opSystem', 'browser'
        );

        $vals = array(
            $session_id, $userId, $addrIp, $fingerprint, $authDate, $email, $authCode, $opSys, $browser
        );

        $db = new DataBaseConn('localhost', 'root', '', 'authtida');
        $db->connect();

        $db->put($tbl, $cols, $vals);

        $data = $db->get($tbl, array('addrIp', 'datetime', 'email', 'authCode', 'opSystem', 'browser'), array('where' => "session_id = $session_id"));

        $db->disconnect();

        if (count($data) === 1 && $data[0] == $content) {
            return $content;
        } else {
            return false;
        }
    }
}
