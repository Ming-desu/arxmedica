<?php


use Phinx\Seed\AbstractSeed;

class QuotationSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        $faker = Faker\Factory::create();
        $data = [];
        
        for ($i = 0; $i < 60; ++$i) {
            $data[] = [
                'representative_id' => 14,
                'created_by' => 0,
                'pr_no' => $faker->regexify('[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}'),
                'date_issued' => $faker->dateTimeThisYear('now', 'Asia/Manila')->format('Y-m-d'),
                'project_title' => $faker->sentence($faker->numberBetween(6, 10)),
                'project_description' => $faker->sentence($faker->numberBetween(10, 20)),
                'recipients' => '5,6'
            ];
        }

        $this->table('quotations')->insert($data)->saveData();
    }
}
