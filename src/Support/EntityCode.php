<?php

declare(strict_types=1);

namespace Spine\Support;

/**
 * EntityCode — generator kode entity (permutasi multiplicative + base36).
 *
 * Kontrak (keputusan user, 5 Sep 2026):
 *   - start_number (settings per modul) = ID AWAL TABEL (mis. customers.id 20721).
 *   - kolom `code` = encode(id) — bijektif 1-to-1, tanpa collision, kelihatan acak.
 *   - encode: (id * a) mod 36^len, a konstanta coprime dgn 36^len (97).
 *
 * Referensi diskusi: references/entity-code-generation.md (skill spine-module-workflow).
 */
class EntityCode
{
    /** Multiplier coprime dgn 36^4 (2^8 * 3^8) — 97 adalah bilangan prima. */
    private const MULTIPLIER = 97;

    private const DIGITS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Encode id ke kode base36 teracak dengan panjang tetap.
     *
     * @param int $id  id record (biasanya >= start_number modul).
     * @param int $len panjang kode hasil (default 4 = 1.679.616 kombinasi).
     */
    public static function encode(int $id, int $len = 4): string
    {
        if ($len < 1 || $len > 10) {
            throw new \InvalidArgumentException("len harus 1..10, diberikan {$len}");
        }
        $mod = 36 ** $len;
        $n = ($id * self::MULTIPLIER) % $mod;

        $out = '';
        while ($n > 0) {
            $out = self::DIGITS[$n % 36] . $out;
            $n = intdiv($n, 36);
        }

        return str_pad($out, $len, '0', STR_PAD_LEFT);
    }
}
