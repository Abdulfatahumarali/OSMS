<?php

namespace Database\Factories;

use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'original_filename' => 'transcript.pdf',
            'stored_path' => 'documents/1/transcript.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 204800,
            'verification_status' => 'pending',
        ];
    }
}
