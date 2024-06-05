<?php

use App\Models\Categories;
use App\Models\Manufacturer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PhpParser\Node\Stmt\Catch_;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scrapped_data', function (Blueprint $table) {
            $table->id();
            $table->float('s_a_p_winter_seasonal_efficiency','4')->nullable();
            $table->float('s_a_p_summer_seasonal_efficiency','4')->nullable();
            $table->string('fuel','50')->nullable();
            $table->string('Model_data','90')->nullable();
            $table->string('main_type','50')->nullable();
            $table->boolean('condensing')->nullable();
            $table->unsignedBigInteger('index_number')->unique();
            $table->string('boiler_i_d',30)->nullable();
            $table->foreignIdFor(Manufacturer::class,)->nullable()->constrained();
            $table->json('extra_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scrapped_data');
    }
};
