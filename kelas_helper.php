<?php
function canonicalClassName(string $className): ?string
{
    if (!preg_match('/^(XII|XI|X)\\s*-?\\s*([1-8])$/i', trim($className), $matches)) {
        return null;
    }

    return strtoupper($matches[1]) . '-' . $matches[2];
}

function ensureStandardClasses(PDO $pdo): array
{
    $classMap = [];
    $classQuery = $pdo->query('SELECT id, nama_kelas FROM kelas ORDER BY id ASC');
    foreach ($classQuery->fetchAll() as $class) {
        $canonicalName = canonicalClassName($class['nama_kelas']);
        if ($canonicalName !== null && !isset($classMap[$canonicalName])) {
            $classMap[$canonicalName] = (int) $class['id'];
        }
    }

    $insertClass = $pdo->prepare('INSERT INTO kelas (nama_kelas) VALUES (:nama_kelas)');
    foreach (['X', 'XI', 'XII'] as $grade) {
        for ($number = 1; $number <= 8; $number++) {
            $canonicalName = $grade . '-' . $number;
            if (!isset($classMap[$canonicalName])) {
                $insertClass->execute(['nama_kelas' => $canonicalName]);
                $classMap[$canonicalName] = (int) $pdo->lastInsertId();
            }
        }
    }

    return $classMap;
}