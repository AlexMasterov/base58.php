<?php
declare(strict_types=1);

namespace Base58;

use InvalidArgumentException;
use function intdiv;
use function ord;
use function strlen;

// ANCII
const CHR = "\X0\X1\X2\X3\X4\X5\X6\X7\X8\X9\Xa\Xb\Xc\Xd\Xe\Xf\X10\X11\X12\X13\X14\X15\X16\X17\X18\X19\X1a\X1b\X1c\X1d\X1e\X1f\X20\X21\X22\X23\X24\X25\X26\X27\X28\X29\X2a\X2b\X2c\X2d\X2e\X2f\X30\X31\X32\X33\X34\X35\X36\X37\X38\X39\X3a\X3b\X3c\X3d\X3e\X3f\X40\X41\X42\X43\X44\X45\X46\X47\X48\X49\X4a\X4b\X4c\X4d\X4e\X4f\X50\X51\X52\X53\X54\X55\X56\X57\X58\X59\X5a\X5b\X5c\X5d\X5e\X5f\X60\X61\X62\X63\X64\X65\X66\X67\X68\X69\X6a\X6b\X6c\X6d\X6e\X6f\X70\X71\X72\X73\X74\X75\X76\X77\X78\X79\X7a\X7b\X7c\X7d\X7e\X7f\X80\X81\X82\X83\X84\X85\X86\X87\X88\X89\X8a\X8b\X8c\X8d\X8e\X8f\X90\X91\X92\X93\X94\X95\X96\X97\X98\X99\X9a\X9b\X9c\X9d\X9e\X9f\Xa0\Xa1\Xa2\Xa3\Xa4\Xa5\Xa6\Xa7\Xa8\Xa9\Xaa\Xab\Xac\Xad\Xae\Xaf\Xb0\Xb1\Xb2\Xb3\Xb4\Xb5\Xb6\Xb7\Xb8\Xb9\Xba\Xbb\Xbc\Xbd\Xbe\Xbf\Xc0\Xc1\Xc2\Xc3\Xc4\Xc5\Xc6\Xc7\Xc8\Xc9\Xca\Xcb\Xcc\Xcd\Xce\Xcf\Xd0\Xd1\Xd2\Xd3\Xd4\Xd5\Xd6\Xd7\Xd8\Xd9\Xda\Xdb\Xdc\Xdd\Xde\Xdf\Xe0\Xe1\Xe2\Xe3\Xe4\Xe5\Xe6\Xe7\Xe8\Xe9\Xea\Xeb\Xec\Xed\Xee\Xef\Xf0\Xf1\Xf2\Xf3\Xf4\Xf5\Xf6\Xf7\Xf8\Xf9\Xfa\Xfb\Xfc\Xfd\Xfe\Xff\X00";

const BASE58_LENGTH = 58;
const BASE256_LENGTH = 256;
const BASE256_LENGTH_BIT = 8;
const ALPHABET_BITCOIN = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
const ALPHABET_BITCOIN_CODES = [
    1 => 0, 2 => 1, 3 => 2, 4 => 3, 5 => 4, 6 => 5, 7 => 6, 8 => 7, 9 => 8, 'A' => 9,
    'B' => 10, 'C' => 11, 'D' => 12, 'E' => 13, 'F' => 14, 'G' => 15, 'H' => 16, 'J' => 17,
    'K' => 18, 'L' => 19, 'M' => 20, 'N' => 21, 'P' => 22, 'Q' => 23, 'R' => 24, 'S' => 25,
    'T' => 26, 'U' => 27, 'V' => 28, 'W' => 29, 'X' => 30, 'Y' => 31, 'Z' => 32, 'a' => 33,
    'b' => 34, 'c' => 35, 'd' => 36, 'e' => 37, 'f' => 38, 'g' => 39, 'h' => 40, 'i' => 41,
    'j' => 42, 'k' => 43, 'm' => 44, 'n' => 45, 'o' => 46, 'p' => 47, 'q' => 48, 'r' => 49,
    's' => 50, 't' => 51, 'u' => 52, 'v' => 53, 'w' => 54, 'x' => 55, 'y' => 56, 'z' => 57
];

function base58_encode(string $string): string
{
    $encoded = '';
    $bytes = [];
    $len = strlen($string);

    for ($i = 0; $i < $len; ++$i) {
        $bytes[$i] = ord($string[$i]);
    }

    for ($i = 0; $i < $len;) {
        $code = 0;
        for ($y = $i; $y < $len; ++$y) {
            $carry = $bytes[$y] | $code << BASE256_LENGTH_BIT;
            $bytes[$y] = intdiv($carry, BASE58_LENGTH);
            $code = $carry % BASE58_LENGTH;
        }

        $char = ALPHABET_BITCOIN[$code];
        $encoded = "${char}${encoded}";

        if ($bytes[$i] === 0) {
            ++$i;
        }
    }

    return $encoded;
}

function base58_decode(string $encoded): string
{
    $binary = '';
    $bytes = [];
    $len = strlen($encoded);

    for ($i = 0; $i < $len; ++$i) {
        if (!isset(ALPHABET_BITCOIN_CODES[$encoded[$i]])) {
            throw new InvalidArgumentException(
                "Unable to decode string, character `${encoded[$i]}` is missing from the alphabet"
            );
        }

        $bytes[$i] = ALPHABET_BITCOIN_CODES[$encoded[$i]];
    }

    for ($i = 0; $i < $len;) {
        $code = 0;
        for ($y = $i; $y < $len; ++$y) {
            $code = $bytes[$y] + ($code * BASE58_LENGTH);
            $bytes[$y] = intdiv($code, BASE256_LENGTH);
            $code %= BASE256_LENGTH;
        }

        if ($code > 0) {
            $char = CHR[$code];
            $binary = "${char}${binary}";
        }

        if ($bytes[$i] === 0) {
            ++$i;
        }
    }

    return $binary;
}
