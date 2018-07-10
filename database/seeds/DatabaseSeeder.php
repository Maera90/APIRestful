<?php

use App\User;
use App\Product;
use App\Category;
use App\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        User::truncate();
        Category::truncate();
        Product::truncate();
        Transaction::truncate();
        DB::table('category_product')->truncate();

        $cantidadUsers = 1000;
        $cantidadCategories = 30;
        $cantidadProducts = 1000;
        $cantidadTransacciones = 1000;

        factory(User::class,$cantidadUsers)->create();
        factory(Category::class,$cantidadCategories)->create();

        factory(Product::class,$cantidadProducts)->create()->each(

            function($producto){
                $categories = Category::all()->random(mt_rand(1,5))->pluck('id');
                $producto -> categories()->attach($categories);
            }

            );

        factory(Transaction::class,$cantidadTransacciones)->create();
    }
}
