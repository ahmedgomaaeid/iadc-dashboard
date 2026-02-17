<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChunkUploadController extends Controller
{
    /**
     * Handle chunk upload.
     */
    public function upload(Request $request)
    {
        $receiver = new \Illuminate\Http\UploadedFile(
            $request->file('file')->getPathname(),
            $request->file('file')->getClientOriginalName(),
            $request->file('file')->getClientMimeType(),
            $request->file('file')->getError(),
            true
        );

        // Parameters from Resumable.js
        $resumableIdentifier = $request->input('resumableIdentifier');
        $resumableFilename = $request->input('resumableFilename');
        $resumableChunkNumber = $request->input('resumableChunkNumber');
        $resumableTotalChunks = $request->input('resumableTotalChunks');

        // Clean identifier
        $resumableIdentifier = preg_replace('/[^A-Za-z0-9\-]/', '', $resumableIdentifier);

        // Temp storage path
        $tempPath = 'chunks/' . $resumableIdentifier;

        // Ensure directory exists
        if (!Storage::disk('local')->exists($tempPath)) {
            Storage::disk('local')->makeDirectory($tempPath);
        }

        // Move chunk
        $chunkFilename = $resumableIdentifier . '.part' . $resumableChunkNumber;
        $request->file('file')->storeAs($tempPath, $chunkFilename, 'local');

        // Check if all chunks are uploaded
        if ($this->isUploadComplete($tempPath, $resumableTotalChunks, $resumableIdentifier)) {
            return $this->assembleChunks($tempPath, $resumableTotalChunks, $resumableIdentifier, $resumableFilename);
        }

        return response()->json(['message' => 'Chunk uploaded'], 200);
    }

    /**
     * Check if all chunks exist.
     */
    protected function isUploadComplete($path, $totalChunks, $identifier)
    {
        for ($i = 1; $i <= $totalChunks; $i++) {
            if (!Storage::disk('local')->exists($path . '/' . $identifier . '.part' . $i)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Assemble chunks into final file.
     */
    protected function assembleChunks($path, $totalChunks, $identifier, $filename)
    {
        $finalPath = 'temp_uploads/' . $identifier . '_' . $filename;
        
        // Ensure temp_uploads directory exists
        if (!Storage::disk('local')->exists('temp_uploads')) {
            Storage::disk('local')->makeDirectory('temp_uploads');
        }

        // Create file
        Storage::disk('local')->put($finalPath, '');

        // Append chunks
        for ($i = 1; $i <= $totalChunks; $i++) {
            $chunkPath = $path . '/' . $identifier . '.part' . $i;
            $chunkContent = Storage::disk('local')->get($chunkPath);
            Storage::disk('local')->append($finalPath, $chunkContent, null); // null separator for binary safety logic check
            
            // Actually append correctly:
            $handle = fopen(Storage::disk('local')->path($finalPath), 'ab');
            fwrite($handle, $chunkContent);
            fclose($handle);
            
            // Delete chunk
            Storage::disk('local')->delete($chunkPath);
        }
        
        // Remove chunk directory
        Storage::disk('local')->deleteDirectory($path);

        return response()->json([
            'path' => $finalPath,
            'filename' => $filename,
            'is_complete' => true
        ], 200);
    }
}
