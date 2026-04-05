<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('image_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['service_id', 'image_id']);
        });

        $now = now();

        $services = DB::table('services')
            ->whereNotNull('image')
            ->where('image', '<>', '')
            ->get(['id', 'image']);

        foreach ($services as $service) {
            $existingImage = DB::table('images')->where('url', $service->image)->first();

            $imageId = $existingImage?->id;

            if (!$imageId) {
                $imageId = DB::table('images')->insertGetId([
                    'url' => $service->image,
                    'is_primary' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $alreadyLinked = DB::table('service_images')
                ->where('service_id', $service->id)
                ->where('image_id', $imageId)
                ->exists();

            if (!$alreadyLinked) {
                DB::table('service_images')->insert([
                    'service_id' => $service->id,
                    'image_id' => $imageId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_images');
    }
};
