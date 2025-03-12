<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;

class ConvertAndUploadImagesToS3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Define the local folder
        $localFolder = storage_path('app/public/products');
        
        if (!File::exists($localFolder)) {
            echo "Local folder does not exist: {$localFolder}\n";
            return;
        }

        $files = File::files($localFolder);

        foreach ($files as $file) {
            $filePath = $file->getPathname();
            $fileName = pathinfo($file->getFilename(), PATHINFO_FILENAME) . '.webp';

            // Convert to WebP
            $image = Image::make($filePath)->encode('webp', 90);

            // Save locally (optional)
            $localWebpPath = storage_path("app/public/products/{$fileName}");
            $image->save($localWebpPath);

            // Upload to S3 (inside production/products/)
            $s3Path = "production/products/{$fileName}";
            Storage::disk('s3')->put($s3Path, $image->stream(), 'public');

            // Optionally, delete the local WebP file after upload
            File::delete($localWebpPath);

            echo "Uploaded to S3: {$s3Path}\n";
        }
    }
}
