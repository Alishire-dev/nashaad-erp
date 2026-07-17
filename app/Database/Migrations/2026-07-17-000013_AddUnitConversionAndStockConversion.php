<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUnitConversionAndStockConversion extends Migration
{
    public function up()
    {
        // ---------------- units: conversion fields ----------------
        // Matches the original's Edit Unit form: "Units" (how many of the
        // base unit make one of this unit) + "Base Unit" (which unit this
        // one is defined in terms of) — e.g. "Dozen" = 12 base unit "Pieces".
        $this->forge->addColumn('units', [
            'conversion_factor' => ['type' => 'DECIMAL', 'constraint' => '12,3', 'null' => true, 'after' => 'short_name'],
            'base_unit_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'conversion_factor'],
        ]);

        // ---------------- stock_adjustments: cost tracking for Issued/Damaged ----------------
        // The original's Issued/Damaged Products lists show Cost Price and
        // Total per entry — captured at the time of issue/damage (like a
        // mini purchase-in-reverse), not looked up live from the item's
        // current price (which could change later and silently rewrite
        // history).
        $this->forge->addColumn('stock_adjustments', [
            'unit_cost' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true, 'after' => 'quantity'],
        ]);

        // ---------------- stock_conversions: execution log ----------------
        // Distinct from item_conversions (the RECIPE: "1 case = 24 pieces").
        // This is the EXECUTION: an actual conversion event that moved real
        // stock from a parent item to a child item, right now.
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'branch_id'       => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'parent_item_id'  => ['type' => 'INT', 'unsigned' => true],
            'qty_converted'   => ['type' => 'DECIMAL', 'constraint' => '14,3'],
            'child_item_id'   => ['type' => 'INT', 'unsigned' => true],
            'qty_produced'    => ['type' => 'DECIMAL', 'constraint' => '14,3'],
            'description'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by'      => ['type' => 'INT', 'unsigned' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id');
        $this->forge->addForeignKey('parent_item_id', 'items', 'id');
        $this->forge->addForeignKey('child_item_id', 'items', 'id');
        $this->forge->addForeignKey('created_by', 'users', 'id');
        $this->forge->createTable('stock_conversions', true);
    }

    public function down()
    {
        $this->forge->dropTable('stock_conversions', true);
        $this->forge->dropColumn('stock_adjustments', ['unit_cost']);
        $this->forge->dropColumn('units', ['conversion_factor', 'base_unit_id']);
    }
}
