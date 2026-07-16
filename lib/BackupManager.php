<?php
// Pure-PHP dump/restore — shared hosting gives us no shell_exec/mysqldump to rely on.

class BackupManager
{
    const TABLES = [
        'dataColorPalette', 'greenColor', 'dataTaskCategories',
        'datastatus', 'dataLists', 'dataMemo', 'dataNxlinks', 'datatasks',
    ];
    const MAX_BACKUPS = 10;
    const HEADER_MARKER = '-- NXTM backup';

    public static function dir(): string
    {
        return __DIR__ . '/../data/backups';
    }

    public static function dump(mysqli $conn): string
    {
        $out = self::HEADER_MARKER . ' ' . date('c') . "\n";
        $out .= "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach (self::TABLES as $table) {
            $out .= "TRUNCATE TABLE `$table`;\n";
            $result = $conn->query("SELECT * FROM `$table`");
            while ($row = $result->fetch_assoc()) {
                $cols = '`' . implode('`, `', array_keys($row)) . '`';
                $vals = implode(', ', array_map(function ($v) use ($conn) {
                    return $v === null ? 'NULL' : "'" . $conn->real_escape_string($v) . "'";
                }, array_values($row)));
                $out .= "INSERT INTO `$table` ($cols) VALUES ($vals);\n";
            }
            $out .= "\n";
        }

        $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $out;
    }

    public static function create(mysqli $conn, string $label = ''): string
    {
        if (!is_dir(self::dir())) {
            mkdir(self::dir(), 0755, true);
        }
        $label = self::sanitizeLabel($label);
        $filename = date('Y-m-d_H-i-s') . '_' . ($label !== '' ? $label : 'backup') . '.sql';
        file_put_contents(self::dir() . '/' . $filename, self::dump($conn));
        self::enforceRetention();
        return $filename;
    }

    public static function restore(mysqli $conn, string $sql): void
    {
        if (strpos(ltrim($sql), self::HEADER_MARKER) !== 0) {
            throw new RuntimeException('File does not look like an NXTM backup (missing header).');
        }
        if (!$conn->multi_query($sql)) {
            throw new RuntimeException($conn->error);
        }
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
            if ($conn->errno) {
                throw new RuntimeException($conn->error);
            }
        } while ($conn->more_results() && $conn->next_result());
    }

    public static function list(): array
    {
        $files = is_dir(self::dir()) ? glob(self::dir() . '/*.sql') : [];
        $items = array_map(function ($f) {
            return ['name' => basename($f), 'size' => filesize($f), 'mtime' => filemtime($f)];
        }, $files);
        usort($items, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
        return $items;
    }

    public static function find(string $filename): ?array
    {
        $safe = basename($filename);
        if ($safe === '' || $safe !== $filename) {
            return null;
        }
        $path = self::dir() . '/' . $safe;
        if (!is_file($path) || pathinfo($path, PATHINFO_EXTENSION) !== 'sql') {
            return null;
        }
        return ['name' => $safe, 'path' => $path, 'size' => filesize($path), 'mtime' => filemtime($path)];
    }

    private static function enforceRetention(): void
    {
        $files = glob(self::dir() . '/*.sql');
        usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));
        foreach (array_slice($files, self::MAX_BACKUPS) as $old) {
            unlink($old);
        }
    }

    private static function sanitizeLabel(string $label): string
    {
        $label = preg_replace('/[^A-Za-z0-9_-]+/', '-', trim($label));
        return substr(trim($label, '-'), 0, 60);
    }
}
