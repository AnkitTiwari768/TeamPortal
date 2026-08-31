<?php

declare(strict_types=1);

namespace App\Domain\LogViewer;

use App\Http\Controllers\ClientController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

final class LogViewerController extends ClientController
{
    public function __construct(
        private LogFileService $fileService,
        private LogReader $logReader
    ) {}

    private function authorizeAdmin(): void
    {
        // if (function_exists('acl') && session()->has('permissions')) {
        //     if (!acl('view_logs') && !acl('view-logs') && !acl('admin') && auth()->user()?->role_id != 1) {
        //         abort(403, 'Unauthorized access to Log Viewer.');
        //     }
        // }
    }

    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        $files = $this->fileService->getLogFiles();
        $selectedFile = $request->query('file') ? (string)$request->query('file') : ($files[0]['name'] ?? 'laravel.log');

        return view('admin.logs.index', [
            'title' => __('Laravel Log Viewer'),
            'files' => $files,
            'selectedFile' => $selectedFile,
        ]);
    }

    public function entries(LogViewerRequest $request): JsonResponse
    {
        $this->authorizeAdmin();

        try {
            $files = $this->fileService->getLogFiles();
            $defaultFile = $files[0]['name'] ?? 'laravel.log';
            $filename = $request->input('file') ? (string)$request->input('file') : $defaultFile;

            $data = $this->logReader->getPaginatedEntries($filename, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Logs retrieved successfully.',
                'data' => $data,
                'errors' => null,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to read log entries: ' . $e->getMessage(),
                'data' => null,
                'errors' => [$e->getMessage()],
            ], 400);
        }
    }

    public function files(): JsonResponse
    {
        $this->authorizeAdmin();

        try {
            $files = $this->fileService->getLogFiles();

            return response()->json([
                'success' => true,
                'message' => 'Log files retrieved successfully.',
                'data' => $files,
                'errors' => null,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to list log files.',
                'data' => [],
                'errors' => [$e->getMessage()],
            ], 400);
        }
    }

    public function download(string $filePath, string $fileName = ''): BinaryFileResponse|JsonResponse
    {
        $this->authorizeAdmin();

        try {
            $filePath = $this->fileService->sanitizeFileName($filePath);

            return response()->download($filePath, basename($filePath), [
                'Content-Type' => 'text/plain',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to download log file.',
                'data' => null,
                'errors' => [$e->getMessage()],
            ], 400);
        }
    }

    public function clear(Request $request, string $filename): JsonResponse
    {
        $this->authorizeAdmin();

        try {
            $success = $this->fileService->clearLogFile($filename);
            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to clear log file.',
                    'data' => null,
                    'errors' => ['File write permission error'],
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => "Log file {$filename} has been cleared successfully.",
                'data' => null,
                'errors' => null,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing log file.',
                'data' => null,
                'errors' => [$e->getMessage()],
            ], 400);
        }
    }
}
