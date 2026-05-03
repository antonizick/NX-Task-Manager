<?php

class GoogleAuthenticator {

    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function createSecret(int $length = 16): string {
        $chars = str_split(self::BASE32_CHARS);
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    public function getCode(string $secret, ?int $timeSlice = null): string {
        if ($timeSlice === null) {
            $timeSlice = (int) floor(time() / 30);
        }
        $key = $this->base32Decode($secret);
        $msg = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $msg, $key, true);
        $offset = ord($hash[19]) & 0x0f;
        $code = (
            ((ord($hash[$offset])     & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) <<  8) |
             (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;
        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    public function verifyCode(string $secret, string $code, int $discrepancy = 1): bool {
        $currentSlice = (int) floor(time() / 30);
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            if (hash_equals($this->getCode($secret, $currentSlice + $i), $code)) {
                return true;
            }
        }
        return false;
    }

    public function getQRCodeUri(string $issuer, string $account, string $secret): string {
        return 'otpauth://totp/'
            . rawurlencode($issuer . ':' . $account)
            . '?secret=' . rawurlencode($secret)
            . '&issuer=' . rawurlencode($issuer);
    }

    private function base32Decode(string $secret): string {
        $secret   = strtoupper(trim($secret));
        $buffer   = 0;
        $bitsLeft = 0;
        $result   = '';
        for ($i = 0; $i < strlen($secret); $i++) {
            $val = strpos(self::BASE32_CHARS, $secret[$i]);
            if ($val === false) {
                continue;
            }
            $buffer    = ($buffer << 5) | $val;
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $result   .= chr(($buffer >> ($bitsLeft - 8)) & 0xff);
                $bitsLeft -= 8;
            }
        }
        return $result;
    }
}
