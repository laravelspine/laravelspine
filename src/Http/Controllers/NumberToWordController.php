<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Services\NumberToWord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API for converting numbers to words (Indonesian/Indian).
 *
 * Used by the frontend when generating invoice PDFs, financial documents, etc.
 *
 * @group api/v1
     * @subgroup Utilities
 */
class NumberToWordController extends Controller
{
    public function __construct(
        private NumberToWord $numberToWord
    ) {}

    /**
     * Convert a number to Indonesian words (Rupiah format).
     *
     * @authenticated
     *
     * @bodyParam number numeric required The number to convert. Example: 1234567
     * @bodyParam currency string optional Currency suffix. Default: rupiah. Example: rupiah
     *
     * @response scenario=success {
     *   "number": 1234567,
     *   "terbilang": "satu juta dua ratus tiga puluh empat ribu lima ratus enam puluh tujuh rupiah"
     * }
     */
    public function convert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number'   => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:20',
        ]);

        $terbilang = $this->numberToWord->convert($validated['number'], $validated['currency'] ?? 'rupiah');

        return response()->json([
            'number'   => (float) $validated['number'],
            'terbilang' => $terbilang,
        ]);
    }

    /**
     * Convert a number to Indian words (lakh/crore format).
     *
     * @authenticated
     *
     * @bodyParam number numeric required The number to convert. Example: 1234567
     * @bodyParam currency string optional Currency suffix. Default: (empty). Example: INR
     *
     * @response scenario=success {
     *   "number": 1234567,
     *   "terbilang": "twelve lakh thirty four thousand five hundred sixty seven"
     * }
     */
    public function convertIndian(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number'   => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
        ]);

        $terbilang = $this->numberToWord->convertIndian($validated['number'], $validated['currency'] ?? '');

        return response()->json([
            'number'   => (float) $validated['number'],
            'terbilang' => $terbilang,
        ]);
    }
}