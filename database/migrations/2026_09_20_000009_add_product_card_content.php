<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'package_name')) {
                $table->string('package_name', 120)->nullable()->after('name');
            }
            if (! Schema::hasColumn('products', 'package_details')) {
                $table->string('package_details', 255)->nullable()->after('package_name');
            }
        });

        DB::table('products')->orderBy('id')->each(function (object $product): void {
            [$packageName, $packageDetails] = $this->splitName((string) $product->name);
            DB::table('products')->where('id', $product->id)->update([
                'package_name' => $packageName,
                'package_details' => $packageDetails,
                'updated_at' => now(),
            ]);
        });
    }

    private function splitName(string $name): array
    {
        $name = preg_replace('/^📦(?!\s)/u', '📦 ', trim($name));
        $parts = preg_split('/\s*🪑\s*/u', $name, 2);
        if (count($parts) === 2) {
            return [trim($parts[0]), '🪑 '.trim($parts[1])];
        }

        if (preg_match('/^(.*?\b\d+ml)\s*(\(.+\))$/u', $name, $matches) === 1) {
            return [trim($matches[1]), trim($matches[2])];
        }

        return [$name, null];
    }

    public function down(): void
    {
        // Product card copy is deliberately preserved during rollback.
    }
};
