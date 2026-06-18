<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('sales', 'sold_at', 'sales_sold_at_index');
        $this->addIndexIfMissing('expenses', 'expense_date', 'expenses_expense_date_index');
        $this->addIndexIfMissing('sale_items', 'sale_id', 'sale_items_sale_id_index');
        $this->addIndexIfMissing('sale_items', 'product_id', 'sale_items_product_id_index');
        $this->addIndexIfMissing('stock_movements', 'product_id', 'stock_movements_product_id_index');
        $this->addIndexIfMissing('stock_movements', 'created_at', 'stock_movements_created_at_index');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('sales', 'sales_sold_at_index');
        $this->dropIndexIfExists('expenses', 'expenses_expense_date_index');
        $this->dropIndexIfExists('sale_items', 'sale_items_sale_id_index');
        $this->dropIndexIfExists('sale_items', 'sale_items_product_id_index');
        $this->dropIndexIfExists('stock_movements', 'stock_movements_product_id_index');
        $this->dropIndexIfExists('stock_movements', 'stock_movements_created_at_index');
    }

    private function addIndexIfMissing(string $tableName, string $columnName, string $indexName): void
    {
        if ($this->indexExists($tableName, $columnName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName, $indexName) {
            $table->index($columnName, $indexName);
        });
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (! $this->namedIndexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function indexExists(string $tableName, string $columnName): bool
    {
        $driverName = DB::getDriverName();

        if ($driverName !== 'mysql') {
            return false;
        }

        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $tableName)
            ->where('column_name', $columnName)
            ->where('seq_in_index', 1)
            ->where('index_name', '<>', 'PRIMARY')
            ->exists();
    }

    private function namedIndexExists(string $tableName, string $indexName): bool
    {
        $driverName = DB::getDriverName();

        if ($driverName !== 'mysql') {
            return false;
        }

        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
};
