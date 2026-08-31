<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Exceptions\PostTooLargeException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {});
    }

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            if (request()->path() === 'claims/bulk-import') {
                return response()->json([
                    'message' => 'Validation failed.',
                    'status' => false,
                    'errors' => $this->makeExcelErrors($exception->validator->errors()->toArray())
                ], 422);
            } else {
                return response()->json([
                    'message' => 'Validation failed.',
                    'status' => false,
                    'errors' => $exception->validator->errors()->toArray()
                ], 422);
            }
        }

        if ($exception instanceof PostTooLargeException) {
            return response()->json([
                'message' => 'The uploaded file is too large.',
                'status' => false,
                'errors' => ['file' => ['The uploaded file is too large.']]
            ], 413);
        }

        return parent::render($request, $exception);
    }

    private function makeExcelErrors($errors)
    {
        $prettyErrors = [];

        foreach ($errors as $key => $messages) {
            [$rowIndex, $colIndex] = explode('.', $key);
            $excelCol = $this->numberToExcelColumn($colIndex);
            $excelRow = $rowIndex;
            $cellRef = $excelCol . $excelRow;

            $prettyErrors[$cellRef] = $messages;
        }

        return $prettyErrors;
    }

    private function numberToExcelColumn($number)
    {
        $column = '';
        while ($number >= 0) {
            $column = chr($number % 26 + 65) . $column;
            $number = floor($number / 26) - 1;
        }
        return $column;
    }
}
