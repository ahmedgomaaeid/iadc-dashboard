<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use App\Models\Article;
use App\Models\User;
use App\Models\Highboard;
use App\Models\Board;
use App\Models\Magazine;
use App\Models\Event;
use App\Models\EventPartner;
use App\Models\DynamicForm;

class ConvertImagesToWebP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:convert-webp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert existinguploaded images to WebP format to enhance performance.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting image conversion to WebP...');

        $configs = [
            ['model' => Article::class, 'column' => 'image'],
            ['model' => User::class, 'column' => 'image'],
            ['model' => Highboard::class, 'column' => 'image'],
            ['model' => Board::class, 'column' => 'image'],
            ['model' => Magazine::class, 'column' => 'image'],
            ['model' => Event::class, 'column' => 'image'],
            ['model' => EventPartner::class, 'column' => 'image'],
            ['model' => DynamicForm::class, 'column' => 'form_image'],
        ];

        foreach ($configs as $config) {
            $modelClass = $config['model'];
            $column = $config['column'];
            $this->processModel($modelClass, $column);
        }

        $this->info('All images converted (or skipped) successfully!');
    }

    private function processModel($modelClass, $column)
    {
        $this->info("Processing {$modelClass}...");

        $records = $modelClass::whereNotNull($column)->get();

        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        foreach ($records as $record) {
            $path = $record->$column;

            // Skip if already webp
            if (Str::endsWith(strtolower($path), '.webp')) {
                $bar->advance();
                continue;
            }

            if (!Storage::disk('public')->exists($path)) {
                // $this->warn("File not found: {$path}"); // Optional: log warning
                $bar->advance();
                continue;
            }

            try {
                // Read image
                $fileContent = Storage::disk('public')->get($path);
                $image = Image::read($fileContent);

                // Convert to webp
                $encoded = $image->toWebp(75);

                // Determine new path
                $directory = dirname($path);
                // Use a new unique name to avoid conflicts if needed, or just change extension
                // Changing extension is cleaner but need to ensure uniqueness if filename repeats? 
                // Usually UUIDs are used, so let's stick to generating a new UUID to be safe and consistent with the Trait.
                $newFilename = Str::uuid() . '.webp';
                // However, to keep folder structure clean, let's keep the directory.
                // If directory is '.', it might be root.
                if ($directory === '.') {
                     $directory = '';
                }
                
                $newPath = $directory ? $directory . '/' . $newFilename : $newFilename;

                // Save new image
                Storage::disk('public')->put($newPath, (string) $encoded);

                // Update record
                $record->$column = $newPath;
                $record->save();

                // Delete old image
                Storage::disk('public')->delete($path);

            } catch (\Exception $e) {
                // Log error but continue
                $this->error("Failed to convert {$path}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }
}
