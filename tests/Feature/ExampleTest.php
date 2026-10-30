<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_question_1_displays_individual_polling_unit_results(): void
    {
        $response = $this->get('/?polling_unit_uniqueid=8');

        $response->assertStatus(200);
        $response->assertSee('Sapele Ward 8 PU _');
        $response->assertSee('802'); // PDP score for PU 8
        $response->assertSee('719'); // DPP score for PU 8
        $response->assertSee('939'); // PPA score for PU 8
    }

    public function test_question_2_displays_summed_lga_results_from_polling_units(): void
    {
        // Uvwie LGA (lga_id = 22) has Polling Units 13, 15, 18, 19, 21, etc.
        // PDP scores in Uvwie: PU 13 (853) + PU 15 (287) + PU 18 (1009) + PU 19 (418) + PU 21 (480) = 3,047
        $response = $this->get('/lga-results?lga_id=22');

        $response->assertStatus(200);
        $response->assertSee('Uvwie');
        $response->assertSee('3,047');
    }

    public function test_question_3_stores_results_for_all_parties_for_new_polling_unit(): void
    {
        $payload = [
            'lga_id' => 22,
            'uniquewardid' => 223,
            'polling_unit_name' => 'Effurun Model School Hall 1',
            'polling_unit_number' => 'DT2201099',
            'polling_unit_description' => 'Near PTI Junction',
            'entered_by_user' => 'Test Presiding Officer',
            'party_scores' => [
                'PDP' => 450,
                'DPP' => 310,
                'ACN' => 275,
                'PPA' => 120,
                'CDC' => 190,
                'JP' => 85,
                'ANPP' => 210,
                'LABO' => 540,
                'CPP' => 95,
            ],
        ];

        $response = $this->post('/polling-unit/store', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('polling_unit', [
            'polling_unit_name' => 'Effurun Model School Hall 1',
            'lga_id' => 22,
            'uniquewardid' => 223,
        ]);
        $this->assertDatabaseHas('announced_pu_results', [
            'party_abbreviation' => 'LABO',
            'party_score' => 540,
            'entered_by_user' => 'Test Presiding Officer',
        ]);
    }

    public function test_chained_combo_box_api_endpoints_return_json(): void
    {
        $wardsResponse = $this->getJson('/api/lgas/22/wards');
        $wardsResponse->assertStatus(200)->assertJsonFragment(['ward_name' => 'Effurun I']);

        $pusResponse = $this->getJson('/api/wards/223/polling-units');
        $pusResponse->assertStatus(200)->assertJsonFragment(['polling_unit_name' => 'Customary Court P.t.i Road']);
    }
}
