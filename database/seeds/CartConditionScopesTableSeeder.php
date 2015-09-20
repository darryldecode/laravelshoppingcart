<?php

use Illuminate\Database\Seeder;

use Ozanmuyes\Cart\Models\CartConditionScope;

class CartConditionScopesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $scopes = [
            [
                "name" => "product"
            ],
            [
                "name" => "collection"
            ],
            [
                "name" => "cart"
            ]
        ];

        foreach ($scopes as $scope) {
            CartConditionScope::create([
                "name" => $scope["name"]
            ]);
        }
    }
}
