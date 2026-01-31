<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;

class ConvertStaticImagesToWebP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:convert-static-webp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert static landing page images to WebP.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting static image conversion...');

        $directories = [
            public_path('assets/images/landing'),
            public_path('assets/images/media'),
            public_path('assets/images/users'),
        ];

        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                $this->warn("Directory not found: {$directory}");
                continue;
            }

            $files = File::files($directory);
            $this->info("Processing directory: {$directory}");
            $bar = $this->output->createProgressBar(count($files));
            $bar->start();
    
            foreach ($files as $file) {
                $extension = strtolower($file->getExtension());
                if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    $filename = $file->getFilenameWithoutExtension();
                    $newPath = $file->getPath() . '/' . $filename . '.webp';
    
                    if (!File::exists($newPath)) {
                        try {
                            $image = Image::read($file->getPathname());
                            $image->toWebp(75)->save($newPath);
                        } catch (\Exception $e) {
                             $this->error("Failed to convert " . $file->getFilename());
                        }
                    }
                }
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
        }
        $this->info('Static images converted successfully!');
    }
}
