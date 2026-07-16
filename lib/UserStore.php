<?php

class UserStore {

    private string $filePath;

    public function __construct() {
        $this->filePath = __DIR__ . '/../data/users.json';
    }

    public function hasUsers(): bool {
        return count($this->load()) > 0;
    }

    public function addUser(string $username, string $password, ?string $totpSecret = null): bool {
        $users = $this->load();
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                return false;
            }
        }
        $users[] = [
            'username'      => $username,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'totp_secret'   => $totpSecret,
            'created_at'    => date('Y-m-d H:i:s'),
        ];
        $this->save($users);
        return true;
    }

    public function verifyPassword(string $username, string $password): bool {
        foreach ($this->load() as $user) {
            if ($user['username'] === $username) {
                return password_verify($password, $user['password_hash']);
            }
        }
        return false;
    }

    public function getTotpSecret(string $username): ?string {
        foreach ($this->load() as $user) {
            if ($user['username'] === $username) {
                return $user['totp_secret'] ?? null;
            }
        }
        return null;
    }

    private function load(): array {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $data = json_decode(file_get_contents($this->filePath), true);
        return is_array($data) ? $data : [];
    }

    private function save(array $users): void {
        file_put_contents($this->filePath, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
    }
}
