<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
  /**
   * Run the migrations.
   * 
   * This migration moves payment_configurations table from crafty_db to crafty_pricing database
   *
   * @return void
   */
  public function up()
  {
    // Check if table exists in crafty_pricing, if yes, skip creation
    if (Schema::connection('crafty_pricing_mysql')->hasTable('payment_configurations')) {
      echo "Table 'payment_configurations' already exists in crafty_pricing database. Skipping creation.\n";
    } else {
      // Create the table in crafty_pricing database
      Schema::connection('crafty_pricing_mysql')->create('payment_configurations', function (Blueprint $table) {
        $table->id();
        $table->string('payment_scope');
        $table->string('gateway');
        $table->json('credentials');
        $table->json('payment_types')->nullable();
        $table->boolean('is_active')->default(0);
        $table->timestamps();
      });
      echo "Table 'payment_configurations' created in crafty_pricing database.\n";
    }

    // Copy data from crafty_db to crafty_pricing if source table exists
    if (Schema::connection('mysql')->hasTable('payment_configurations')) {
      $data = DB::connection('mysql')->table('payment_configurations')->get();

      if ($data->count() > 0) {
        echo "Copying {$data->count()} records from crafty_db to crafty_pricing...\n";

        foreach ($data as $row) {
          // Check if record already exists
          $exists = DB::connection('crafty_pricing_mysql')
            ->table('payment_configurations')
            ->where('id', $row->id)
            ->exists();

          if (!$exists) {
            DB::connection('crafty_pricing_mysql')->table('payment_configurations')->insert([
              'id' => $row->id,
              'payment_scope' => $row->payment_scope,
              'gateway' => $row->gateway,
              'credentials' => $row->credentials,
              'payment_types' => $row->payment_types ?? null,
              'is_active' => $row->is_active,
              'created_at' => $row->created_at,
              'updated_at' => $row->updated_at,
            ]);
            echo "  - Copied record ID: {$row->id} - Gateway: {$row->gateway}\n";
          } else {
            echo "  - Record ID: {$row->id} already exists, skipping\n";
          }
        }

        echo "Data migration completed.\n";
      } else {
        echo "No data found in crafty_db.payment_configurations to migrate.\n";
      }

      // Drop the table from crafty_db after successful migration
      Schema::connection('mysql')->dropIfExists('payment_configurations');
      echo "Table 'payment_configurations' dropped from crafty_db.\n";
    } else {
      echo "Table 'payment_configurations' does not exist in crafty_db. Migration already completed or table never existed.\n";
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    // Recreate table in crafty_db
    if (!Schema::connection('mysql')->hasTable('payment_configurations')) {
      Schema::connection('mysql')->create('payment_configurations', function (Blueprint $table) {
        $table->id();
        $table->string('payment_scope');
        $table->string('gateway');
        $table->json('credentials');
        $table->json('payment_types')->nullable();
        $table->boolean('is_active')->default(0);
        $table->timestamps();
      });
      echo "Table 'payment_configurations' recreated in crafty_db.\n";
    }

    // Copy data back from crafty_pricing to crafty_db
    if (Schema::connection('crafty_pricing_mysql')->hasTable('payment_configurations')) {
      $data = DB::connection('crafty_pricing_mysql')->table('payment_configurations')->get();

      if ($data->count() > 0) {
        echo "Copying {$data->count()} records back from crafty_pricing to crafty_db...\n";

        foreach ($data as $row) {
          DB::connection('mysql')->table('payment_configurations')->insert([
            'id' => $row->id,
            'payment_scope' => $row->payment_scope,
            'gateway' => $row->gateway,
            'credentials' => $row->credentials,
            'payment_types' => $row->payment_types ?? null,
            'is_active' => $row->is_active,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
          ]);
        }

        echo "Data rollback completed.\n";
      }
    }

    // Drop from crafty_pricing
    Schema::connection('crafty_pricing_mysql')->dropIfExists('payment_configurations');
    echo "Table 'payment_configurations' dropped from crafty_pricing.\n";
  }
};
