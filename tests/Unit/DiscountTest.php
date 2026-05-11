<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use App\Http\Controllers\Tenant\DiscountController;
use App\Http\Requests\Tenant\Discount\UpdateDiscountRequest;
use App\Models\Discount;
use Illuminate\Http\JsonResponse;

class DiscountTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_update_gift_card_value_updates_current_balance()
    {
        // 1. STUB: Creiamo un finto oggetto Discount con dati iniziali
        // Usiamo un mock parziale per simulare i metodi Eloquent senza toccare il DB
        $discountStub = Mockery::mock(Discount::class)->makePartial();
        $discountStub->id = 1;
        $discountStub->type = 'gift_card';
        $discountStub->value = 50; // Vecchio valore nominale
        $discountStub->current_balance = 20; // Saldo attuale (era stata usata)

        // 2. MOCK: Mockiamo il metodo update() del model
        $discountStub->shouldReceive('update')
                     ->once()
                     ->with(['value' => 70])
                     ->andReturnUsing(function($data) use ($discountStub) {
                         $discountStub->value = $data['value'];
                         return true;
                     });

        $discountStub->shouldReceive('update')
                     ->once() // Ci aspettiamo la seconda chiamata per aggiornare in automatico il saldo
                     ->with(['current_balance' => 40]) // 50 -> 70 (+20), quindi saldo 20 + 20 = 40
                     ->andReturn(true);

        // 3. MOCK: Mockiamo la Request per simulare l'input dell'utente
        $requestMock = Mockery::mock(UpdateDiscountRequest::class);
        $requestMock->shouldReceive('validated')->once()->andReturn(['value' => 70]);
        $requestMock->shouldReceive('has')->with('value')->once()->andReturn(true);

        // 4. TEST: Eseguiamo il controller passando il Mock della Request e lo Stub del Modello
        $controller = new DiscountController();
        $response = $controller->update($requestMock, $discountStub);

        // 5. ASSERT: Verifichiamo il risultato
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertEquals('Sconto aggiornato con successo', $responseData['message']);
    }

    public function test_show_returns_discount_details()
    {
        // 1. STUB: Modello finto
        $discountStub = Mockery::mock(Discount::class)->makePartial();
        $discountStub->id = 99;
        $discountStub->name = 'Sconto Invernale';
        $discountStub->type = 'percentage';
        $discountStub->value = 15;
        $discountStub->is_active = true;

        // Non c'è bisogno di mockare metodi perché 'show' legge solo i dati

        // 2. Esecuzione
        $controller = new DiscountController();
        $response = $controller->show($discountStub);

        // 3. ASSERT
        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);
        
        $this->assertEquals('Dettaglio sconto recuperato', $data['message']);
        $this->assertEquals('Sconto Invernale', $data['data']['name']);
        $this->assertEquals(15, $data['data']['value']);
    }
}
