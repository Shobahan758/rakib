<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeploymentMigrationTest extends TestCase
{
    public function test_risk_migration_handles_existing_columns_and_repeated_runs_without_data_loss(): void
    {
        $original = DB::getDefaultConnection();
        $config = config('database.connections.'.$original);
        $config['prefix'] = 'migration_check_'.bin2hex(random_bytes(6)).'_';
        config(['database.connections.migration_check' => $config]);
        DB::setDefaultConnection('migration_check');
        try {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('status');
                $table->string('ip_address')->nullable();
                $table->timestamps();
                $table->unsignedInteger('risk_score')->default(0);
            });
            $id = DB::table('orders')->insertGetId(['status' => 'fake', 'risk_score' => 85]);
            $migration = require database_path('migrations/2026_09_07_000002_add_risk_scoring_to_orders_table.php');
            $migration->up();
            $this->assertTrue(Schema::hasColumn('orders', 'risk_reasons'));
            $this->assertTrue(Schema::hasIndex('orders', ['ip_address', 'created_at']));
            DB::table('orders')->where('id', $id)->update(['risk_reasons' => json_encode(['legacy' => 85]), 'fake_marked_at' => '2026-09-01 10:00:00']);
            $migration->up();
            $row = DB::table('orders')->find($id);
            $this->assertSame(85, (int) $row->risk_score);
            $this->assertSame(['legacy' => 85], json_decode($row->risk_reasons, true));
            $this->assertSame('2026-09-01 10:00:00', $row->fake_marked_at);

            Schema::create('incomplete_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('quantity')->default(1);
            });
            Schema::table('orders', fn (Blueprint $table) => $table->uuid('incomplete_token')->nullable());
            $checkout = require database_path('migrations/2026_09_07_000003_link_incomplete_checkouts_to_orders.php');
            $checkout->up();
            $checkout->up();
            $this->assertTrue(Schema::hasIndex('orders', ['incomplete_token'], 'unique'));
            DB::table('incomplete_orders')->insert(['quantity' => 9999]);
            $this->assertSame(9999, (int) DB::table('incomplete_orders')->value('quantity'));
        } finally {
            Schema::dropIfExists('incomplete_orders');
            Schema::dropIfExists('orders');
            DB::setDefaultConnection($original);
            DB::purge('migration_check');
        }
    }
}
