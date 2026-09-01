<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$schema = DB::connection()->getDatabaseName();

$tables = DB::select("
    SELECT table_name
    FROM information_schema.tables
    WHERE table_schema = 'public'
    AND table_type = 'BASE TABLE'
    AND table_name NOT IN ('migrations')
");

$dbml = "";

foreach ($tables as $t) {
    $tableName = $t->table_name;
    $dbml .= "Table $tableName {\n";
    
    $columns = DB::select("
        SELECT column_name, data_type, character_maximum_length, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_schema = 'public'
        AND table_name = ?
        ORDER BY ordinal_position
    ", [$tableName]);
    
    foreach ($columns as $c) {
        $type = $c->data_type;
        if ($c->character_maximum_length) {
            $type .= "(" . $c->character_maximum_length . ")";
        }
        $attributes = [];
        if ($c->column_name == 'id' || strpos($c->column_default, 'nextval') !== false) {
            $attributes[] = 'primary key';
        }
        if ($c->is_nullable == 'NO') {
            $attributes[] = 'not null';
        }
        
        $attrString = empty($attributes) ? "" : " [" . implode(", ", $attributes) . "]";
        $dbml .= "  {$c->column_name} $type$attrString\n";
    }
    
    $dbml .= "}\n\n";
}

$fks = DB::select("
    SELECT
        tc.table_name,
        kcu.column_name,
        ccu.table_name AS foreign_table_name,
        ccu.column_name AS foreign_column_name
    FROM
        information_schema.table_constraints AS tc
        JOIN information_schema.key_column_usage AS kcu
          ON tc.constraint_name = kcu.constraint_name
          AND tc.table_schema = kcu.table_schema
        JOIN information_schema.constraint_column_usage AS ccu
          ON ccu.constraint_name = tc.constraint_name
          AND ccu.table_schema = tc.table_schema
    WHERE tc.constraint_type = 'FOREIGN KEY'
");

foreach ($fks as $fk) {
    $dbml .= "Ref: {$fk->table_name}.{$fk->column_name} > {$fk->foreign_table_name}.{$fk->foreign_column_name}\n";
}

file_put_contents('schema.dbml', $dbml);
echo "DBML generated successfully in schema.dbml\n";
